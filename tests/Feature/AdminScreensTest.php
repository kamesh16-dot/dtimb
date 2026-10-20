<?php

namespace Tests\Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;

/**
 * The admin screens against a real database, built from the migrations for
 * every test. The rest of the suite runs with no tables and so only ever
 * exercises the fallbacks; these cover what an editor actually does.
 */
final class AdminScreensTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use FeatureTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    /**
     * Drop the tables again afterwards. The rest of the suite runs against an
     * empty database on purpose — it tests the fallbacks — and an in-memory
     * database is shared by the whole run.
     */
    protected function tearDown(): void
    {
        \App\Libraries\Notifier::$mailerFactory = null;
        $this->regressDatabase();
        parent::tearDown();
    }

    private function user(string $role = 'admin', array $extra = []): array
    {
        $now = date('Y-m-d H:i:s');
        $row = $extra + [
            'name'          => ucfirst($role) . ' Test',
            'email'         => $role . '-' . bin2hex(random_bytes(3)) . '@example.invalid',
            'password_hash' => password_hash('irrelevant-Pa55!', PASSWORD_BCRYPT),
            'role'          => $role,
            'is_active'     => 1,
            'force_password_change' => 0,
            'failed_logins' => 0,
            'created_at'    => $now,
            'updated_at'    => $now,
        ];
        $this->db->table('users')->insert($row);

        return ['id' => (int) $this->db->insertID()] + $row;
    }

    private function as(array $user): self
    {
        return $this->withSession([
            'admin_logged_in'     => true,
            'admin_last_activity' => time(),
            'admin_user'          => ['id' => $user['id'], 'name' => $user['name'], 'email' => $user['email'], 'role' => $user['role']],
        ]);
    }

    /**
     * POST like a browser that loaded the form: the CSRF cookie and the posted
     * token match (the routes keep their csrf filter on, as in production).
     */
    private function postForm(string $url, array $params = [])
    {
        $token = bin2hex(random_bytes(16));
        $_COOKIE[config('Security')->cookieName] = $token;
        \Config\Services::resetSingle('security');

        return $this->post($url, [config('Security')->tokenName => $token] + $params);
    }

    /** The request ends in "not found", whether thrown or rendered as a 404. */
    private function assertNotFound(callable $request, string $what): void
    {
        try {
            $response = $request();
        } catch (\CodeIgniter\Exceptions\PageNotFoundException) {
            $this->addToAssertionCount(1);

            return;
        }
        $this->assertSame(404, $response->response()->getStatusCode(), $what . ' should not be reachable');
    }

    public function testTheMigrationsBuildEveryTable(): void
    {
        foreach (['machines', 'pages', 'media', 'news_articles', 'enquiries', 'career_applications', 'users', 'settings', 'hero_slides', 'audit_log'] as $table) {
            $this->assertTrue($this->db->tableExists($table), "{$table} is missing");
        }
        $this->assertTrue($this->db->fieldExists('nav_order', 'machines'), 'AddMachineNavOrder did not run');
        $this->assertSame(10, $this->db->table('pages')->countAllResults(), 'SeedSitePages did not run');
    }

    public function testAMachineAddedInTheAdminAppearsOnTheSite(): void
    {
        $admin = $this->user();

        $this->as($admin)->postForm('admin/machines/create', [
            'name' => 'Test Bender', 'slug' => 'test-bender', 'kind' => 'product', 'group_name' => 'Forming Machines',
            'series_label' => 'TB', 'metric' => '1–9 mm', 'summary' => 'A test machine for the test suite.',
            'menu_order' => 1, 'nav_order' => 1, 'is_published' => '1', 'show_in_menu' => '1',
        ])->assertRedirect();

        $this->seeInDatabase('machines', ['slug' => 'test-bender', 'is_published' => 1]);
        $this->seeInDatabase('audit_log', ['action' => 'machine_create', 'user_id' => $admin['id']]);

        $page = $this->get('machines/test-bender');
        $page->assertOK();
        $page->assertSee('Test Bender');
    }

    public function testAnUnpublishedMachineIsNotOnTheSite(): void
    {
        $this->db->table('machines')->insert([
            'slug' => 'hidden-one', 'name' => 'Hidden One', 'group_name' => 'Other', 'kind' => 'product',
            'series_label' => 'H', 'metric' => '1', 'menu_order' => 1, 'nav_order' => 1,
            'show_in_menu' => 1, 'is_published' => 0,
        ]);

        $this->assertNotFound(fn () => $this->get('machines/hidden-one'), 'an unpublished machine');
    }

    public function testStatsCaptionsCannotCarryScript(): void
    {
        // An editor-editable list must not be able to put a script on the home page.
        $this->db->table('stats')->insert([
            'payload' => json_encode(['8', 'core machine<br><script>alert(1)</script>']),
            'sort_order' => 0, 'is_active' => 1,
        ]);

        $home = $this->get('/');
        $home->assertOK();
        $home->assertSee('core machine<br>', null);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $home->getBody());
    }

    public function testADeactivatedUserIsSignedOutOnTheNextClick(): void
    {
        $user = $this->user('admin');
        $this->db->table('users')->where('id', $user['id'])->update(['is_active' => 0]);

        $this->as($user)->get('admin')->assertRedirectTo('/admin/login');
    }

    public function testADemotedAdminLosesAdminScreensAtOnce(): void
    {
        $user = $this->user('admin');
        $this->db->table('users')->where('id', $user['id'])->update(['role' => 'editor']);

        $this->assertNotFound(fn () => $this->as($user)->get('admin/users'), 'the Users screen for a demoted admin');
    }

    public function testEditorsCannotSeeApplicationsOrCvs(): void
    {
        $editor = $this->user('editor');
        $this->db->table('career_applications')->insert([
            'name' => 'Applicant', 'email' => 'a@example.invalid', 'phone' => '123456', 'area' => 'Design',
            'cv_path' => 'careers/none.pdf', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();

        foreach (['admin/inbox/applications', "admin/inbox/message/application/{$id}", "admin/inbox/cv/{$id}"] as $url) {
            $this->assertNotFound(fn () => $this->as($editor)->get($url), "{$url} for an editor");
        }

        $this->as($editor)->get('admin/inbox')->assertOK();
    }

    public function testAnAdminCanDeleteAMessage(): void
    {
        $admin = $this->user('admin');
        $this->db->table('enquiries')->insert([
            'name' => 'Visitor', 'email' => 'v@example.invalid', 'phone' => '', 'company' => '', 'interest' => '',
            'message' => 'Please delete me.', 'ip' => '127.0.0.1', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();

        $this->as($admin)->postForm("admin/inbox/delete/enquiry/{$id}")->assertRedirect();

        $this->dontSeeInDatabase('enquiries', ['id' => $id]);
    }

    public function testEditorsCannotDeleteMessages(): void
    {
        $editor = $this->user('editor');
        $this->db->table('enquiries')->insert([
            'name' => 'Visitor', 'email' => 'v@example.invalid', 'phone' => '', 'company' => '', 'interest' => '',
            'message' => 'Keep me.', 'ip' => '127.0.0.1', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $id = (int) $this->db->insertID();

        $this->assertNotFound(fn () => $this->as($editor)->postForm("admin/inbox/delete/enquiry/{$id}"), 'deleting as an editor');

        $this->seeInDatabase('enquiries', ['id' => $id]);
    }

    public function testAListItemCanBeAddedHiddenAndShown(): void
    {
        $admin = $this->user('admin');

        $this->as($admin)->postForm('admin/lists/career_areas/add', ['name' => 'Testing area'])->assertRedirect();
        $this->seeInDatabase('career_areas', ['name' => 'Testing area', 'is_active' => 0]); // box left unticked

        $id = (int) $this->db->table('career_areas')->where('name', 'Testing area')->get()->getRow()->id;
        $this->as($admin)->postForm("admin/lists/career_areas/edit/{$id}", ['name' => 'Testing area', 'is_active' => '1'])->assertRedirect();
        $this->seeInDatabase('career_areas', ['id' => $id, 'is_active' => 1]);
    }

    public function testPageSeoEditsReachTheSite(): void
    {
        $admin = $this->user('admin');

        $this->as($admin)->postForm('admin/pages/edit/about', [
            'title' => 'About', 'seo_title' => 'About TIMB — Test Title',
            'seo_description' => 'A description long enough to be a sensible search engine description for the test.',
            'intro' => '', 'body_blocks' => '',
        ])->assertRedirect();

        $this->get('about')->assertSee('About TIMB — Test Title');
    }

    private function machineWithPage(): int
    {
        $this->db->table('machines')->insert([
            'slug' => 'paged', 'name' => 'Paged Machine', 'group_name' => 'Forming Machines', 'kind' => 'product',
            'series_label' => 'PM', 'metric' => '1–2 m', 'summary' => 'The summary.', 'menu_order' => 1, 'nav_order' => 1,
            'show_in_menu' => 1, 'is_published' => 1,
            'details_json' => json_encode(['tagline' => 'Old description.', 'specLabel' => 'Kept specs', 'specs' => [['Width', '2 m']]]),
        ]);

        return (int) $this->db->insertID();
    }

    private function machineForm(int $id, array $extra): array
    {
        return $extra + [
            'name' => 'Paged Machine', 'slug' => 'paged', 'kind' => 'product', 'group_name' => 'Forming Machines',
            'series_label' => 'PM', 'metric' => '1–2 m', 'summary' => 'The summary.', 'menu_order' => 1, 'nav_order' => 1,
            'is_published' => '1', 'show_in_menu' => '1',
        ];
    }

    public function testTheSearchDescriptionIsEditableAndReachesThePage(): void
    {
        $admin = $this->user();
        $id    = $this->machineWithPage();

        $this->as($admin)->postForm("admin/machines/edit/{$id}", $this->machineForm($id, [
            'page_tagline' => 'A brand new search description for this machine, long enough to look right in Google.',
        ]))->assertRedirect();

        $stored = json_decode($this->db->table('machines')->where('id', $id)->get()->getRow()->details_json, true);
        $this->assertSame('A brand new search description for this machine, long enough to look right in Google.', $stored['tagline']);
        $this->assertSame('Kept specs', $stored['specLabel'], 'the rest of the page content must survive');

        $this->get('machines/paged')->assertSee('A brand new search description');
    }

    public function testInvalidPageContentIsRejectedAndNothingIsSaved(): void
    {
        $admin = $this->user();
        $id    = $this->machineWithPage();
        $before = $this->db->table('machines')->where('id', $id)->get()->getRow()->details_json;

        $this->as($admin)->postForm("admin/machines/edit/{$id}", $this->machineForm($id, [
            'name' => 'Renamed',
            'page_json' => '{"specs": [ "missing bracket" }',
        ]))->assertRedirect();

        $row = $this->db->table('machines')->where('id', $id)->get()->getRow();
        $this->assertSame($before, $row->details_json);
        $this->assertSame('Paged Machine', $row->name, 'a refused save must change nothing');
    }

    // ── Replies from the Inbox ───────────────────────────────────────────────

    /** @var list<\CodeIgniter\Test\Mock\MockEmail> emails "sent" during a test */
    private array $sent = [];

    /** Email set up, with every message captured instead of sent. */
    private function captureMail(): void
    {
        $c = config('Email');
        $c->protocol   = 'smtp';
        $c->SMTPHost   = 'smtp.example.com';
        $c->SMTPUser   = 'site@example.com';
        $c->SMTPPass   = 'secret';
        $c->fromEmail  = 'site@example.com';
        $c->recipients = 'team@example.com';

        $this->sent = [];
        \App\Libraries\Notifier::$mailerFactory = function () {
            $mock = new \CodeIgniter\Test\Mock\MockEmail(config('Email'));
            $this->sent[] = $mock;

            return $mock;
        };
    }

    private function enquiry(array $extra = []): int
    {
        $this->db->table('enquiries')->insert($extra + [
            'name' => 'Priya Raman', 'email' => 'priya@example.com', 'phone' => '', 'company' => 'Sundaram Tubes',
            'interest' => 'Tube Mill', 'message' => 'Please send a quotation.', 'ip' => '127.0.0.1',
            'status' => 'new', 'created_at' => date('Y-m-d H:i:s'),
        ]);

        return (int) $this->db->insertID();
    }

    public function testAReplyIsEmailedToTheVisitorAndKept(): void
    {
        $this->captureMail();
        $admin = $this->user('admin', ['name' => 'Kamesh K', 'email' => 'kamesh@example.com']);
        $id    = $this->enquiry();

        $this->as($admin)->postForm("admin/inbox/reply/enquiry/{$id}", [
            'subject' => 'Re: your enquiry to TIMB about Tube Mill',
            'body'    => "Dear Priya,\n\nOur quotation is attached.\n\nKind regards,\nKamesh",
            'quote'   => '1',
            'copy'    => '1',
        ])->assertRedirect();

        $this->assertCount(1, $this->sent);
        $mail = $this->sent[0]->archive;
        $this->assertSame(['priya@example.com'], $mail['recipients']);
        $this->assertStringContainsString('team@example.com', $mail['headers']['Reply-To'], 'answers must reach the team');
        $this->assertStringContainsString('kamesh@example.com', implode(',', (array) $mail['BCCArray']), 'the copy to the sender');
        $this->assertStringContainsString('Our quotation is attached.', $mail['body']);
        $this->assertStringContainsString('Please send a quotation.', $mail['body'], 'their message is quoted');
        $this->assertStringContainsString('TI Machine Building', $mail['body'], 'company signature');

        $this->seeInDatabase('inbox_replies', ['type' => 'enquiry', 'message_id' => $id, 'to_email' => 'priya@example.com', 'user_name' => 'Kamesh K']);
        $this->seeInDatabase('enquiries', ['id' => $id, 'status' => 'read']);

        $page = $this->as($admin)->get("admin/inbox/message/enquiry/{$id}");
        $page->assertSee('Replies sent (1)');
        $page->assertSee('Our quotation is attached.');
    }

    public function testAnEmptyReplyIsNotSent(): void
    {
        $this->captureMail();
        $admin = $this->user();
        $id    = $this->enquiry();

        $this->as($admin)->postForm("admin/inbox/reply/enquiry/{$id}", ['subject' => 'Hi', 'body' => ''])->assertRedirect();

        $this->assertCount(0, array_filter($this->sent, static fn ($m) => ! empty($m->archive)));
        $this->dontSeeInDatabase('inbox_replies', ['message_id' => $id]);
    }

    public function testNothingIsKeptWhenEmailIsNotSetUp(): void
    {
        $admin = $this->user();
        $id    = $this->enquiry();
        config('Email')->SMTPHost = '';

        $this->as($admin)->postForm("admin/inbox/reply/enquiry/{$id}", ['subject' => 'Hi there', 'body' => 'A reply that cannot go anywhere.'])->assertRedirect();

        $this->dontSeeInDatabase('inbox_replies', ['message_id' => $id]);
    }

    public function testEditorsCanAnswerEnquiriesButNotApplications(): void
    {
        $this->captureMail();
        $editor = $this->user('editor');
        $id     = $this->enquiry();

        $this->as($editor)->postForm("admin/inbox/reply/enquiry/{$id}", ['subject' => 'Re: enquiry', 'body' => 'Thank you, we will call you.'])->assertRedirect();
        $this->seeInDatabase('inbox_replies', ['type' => 'enquiry', 'message_id' => $id]);

        $this->db->table('career_applications')->insert([
            'name' => 'Arjun', 'email' => 'arjun@example.com', 'phone' => '123456', 'area' => 'Design',
            'cv_path' => '', 'status' => 'new', 'created_at' => date('Y-m-d H:i:s'),
        ]);
        $app = (int) $this->db->insertID();
        $this->assertNotFound(fn () => $this->as($editor)->postForm("admin/inbox/reply/application/{$app}", ['subject' => 'Re', 'body' => 'Hello there, Arjun.']), 'an editor replying to an application');
        $this->dontSeeInDatabase('inbox_replies', ['type' => 'application', 'message_id' => $app]);
    }

    public function testDeletingAMessageRemovesItsReplies(): void
    {
        $this->captureMail();
        $admin = $this->user();
        $id    = $this->enquiry();
        $this->as($admin)->postForm("admin/inbox/reply/enquiry/{$id}", ['subject' => 'Re', 'body' => 'Thank you for writing.'])->assertRedirect();

        $this->as($admin)->postForm("admin/inbox/delete/enquiry/{$id}")->assertRedirect();

        $this->dontSeeInDatabase('inbox_replies', ['message_id' => $id]);
    }

    public function testWhatAnAdminTypesIsEscapedInTheReply(): void
    {
        $this->captureMail();
        $admin = $this->user();
        $id    = $this->enquiry(['message' => '<img src=x onerror=alert(1)>']);

        $this->as($admin)->postForm("admin/inbox/reply/enquiry/{$id}", ['subject' => 'Re', 'body' => '<script>alert(1)</script> Thanks.', 'quote' => '1'])->assertRedirect();

        $body = $this->sent[0]->archive['body'];
        $this->assertStringNotContainsString('<script>alert(1)', $body);
        $this->assertStringNotContainsString('<img src=x', $body);
    }
}

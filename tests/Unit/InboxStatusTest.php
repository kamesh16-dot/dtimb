<?php

namespace Tests\Unit;

use App\Models\ApplicationModel;
use App\Models\EnquiryModel;
use App\Models\InboxModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The inbox's status values are shared by the dashboard counters, the filter
 * tabs and the forms that create messages. A mismatch between them does not
 * error — it quietly loses messages from a view.
 */
final class InboxStatusTest extends CIUnitTestCase
{
    public function testTheThreeStatusesAreTheOnesTheUiOffers(): void
    {
        $this->assertSame(['new', 'read', 'archived'], InboxModel::STATUSES);
    }

    public function testEveryStatusHasALabelForTheFilterTabs(): void
    {
        foreach (InboxModel::STATUSES as $status) {
            $this->assertArrayHasKey($status, InboxModel::LABELS, "no label for '{$status}'");
            $this->assertNotSame('', InboxModel::LABELS[$status]);
        }
    }

    public function testNewIsTheStatusTheDashboardCounts(): void
    {
        // App\Controllers\Admin\Dashboard counts status = 'new', and
        // App\Controllers\Forms writes that value on every submission.
        $this->assertContains('new', InboxModel::STATUSES);
        $this->assertSame('new', InboxModel::STATUSES[0]);
    }

    public function testEnquiryModelAcceptsEveryColumnTheContactFormWrites(): void
    {
        $allowed = (new EnquiryModel())->allowedFieldList();

        foreach (['name', 'email', 'phone', 'company', 'interest', 'message', 'ip', 'user_agent', 'status'] as $field) {
            $this->assertContains($field, $allowed, "the contact form writes '{$field}'");
        }
    }

    public function testApplicationModelAcceptsEveryColumnTheCareersFormWrites(): void
    {
        $allowed = (new ApplicationModel())->allowedFieldList();

        foreach (['name', 'email', 'phone', 'area', 'experience', 'message', 'cv_path', 'status'] as $field) {
            $this->assertContains($field, $allowed, "the careers form writes '{$field}'");
        }
    }

    public function testNotesAreEditableOnEnquiriesOnly(): void
    {
        $this->assertContains('notes', (new EnquiryModel())->allowedFieldList());
        $this->assertNotContains('notes', (new ApplicationModel())->allowedFieldList());
    }

    public function testACvPathPointingOutsideUploadsIsRefused(): void
    {
        // cv_path is read from the database and joined onto a filesystem path;
        // a traversing value must never resolve to a file.
        $model = new ApplicationModel();

        foreach (['../../../.env', '/etc/passwd', '../../app/Config/Database.php'] as $path) {
            $this->assertNull($model->cvFile(['cv_path' => $path]), "'{$path}' should be refused");
        }
    }

    public function testAnEmptyOrMissingCvPathResolvesToNothing(): void
    {
        $model = new ApplicationModel();

        $this->assertNull($model->cvFile(['cv_path' => '']));
        $this->assertNull($model->cvFile(['cv_path' => '   ']));
        $this->assertNull($model->cvFile([]));
    }

    public function testAPathThatDoesNotExistResolvesToNothing(): void
    {
        $this->assertNull((new ApplicationModel())->cvFile(['cv_path' => 'careers/no-such-file.pdf']));
    }
}

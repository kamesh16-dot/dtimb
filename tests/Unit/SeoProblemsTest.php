<?php

namespace Tests\Unit;

use App\Libraries\SeoAudit;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The SEO checklist tells an editor what is wrong with a page and how to fix
 * it. Every finding must name the part it is about and come with a fix, or the
 * drawer shows a problem with no way forward.
 */
final class SeoProblemsTest extends CIUnitTestCase
{
    private function problems(string $title, string $description): array
    {
        return (new SeoAudit())->problems($title, $description);
    }

    private function goodDescription(): string
    {
        return str_repeat('a', SeoAudit::DESCRIPTION_MIN + 10);
    }

    public function testAGoodTitleAndDescriptionHaveNoProblems(): void
    {
        $this->assertSame([], $this->problems('About | TIMB', $this->goodDescription()));
    }

    public function testEveryProblemHasAPartATextAndAFix(): void
    {
        $cases = [
            ['', ''],
            [str_repeat('t', SeoAudit::TITLE_MAX + 5), str_repeat('d', SeoAudit::DESCRIPTION_MAX + 5)],
            ['Hi', 'Short.'],
        ];

        foreach ($cases as [$t, $d]) {
            foreach ($this->problems($t, $d) as $p) {
                $this->assertContains($p['part'], ['title', 'description']);
                $this->assertNotSame('', $p['text']);
                $this->assertNotSame('', $p['tip'], 'a problem with no fix: ' . $p['text']);
            }
        }
    }

    public function testMissingTitleAndDescriptionAreBothReported(): void
    {
        $parts = array_column($this->problems('', ''), 'part');

        $this->assertSame(['title', 'description'], $parts);
    }

    public function testTooLongIsReportedWithTheActualLength(): void
    {
        $title = str_repeat('t', SeoAudit::TITLE_MAX + 7);
        [$p]   = $this->problems($title, $this->goodDescription());

        $this->assertSame('title', $p['part']);
        $this->assertStringContainsString((string) (SeoAudit::TITLE_MAX + 7), $p['text']);
    }

    public function testAShortBrandTitleIsNotFlagged(): void
    {
        // "About | TIMB" is 12 characters and a perfectly good title.
        $this->assertSame([], array_filter(
            $this->problems('About | TIMB', $this->goodDescription()),
            static fn ($p) => $p['part'] === 'title'
        ));
    }

    public function testDuplicatesAreFlaggedOnBothPages(): void
    {
        $audit = new SeoAudit();
        $rows  = [
            ['title' => 'Same | TIMB', 'description' => $this->goodDescription(), 'problems' => []],
            ['title' => 'Same | TIMB', 'description' => $this->goodDescription() . 'x', 'problems' => []],
        ];

        $flag = new \ReflectionMethod($audit, 'flagDuplicates');
        $flag->setAccessible(true);
        $flag->invokeArgs($audit, [&$rows]);

        foreach ($rows as $r) {
            $this->assertCount(1, $r['problems']);
            $this->assertSame('title', $r['problems'][0]['part']);
        }
    }
}

<?php

namespace Tests\Unit;

use App\Models\PageModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * pages.body_blocks holds a page's structured content as JSON, edited in the
 * admin as raw JSON. A mistake in the conversion either corrupts a live page
 * or silently discards content, so both directions are covered here.
 */
final class PageBlocksTest extends CIUnitTestCase
{
    public function testTextIsPrettyPrintedForEditing(): void
    {
        $text = PageModel::blocksToText('{"stats":[["15+","Years"]]}');

        $this->assertStringContainsString("\n", $text, 'blocks should be pretty printed');
        $this->assertSame(['stats' => [['15+', 'Years']]], json_decode($text, true));
    }

    public function testEmptyBlocksGiveEmptyText(): void
    {
        $this->assertSame('', PageModel::blocksToText(''));
        $this->assertSame('', PageModel::blocksToText(null));
        $this->assertSame('', PageModel::blocksToText('   '));
    }

    public function testUnparseableStoredValueIsShownAsIsRatherThanBlanked(): void
    {
        // An editor must be able to see and repair a broken value.
        $this->assertSame('{not json', PageModel::blocksToText('{not json'));
    }

    public function testValidJsonIsAccepted(): void
    {
        [$valid, $json] = PageModel::blocksFromText('{"stats":[["15+","Years"]]}');

        $this->assertTrue($valid);
        $this->assertSame(['stats' => [['15+', 'Years']]], json_decode($json, true));
    }

    public function testBrokenJsonIsRejectedWithAReadableMessage(): void
    {
        [$valid, $message] = PageModel::blocksFromText('{"broken": ');

        $this->assertFalse($valid);
        $this->assertStringContainsString('not valid JSON', $message);
    }

    public function testNonObjectJsonIsRejected(): void
    {
        // A bare string or number would break every page view that reads keys.
        [$valid, $message] = PageModel::blocksFromText('"just a string"');

        $this->assertFalse($valid);
        $this->assertStringContainsString('must be a JSON object', $message);
    }

    public function testEmptyTextIsAcceptedAsNoBlocks(): void
    {
        [$valid, $json] = PageModel::blocksFromText('   ');

        $this->assertTrue($valid);
        $this->assertSame('', $json);
    }

    public function testLeadIsStrippedBecauseItLivesInTheIntroColumn(): void
    {
        // Keeping a second copy here would let the two disagree, and the page
        // would quietly use the intro while the editor edited the other one.
        [$valid, $json] = PageModel::blocksFromText('{"lead":"stale copy","stats":[]}');

        $this->assertTrue($valid);
        $this->assertSame(['stats' => []], json_decode($json, true));
    }

    public function testUnicodeAndSlashesAreStoredUnescaped(): void
    {
        [, $json] = PageModel::blocksFromText('{"note":"TIMB’s line and news/photo-1"}');

        $this->assertSame('{"note":"TIMB’s line and news/photo-1"}', $json);
    }

    public function testRoundTripPreservesContent(): void
    {
        $stored = '{"stats":[["15+","Years experience"]],"group":{"text":"A sentence."}}';

        [, $back] = PageModel::blocksFromText(PageModel::blocksToText($stored));

        $this->assertSame(json_decode($stored, true), json_decode($back, true));
    }

    public function testNestedStructureSurvivesTheRoundTrip(): void
    {
        $stored = '{"timeline":[["1900","Founded","The group starts."],["2012","TIMB","Starts as a unit."]]}';

        [, $back] = PageModel::blocksFromText(PageModel::blocksToText($stored));

        $this->assertSame($stored, $back);
    }
}

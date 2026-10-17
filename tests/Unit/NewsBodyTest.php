<?php

namespace Tests\Unit;

use App\Models\NewsModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * news_articles.body holds a JSON array of paragraphs, but the admin form
 * edits it as plain text with blank lines between paragraphs. These cover the
 * conversion in both directions, since a mistake there silently mangles or
 * drops published copy.
 */
final class NewsBodyTest extends CIUnitTestCase
{
    public function testJsonDecodesToParagraphs(): void
    {
        $this->assertSame(['one', 'two'], NewsModel::bodyToArray('["one","two"]'));
    }

    public function testEmptyBodyIsAnEmptyList(): void
    {
        $this->assertSame([], NewsModel::bodyToArray(''));
        $this->assertSame([], NewsModel::bodyToArray(null));
        $this->assertSame([], NewsModel::bodyToArray('   '));
    }

    public function testPlainTextIsTreatedAsOneParagraphRatherThanLost(): void
    {
        // A hand-edited or legacy row may hold text instead of JSON.
        $this->assertSame(['hello world'], NewsModel::bodyToArray('hello world'));
    }

    public function testBlankParagraphsAreDropped(): void
    {
        $this->assertSame(['a', 'b'], NewsModel::bodyToArray('["a","   ","b"]'));
    }

    public function testTextSplitsOnBlankLines(): void
    {
        $this->assertSame('["one","two"]', NewsModel::bodyToJson("one\n\ntwo"));
    }

    public function testSingleNewlinesStayInsideOneParagraph(): void
    {
        $this->assertSame('["one\ntwo"]', NewsModel::bodyToJson("one\ntwo"));
    }

    public function testWindowsLineEndingsSplitTheSameWay(): void
    {
        $this->assertSame('["one","two"]', NewsModel::bodyToJson("one\r\n\r\ntwo"));
    }

    public function testRunsOfBlankLinesCollapseToOneBreak(): void
    {
        $this->assertSame('["a","b"]', NewsModel::bodyToJson("a\n\n\n\n   \n\nb"));
    }

    public function testSurroundingWhitespaceIsTrimmed(): void
    {
        $this->assertSame('["a","b"]', NewsModel::bodyToJson("\n\n  a  \n\n  b  \n\n"));
    }

    public function testEmptyTextProducesAnEmptyJsonArray(): void
    {
        $this->assertSame('[]', NewsModel::bodyToJson('   '));
    }

    public function testUnicodeAndSlashesAreStoredUnescaped(): void
    {
        // The seeded article contains a typographic apostrophe; escaping it
        // would change what is already published.
        $this->assertSame('["TIMB’s line"]', NewsModel::bodyToJson('TIMB’s line'));
        $this->assertSame('["news/photo-1"]', NewsModel::bodyToJson('news/photo-1'));
    }

    public function testRoundTripThroughTheFormIsLossless(): void
    {
        $text = "First paragraph.\n\nSecond paragraph, with a comma.\n\nThird.";

        $this->assertSame($text, NewsModel::bodyToText(NewsModel::bodyToJson($text)));
    }

    public function testRoundTripPreservesStoredContent(): void
    {
        $stored = '["IMTEX FORMING will be significant.","A second paragraph."]';

        $this->assertSame($stored, NewsModel::bodyToJson(NewsModel::bodyToText($stored)));
    }
}

<?php

namespace Tests\Unit;

use App\Libraries\Paginator;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Every list in the admin, and /news, pages through this one class. A wrong
 * offset hides rows without any error, and a link that drops the query
 * string silently resets the filter someone was looking at.
 */
final class PaginatorTest extends CIUnitTestCase
{
    public function testFirstPageByDefault(): void
    {
        $p = new Paginator(45, []);

        $this->assertSame(1, $p->page);
        $this->assertSame(20, $p->perPage);
        $this->assertSame(3, $p->pages);
        $this->assertSame(0, $p->offset);
        $this->assertSame(1, $p->from());
        $this->assertSame(20, $p->to());
    }

    public function testLastPageIsPartial(): void
    {
        $p = new Paginator(45, ['page' => '3']);

        $this->assertSame(40, $p->offset);
        $this->assertSame(41, $p->from());
        $this->assertSame(45, $p->to());
        $this->assertSame([41, 42, 43, 44, 45], $p->slice(range(1, 45)));
    }

    public function testAPagePastTheEndLandsOnTheLastPage(): void
    {
        // e.g. the only item on the last page was just deleted
        $p = new Paginator(40, ['page' => '3']);

        $this->assertSame(2, $p->page);
        $this->assertSame(20, $p->offset);
    }

    public function testNonsensePageNumbersFallBackToPageOne(): void
    {
        foreach (['0', '-4', 'abc', ''] as $bad) {
            $this->assertSame(1, (new Paginator(100, ['page' => $bad]))->page, "page={$bad}");
        }
    }

    public function testAnEmptyListIsOnePageShowingNothing(): void
    {
        $p = new Paginator(0, ['page' => '5']);

        $this->assertSame(1, $p->pages);
        $this->assertSame(1, $p->page);
        $this->assertSame(0, $p->from());
        $this->assertSame(0, $p->to());
        $this->assertFalse($p->hasPages());
        $this->assertSame([], $p->slice([]));
    }

    public function testPerPageAcceptsOnlyTheOfferedSizes(): void
    {
        $this->assertSame(50, (new Paginator(500, ['per' => '50']))->perPage);
        $this->assertSame(20, (new Paginator(500, ['per' => '7']))->perPage);
        $this->assertSame(20, (new Paginator(500, ['per' => '100000']))->perPage);
    }

    public function testPerPageCanBeFixed(): void
    {
        $p = new Paginator(30, ['per' => '50'], 5, choosablePerPage: false);

        $this->assertSame(5, $p->perPage);
        $this->assertFalse($p->canChoosePerPage());
    }

    public function testACustomDefaultIsAmongTheOptions(): void
    {
        $p = new Paginator(100, [], 24);

        $this->assertContains(24, $p->perPageOptions());
        $this->assertSame([10, 20, 24, 50, 100], $p->perPageOptions());
    }

    public function testLinksKeepOtherParameters(): void
    {
        $p = new Paginator(100, ['status' => 'new', 'page' => '2', 'per' => '10']);

        $this->assertSame('?page=3&status=new&per=10', $p->url(3));
        // Page one drops the parameter so it matches the plain address.
        $this->assertSame('?status=new&per=10', $p->url(1));
    }

    public function testWithAddsAndRemovesParameters(): void
    {
        $p = new Paginator(100, ['page' => '2', 'edit' => '9']);

        $this->assertSame('?edit=4&page=2', $p->with(['edit' => 4]));
        $this->assertSame('?page=2', $p->with(['edit' => null]));
    }

    public function testThePerPageFormCarriesFiltersButNotThePage(): void
    {
        $p = new Paginator(100, ['status' => 'read', 'page' => '3', 'per' => '10']);

        $this->assertSame(['status' => 'read'], $p->hiddenFields());
    }

    public function testTwoListsOnOneScreenDoNotShareAPage(): void
    {
        $query = ['page' => '2', 'dpage' => '3'];
        $main  = new Paginator(100, $query);
        $other = new Paginator(100, $query, 10, 'dpage', 'dper');

        $this->assertSame(2, $main->page);
        $this->assertSame(3, $other->page);
        $this->assertSame('?dpage=4&page=2', $other->url(4));
    }

    public function testWindowShowsEndsAndNeighbours(): void
    {
        $this->assertSame([1, 2, 3], (new Paginator(60, []))->window());
        $this->assertSame([1, null, 9, 10, 11, null, 20], (new Paginator(400, ['page' => '10']))->window());
        // A gap of exactly one page shows the page rather than "…".
        $this->assertSame([1, 2, 3, 4, null, 20], (new Paginator(400, ['page' => '3']))->window());
    }

    public function testLinksAreRelativeQueriesOnly(): void
    {
        // Relative links survive index.php and subfolder installs.
        $this->assertStringStartsWith('?', (new Paginator(100, []))->url(2));
        $this->assertSame('?', (new Paginator(100, []))->url(1));
    }
}

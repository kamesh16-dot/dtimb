<?php

namespace Tests\Unit;

use App\Models\MachineModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * app/Views/product.php reads every one of these keys without a null guard,
 * so a machine created through the CMS — which has no stored details_json —
 * must still arrive at the view with a complete payload.
 */
final class MachineDetailsTest extends CIUnitTestCase
{
    /** Keys app/Views/product.php dereferences directly. */
    private const REQUIRED = [
        'tag', 'title', 'tagline', 'hero', 'heroCaption', 'specLabel', 'specs',
        'info', 'callout', 'features', 'types', 'mech', 'apps', 'galleries',
        'highlight', 'cta',
    ];

    private function row(array $overrides = []): array
    {
        return $overrides + [
            'slug'         => 'qa-machine',
            'name'         => 'QA Machine',
            'series_label' => 'QA-Series',
            'metric'       => '10 mm',
            'summary'      => 'A short summary.',
            'image_key'    => null,
            'visual_key'   => null,
        ];
    }

    public function testSkeletonSuppliesEveryKeyTheProductViewReads(): void
    {
        $d = MachineModel::detailsSkeleton($this->row());

        foreach (self::REQUIRED as $key) {
            $this->assertArrayHasKey($key, $d, "details payload is missing '{$key}'");
        }
    }

    public function testSkeletonSurvivesAnEmptyRow(): void
    {
        $d = MachineModel::detailsSkeleton([]);

        foreach (self::REQUIRED as $key) {
            $this->assertArrayHasKey($key, $d);
        }
    }

    public function testSkeletonUsesTheMachinesOwnContent(): void
    {
        $d = MachineModel::detailsSkeleton($this->row());

        $this->assertSame('QA Machine', $d['tag']);
        $this->assertSame('A short summary.', $d['tagline']);
        $this->assertSame([['Key metric', '10 mm']], $d['specs']);
        $this->assertSame('QA-Series QA Machine', $d['heroCaption'][0]);
    }

    public function testTitleRendersWithoutEmptyLeadingOrTrailingWords(): void
    {
        // product.php prints title[0] and title[2] only when they are not ''.
        $d = MachineModel::detailsSkeleton($this->row());

        $this->assertSame(['', 'QA Machine', ''], $d['title']);
    }

    public function testTaglineFallsBackToTheNameWhenThereIsNoSummary(): void
    {
        $d = MachineModel::detailsSkeleton($this->row(['summary' => '   ']));

        $this->assertSame('QA Machine', $d['tagline']);
    }

    public function testHeroIsNullRatherThanEmptyStringWhenThereIsNoPhoto(): void
    {
        // product.php does `if ($d['hero'])` then img_tag($d['hero'], ...),
        // which is typed string — an empty string must not reach it as truthy.
        $d = MachineModel::detailsSkeleton($this->row(['image_key' => '']));

        $this->assertNull($d['hero']);
    }

    public function testStoredPayloadKeysWinOverTheSkeleton(): void
    {
        $skeleton = MachineModel::detailsSkeleton($this->row());
        $stored   = ['tagline' => 'Real marketing copy.', 'specs' => [['Diameter', '10–254 mm']]];
        $merged   = $stored + $skeleton;

        $this->assertSame('Real marketing copy.', $merged['tagline']);
        $this->assertSame([['Diameter', '10–254 mm']], $merged['specs']);
        $this->assertSame('QA Machine', $merged['tag']); // untouched by the stored payload
    }
}

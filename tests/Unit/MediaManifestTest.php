<?php

namespace Tests\Unit;

use App\Models\MediaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The media library is merged over app/Data/ImageManifest.php by
 * timb_manifest(). img_tag() reads dimensions straight out of that result, so
 * a wrong shape here produces images with no width or height — layout shift on
 * every page — rather than a visible error.
 */
final class MediaManifestTest extends CIUnitTestCase
{
    public function testGeneratedManifestEntriesHaveTitleAndFiles(): void
    {
        $manifest = require APPPATH . 'Data/ImageManifest.php';

        $this->assertNotEmpty($manifest);

        foreach ($manifest as $key => $entry) {
            $this->assertArrayHasKey('title', $entry, "{$key} has no title");
            $this->assertArrayHasKey('files', $entry, "{$key} has no files");
            $this->assertNotEmpty($entry['files'], "{$key} has no variants");
        }
    }

    public function testEveryVariantIsAWidthHeightPair(): void
    {
        foreach (require APPPATH . 'Data/ImageManifest.php' as $key => $entry) {
            foreach ($entry['files'] as $size => $dims) {
                $this->assertCount(2, $dims, "{$key} variant {$size} is not a pair");
                $this->assertIsInt($dims[0]);
                $this->assertIsInt($dims[1]);
                $this->assertGreaterThan(0, $dims[0]);
                $this->assertGreaterThan(0, $dims[1]);
            }
        }
    }

    public function testVariantsNeverExceedTheirNominalWidth(): void
    {
        // The optimiser never upscales, and neither does an upload: a variant
        // wider than its name would mean a file that does not match the label.
        foreach (require APPPATH . 'Data/ImageManifest.php' as $key => $entry) {
            foreach ($entry['files'] as $size => $dims) {
                $this->assertLessThanOrEqual(
                    (int) $size,
                    $dims[0],
                    "{$key} variant {$size} is {$dims[0]}px wide"
                );
            }
        }
    }

    public function testVariantsDecodeFromAStoredRow(): void
    {
        $row = ['variants' => '{"1600":[1600,900],"640":[640,360]}'];

        $this->assertSame(['1600' => [1600, 900], '640' => [640, 360]], MediaModel::variants($row));
    }

    public function testMissingOrBrokenVariantsDecodeToAnEmptyList(): void
    {
        // A broken value must not become a fatal when a page renders.
        $this->assertSame([], MediaModel::variants(['variants' => null]));
        $this->assertSame([], MediaModel::variants(['variants' => '']));
        $this->assertSame([], MediaModel::variants(['variants' => 'not json']));
        $this->assertSame([], MediaModel::variants([]));
    }

    public function testTheGeneratedSizesMatchTheOptimiser(): void
    {
        // tools/optimize_images.py writes -1600 and -640; uploads must agree,
        // or an uploaded image will not resolve at the size a view asks for.
        $this->assertSame([1600, 640], MediaModel::SIZES);
    }

    public function testManifestKeysResolveToTheExpectedFilePath(): void
    {
        $manifest = require APPPATH . 'Data/ImageManifest.php';
        $key      = array_key_first($manifest);

        $this->assertFileExists(
            FCPATH . MediaModel::BASE . $key . '-640.webp',
            'the key/path convention no longer matches what is on disk'
        );
    }
}

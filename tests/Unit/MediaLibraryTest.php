<?php

namespace Tests\Unit;

use App\Libraries\MediaUsage;
use App\Models\MediaModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The media library decides whether a photo may be deleted. Missing a place a
 * photo is used means a broken picture on the live site, which is exactly how
 * product-page photos used to show as "unused".
 */
final class MediaLibraryTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('site');
    }

    public function testKeysAreFoundAnywhereInNestedProductContent(): void
    {
        $details = [
            'hero'      => 'product-tube-mill/hero',
            'galleries' => [['title' => 'Mill', 'items' => [['img' => 'product-tube-mill/mech-1']]]],
            'tagline'   => 'Not a key at all',
        ];
        $known = ['product-tube-mill/hero', 'product-tube-mill/mech-1', 'draw-bench/carriage'];

        $found = MediaUsage::keysIn($details, $known);
        sort($found);

        $this->assertSame(['product-tube-mill/hero', 'product-tube-mill/mech-1'], $found);
    }

    public function testOnlyKnownKeysCount(): void
    {
        // A slug-like string that is not an image must not mark anything used.
        $this->assertSame([], MediaUsage::keysIn(['link' => 'machines/tube-mill'], ['tube-mill/mill-1']));
        $this->assertSame([], MediaUsage::keysIn(null, ['tube-mill/mill-1']));
    }

    public function testEveryProductPagePhotoInTheBuiltInCopiesIsFound(): void
    {
        // The built-in product copies reference real library photos; if none
        // were found the delete guard would be blind to product pages again.
        $files = (require APPPATH . 'Data/ProductDetails.php') + (require APPPATH . 'Data/ExtraProductDetails.php');
        $known = array_keys(require APPPATH . 'Data/ImageManifest.php');

        $this->assertNotEmpty(MediaUsage::keysIn($files, $known));
    }

    public function testHomeGalleryRule(): void
    {
        $this->assertTrue(MediaUsage::inHomeGallery('tube-mill/mill-1'));
        $this->assertFalse(MediaUsage::inHomeGallery('product-tube-mill/hero'));
        $this->assertFalse(MediaUsage::inHomeGallery('news/imtex-2020-booth'));
    }

    public function testACopyOfTheTitleIsNotADescription(): void
    {
        $this->assertSame('', MediaModel::description(['title' => 'mill 1', 'alt_text' => 'mill 1']));
        $this->assertSame('', MediaModel::description(['title' => 'Mill 1', 'alt_text' => 'mill 1']));
        $this->assertSame('', MediaModel::description(['title' => 'mill 1', 'alt_text' => '  ']));
        $this->assertSame(
            'A tube mill on the factory floor',
            MediaModel::description(['title' => 'mill 1', 'alt_text' => 'A tube mill on the factory floor'])
        );
    }

    public function testPhotoUrlFallsBackToTheOldSingleFile(): void
    {
        // A key the library does not know resolves to assets/img/<key>.webp,
        // where news photos lived before they joined the library.
        $this->assertStringEndsWith('assets/img/news/not-in-library.webp', photo_url('news/not-in-library'));
    }

    public function testPhotoUrlUsesTheLibraryForAKnownKey(): void
    {
        $url = photo_url('tube-mill/mill-1', '640');

        $this->assertStringContainsString('assets/img/machines/tube-mill/mill-1-640.webp', $url);
    }
}

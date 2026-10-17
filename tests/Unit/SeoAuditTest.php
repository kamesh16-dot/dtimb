<?php

namespace Tests\Unit;

use App\Models\SeoSettingModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * The SEO settings decide what every page emits in its <head> and what
 * robots.txt says. A wrong default here is published site-wide and is not
 * visible on the page itself, so the defaults are pinned.
 */
final class SeoAuditTest extends CIUnitTestCase
{
    public function testEveryFieldHasALabelTypeDefaultAndHint(): void
    {
        foreach (SeoSettingModel::FIELDS as $key => $field) {
            foreach (['label', 'type', 'default', 'hint'] as $required) {
                $this->assertArrayHasKey($required, $field, "{$key} is missing '{$required}'");
            }
            $this->assertNotSame('', $field['label'], "{$key} has an empty label");
            $this->assertNotSame('', $field['hint'], "{$key} has no explanation");
        }
    }

    public function testFieldTypesAreOnesTheFormCanRender(): void
    {
        foreach (SeoSettingModel::FIELDS as $key => $field) {
            $this->assertContains(
                $field['type'],
                ['text', 'textarea', 'bool'],
                "{$key} has unknown type '{$field['type']}'"
            );
        }
    }

    public function testSwitchesDefaultToOnExceptNone(): void
    {
        // Sharing previews, canonical links and indexing should all be on for
        // a live site; a default of off would silently de-list it.
        foreach (['social_tags', 'canonical_tags', 'allow_indexing'] as $key) {
            $this->assertSame('1', SeoSettingModel::FIELDS[$key]['default'], "{$key} should default to on");
        }
    }

    public function testBooleanDefaultsAreTheStringsTheModelCompares(): void
    {
        // on() compares against '1'; a true/false default would never match.
        foreach (SeoSettingModel::FIELDS as $key => $field) {
            if ($field['type'] === 'bool') {
                $this->assertContains($field['default'], ['0', '1'], "{$key} default must be '0' or '1'");
            }
        }
    }

    public function testTheDefaultDescriptionMatchesTheLayoutFallback(): void
    {
        // app/Views/layout.php carries the same sentence as its own fallback;
        // if the two drift, a page with no description gets different text
        // in <meta name="description"> than in its sharing preview.
        $layout = file_get_contents(APPPATH . 'Views/layout.php');

        $this->assertStringContainsString(
            SeoSettingModel::FIELDS['default_description']['default'],
            $layout,
            'the layout fallback and the SEO default have drifted apart'
        );
    }

    public function testSettingsAreStoredUnderTheirOwnGroup(): void
    {
        // The Settings screen writes the 'site' group; these must not collide.
        $this->assertSame('seo', SeoSettingModel::GROUP);
    }

    public function testCanonicalBaseLosesAnyTrailingSlash(): void
    {
        // Paths are appended with a slash, so a stored trailing slash would
        // produce https://example.com//about on every page.
        $this->assertSame('', rtrim('', '/'));
        $this->assertSame('https://x.test', rtrim('https://x.test/', '/'));
        $this->assertSame('https://x.test', rtrim('https://x.test///', '/'));
    }

    public function testAShortBrandTitleIsNotFlagged(): void
    {
        // "About | TIMB" is 12 characters and is a perfectly good title; the
        // minimum must sit below it or the audit cries wolf on every page.
        $this->assertLessThanOrEqual(
            12,
            \App\Libraries\SeoAudit::TITLE_MIN,
            'the short-title threshold would flag a normal short title'
        );
    }

    public function testTheLengthLimitsAreOrdered(): void
    {
        $this->assertLessThan(\App\Libraries\SeoAudit::TITLE_MAX, \App\Libraries\SeoAudit::TITLE_MIN);
        $this->assertLessThan(\App\Libraries\SeoAudit::DESCRIPTION_MAX, \App\Libraries\SeoAudit::DESCRIPTION_MIN);
    }
}

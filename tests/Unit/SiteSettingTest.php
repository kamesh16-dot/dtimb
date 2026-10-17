<?php

namespace Tests\Unit;

use App\Models\SiteSettingModel;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * Site settings replace fields on the shared Config\Site instance, which the
 * layout renders on every page. A key naming a property that does not exist
 * would fail silently — the old screen did exactly that, writing eight keys
 * nothing ever read.
 */
final class SiteSettingTest extends CIUnitTestCase
{
    public function testEveryFieldNamesARealPropertyOnTheSiteConfig(): void
    {
        $site = config('Site');

        foreach (SiteSettingModel::FIELDS as $key => $field) {
            $this->assertArrayHasKey('property', $field, "{$key} has no property");
            $this->assertTrue(
                property_exists($site, $field['property']),
                "{$key} points at Config\\Site::\${$field['property']}, which does not exist"
            );
        }
    }

    public function testEveryFieldHasALabelTypeAndHint(): void
    {
        foreach (SiteSettingModel::FIELDS as $key => $field) {
            foreach (['label', 'type', 'hint'] as $required) {
                $this->assertArrayHasKey($required, $field, "{$key} is missing '{$required}'");
                $this->assertNotSame('', $field[$required], "{$key} has an empty '{$required}'");
            }
        }
    }

    public function testFieldTypesAreOnesTheFormCanRender(): void
    {
        foreach (SiteSettingModel::FIELDS as $key => $field) {
            $this->assertContains($field['type'], ['text', 'email', 'url'], "{$key} has unknown type");
        }
    }

    public function testDefaultsComeFromTheSiteConfig(): void
    {
        // With no stored rows the screen must still show the live values
        // rather than blanks, so a save cannot wipe the brand.
        $site = config('Site');

        foreach (SiteSettingModel::FIELDS as $key => $field) {
            $this->assertSame(
                (string) $site->{$field['property']},
                SiteSettingModel::get($key),
                "{$key} does not fall back to Config\\Site"
            );
        }
    }

    public function testRequiredFieldsAreRejectedWhenEmpty(): void
    {
        $problems = SiteSettingModel::problems(['name' => '', 'tagline' => '', 'email' => '']);

        $this->assertArrayHasKey('name', $problems);
        $this->assertArrayHasKey('tagline', $problems);
        $this->assertArrayHasKey('email', $problems);
    }

    public function testAnInvalidEmailIsRejected(): void
    {
        $problems = SiteSettingModel::problems(['name' => 'X', 'tagline' => 'Y', 'email' => 'not-an-email']);

        $this->assertArrayHasKey('email', $problems);
    }

    public function testAnInvalidUrlIsRejected(): void
    {
        $values = ['name' => 'X', 'tagline' => 'Y', 'email' => 'a@b.test', 'linkedin' => 'notaurl'];

        $this->assertArrayHasKey('linkedin', SiteSettingModel::problems($values));
    }

    public function testOptionalFieldsMayBeLeftBlank(): void
    {
        $values = ['name' => 'X', 'tagline' => 'Y', 'email' => 'a@b.test', 'linkedin' => '', 'website' => ''];

        $this->assertSame([], SiteSettingModel::problems($values));
    }

    public function testApplyingSettingsReplacesTheConfigValues(): void
    {
        $site = config('Site');
        $was  = $site->name;

        SiteSettingModel::applyToSite($site);

        $this->assertSame(SiteSettingModel::get('name'), $site->name);
        $site->name = $was;
    }

    public function testSettingsAreStoredUnderTheirOwnGroup(): void
    {
        $this->assertSame('site', SiteSettingModel::GROUP);
    }
}

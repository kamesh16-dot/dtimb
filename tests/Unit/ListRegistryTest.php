<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Lists;

/**
 * Config\Lists drives one controller, one model and one pair of views for
 * every list. A malformed definition there does not fail loudly — it produces
 * a form with a missing or unlabelled field — so the shape is checked here.
 */
final class ListRegistryTest extends CIUnitTestCase
{
    private const TYPES = ['text', 'textarea', 'number', 'decimal', 'lines', 'numbers', 'select'];

    private function lists(): array
    {
        return (new Lists())->lists;
    }

    public function testEveryListHasTheKeysTheHubAndFormsRead(): void
    {
        foreach ($this->lists() as $key => $def) {
            foreach (['label', 'description', 'table', 'consumer', 'fields'] as $required) {
                $this->assertArrayHasKey($required, $def, "list '{$key}' is missing '{$required}'");
            }
            $this->assertNotEmpty($def['fields'], "list '{$key}' has no fields");
        }
    }

    public function testEveryFieldHasALabelAndAKnownType(): void
    {
        foreach ($this->lists() as $key => $def) {
            foreach ($def['fields'] as $name => $field) {
                $this->assertArrayHasKey('label', $field, "{$key}.{$name} has no label");
                $this->assertArrayHasKey('type', $field, "{$key}.{$name} has no type");
                $this->assertContains(
                    $field['type'],
                    self::TYPES,
                    "{$key}.{$name} has unknown type '{$field['type']}'"
                );
            }
        }
    }

    public function testSelectFieldsOfferOptions(): void
    {
        foreach ($this->lists() as $key => $def) {
            foreach ($def['fields'] as $name => $field) {
                if ($field['type'] !== 'select') {
                    continue;
                }
                $this->assertArrayHasKey('options', $field, "{$key}.{$name} is a select with no options");
                $this->assertNotEmpty($field['options']);
            }
        }
    }

    public function testPayloadPositionsAllNameARealField(): void
    {
        // A position naming a field that does not exist would silently store
        // an empty value in that slot and blank part of a live page.
        foreach ($this->lists() as $key => $def) {
            foreach ($def['payload'] ?? [] as $name) {
                $this->assertArrayHasKey(
                    $name,
                    $def['fields'],
                    "{$key} stores '{$name}' positionally but has no such field"
                );
            }
        }
    }

    public function testPayloadListsNameEveryOneOfTheirFields(): void
    {
        // A field missing from the payload order could be edited but never saved.
        foreach ($this->lists() as $key => $def) {
            if (! isset($def['payload'])) {
                continue;
            }
            $this->assertSame(
                array_keys($def['fields']),
                $def['payload'],
                "{$key}: payload order and field list disagree"
            );
        }
    }

    public function testScalarPayloadListsHoldExactlyOneField(): void
    {
        foreach ($this->lists() as $key => $def) {
            if (empty($def['payloadScalar'])) {
                continue;
            }
            $this->assertCount(1, $def['fields'], "{$key} stores a bare value but defines several fields");
        }
    }

    public function testTableNamesAreUnique(): void
    {
        $tables = array_column($this->lists(), 'table');

        $this->assertSame(
            count($tables),
            count(array_unique($tables)),
            'two lists point at the same table'
        );
    }

    public function testListKeysAreUrlSafe(): void
    {
        // The key becomes a path segment: /admin/lists/<key>
        foreach (array_keys($this->lists()) as $key) {
            $this->assertMatchesRegularExpression('/^[a-z0-9_]+$/', $key, "list key '{$key}' is not url safe");
        }
    }

    public function testJsonColumnsAreEditedAsListsOrNumbers(): void
    {
        // A *_json column edited as plain text would be stored unencoded.
        foreach ($this->lists() as $key => $def) {
            foreach ($def['fields'] as $name => $field) {
                if (! str_ends_with($name, '_json')) {
                    continue;
                }
                $this->assertContains(
                    $field['type'],
                    ['lines', 'numbers'],
                    "{$key}.{$name} is a JSON column but edited as '{$field['type']}'"
                );
            }
        }
    }
}

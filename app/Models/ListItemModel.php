<?php

namespace App\Models;

use App\Libraries\CmsCache;
/**
 * One model over all the Lists Hub tables.
 *
 * Every list table shares the same shape — id, its own fields, sort_order,
 * is_active, timestamps — so the only thing that varies is the definition in
 * Config\Lists. This class reads that definition and turns rows into the
 * arrays the public views expect, and form input back into rows.
 *
 * Five of the tables keep their fields positionally in a single `payload`
 * column; `payload` in the definition names those positions in order.
 */
class ListItemModel
{
    private \CodeIgniter\Database\BaseConnection $db;

    public function __construct(private string $key, private array $def)
    {
        $this->db = db_connect();
    }

    /** @return array<string,array> every registered list, keyed by its url segment */
    public static function definitions(): array
    {
        return config('Lists')->lists;
    }

    public static function make(string $key): ?self
    {
        $def = self::definitions()[$key] ?? null;

        return $def === null ? null : new self($key, $def);
    }

    public function key(): string
    {
        return $this->key;
    }

    public function def(): array
    {
        return $this->def;
    }

    public function label(): string
    {
        return $this->def['label'];
    }

    public function table(): string
    {
        return $this->def['table'];
    }

    /** @return array<string,array> field key => definition */
    public function fields(): array
    {
        return $this->def['fields'];
    }

    private function payloadKeys(): array
    {
        return $this->def['payload'] ?? [];
    }

    private function isPayload(): bool
    {
        return $this->payloadKeys() !== [];
    }

    private function isScalarPayload(): bool
    {
        return ! empty($this->def['payloadScalar']);
    }

    // ── Reading ───────────────────────────────────────────────────────────────

    /** Raw rows in display order, including inactive ones. */
    public function all(): array
    {
        return $this->db->table($this->table())
                        ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
                        ->get()->getResultArray();
    }

    /** Active rows only, in display order. */
    public function active(): array
    {
        return CmsCache::remember('list_' . $this->key(), fn () => $this->db->table($this->table())
                        ->where('is_active', 1)
                        ->orderBy('sort_order', 'ASC')->orderBy('id', 'ASC')
                        ->get()->getResultArray());
    }

    public function find(int $id): ?array
    {
        return $this->db->table($this->table())->where('id', $id)->get()->getRowArray();
    }

    public function count(): int
    {
        return $this->db->table($this->table())->countAllResults();
    }

    /**
     * A row flattened to field key => editable value, whether the fields live
     * in their own columns or inside the payload.
     */
    public function values(array $row): array
    {
        $out = [];

        if ($this->isPayload()) {
            $payload = json_decode((string) ($row['payload'] ?? ''), true);
            if ($this->isScalarPayload()) {
                $payload = [$payload];
            }
            $payload = is_array($payload) ? array_values($payload) : [];

            foreach ($this->payloadKeys() as $i => $name) {
                $out[$name] = $this->decode($name, $payload[$i] ?? null);
            }

            return $out;
        }

        foreach ($this->fields() as $name => $_) {
            $out[$name] = $this->decode($name, $row[$name] ?? null);
        }

        return $out;
    }

    /** Stored value into the shape the form and the public views want. */
    private function decode(string $name, $value)
    {
        $type = $this->fields()[$name]['type'] ?? 'text';

        if ($type === 'lines' || $type === 'numbers') {
            if (is_array($value)) {
                return $value;
            }
            $decoded = json_decode((string) $value, true);

            return is_array($decoded) ? $decoded : [];
        }

        if ($type === 'decimal') {
            return $value === null ? null : (float) $value;
        }

        return $value;
    }

    // ── Writing ───────────────────────────────────────────────────────────────

    /**
     * Validate submitted values.
     *
     * @return array<string,string> field key => error, empty when valid
     */
    public function validate(array $values): array
    {
        $errors = [];

        foreach ($this->fields() as $name => $f) {
            $value    = $values[$name] ?? null;
            $required = ! empty($f['required']);
            $label    = $f['label'];

            if ($f['type'] === 'lines') {
                if ($required && $value === []) {
                    $errors[$name] = "{$label} needs at least one line.";
                }

                continue;
            }

            if ($f['type'] === 'numbers') {
                if ($required && count($value ?? []) === 0) {
                    $errors[$name] = "{$label} needs at least one number.";
                } elseif (array_filter($value ?? [], static fn ($n) => ! is_numeric($n)) !== []) {
                    $errors[$name] = "{$label} must be numbers separated by commas.";
                }

                continue;
            }

            if ($f['type'] === 'select') {
                if (! array_key_exists((string) $value, $f['options'])) {
                    $errors[$name] = "Choose a valid {$label}.";
                }

                continue;
            }

            if ($f['type'] === 'decimal' || $f['type'] === 'number') {
                if (trim((string) $value) === '') {
                    if ($required) {
                        $errors[$name] = "{$label} is required.";
                    }
                } elseif (! is_numeric($value)) {
                    $errors[$name] = "{$label} must be a number.";
                }

                continue;
            }

            if ($required && trim((string) $value) === '') {
                $errors[$name] = "{$label} is required.";
            }
        }

        return $errors;
    }

    /** Field values into a database row. */
    private function toRow(array $values): array
    {
        $row = [];

        if ($this->isPayload()) {
            $payload = [];
            foreach ($this->payloadKeys() as $name) {
                $payload[] = $this->encode($name, $values[$name] ?? null);
            }
            $row['payload'] = json_encode(
                $this->isScalarPayload() ? ($payload[0] ?? '') : $payload,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            return $row;
        }

        foreach ($this->fields() as $name => $_) {
            $row[$name] = $this->encode($name, $values[$name] ?? null);
        }

        return $row;
    }

    /** Editable value into its stored form. */
    private function encode(string $name, $value)
    {
        $type = $this->fields()[$name]['type'] ?? 'text';

        if ($type === 'lines') {
            $value = array_values(array_filter(array_map('trim', (array) $value), static fn ($v) => $v !== ''));

            return str_ends_with($name, '_json')
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $value;
        }

        if ($type === 'numbers') {
            $value = array_values(array_map(
                static fn ($n) => str_contains((string) $n, '.') ? (float) $n : (int) $n,
                (array) $value
            ));

            return str_ends_with($name, '_json')
                ? json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $value;
        }

        if ($type === 'decimal') {
            return (float) $value;
        }

        if ($type === 'number') {
            return (int) $value;
        }

        return trim((string) $value);
    }

    public function create(array $values): int
    {
        $row = $this->toRow($values) + [
            'sort_order' => $this->nextSortOrder(),
            'is_active'  => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        $this->db->table($this->table())->insert($row);

        return (int) $this->db->insertID();
    }

    public function update(int $id, array $values): void
    {
        $row               = $this->toRow($values);
        $row['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table($this->table())->where('id', $id)->update($row);
    }

    public function delete(int $id): void
    {
        $this->db->table($this->table())->where('id', $id)->delete();
    }

    public function setActive(int $id, bool $active): void
    {
        $this->db->table($this->table())->where('id', $id)->update([
            'is_active'  => $active ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /** Swap this row with its neighbour in the given direction. */
    public function move(int $id, string $direction): void
    {
        $rows = $this->all();
        $at   = null;
        foreach ($rows as $i => $r) {
            if ((int) $r['id'] === $id) {
                $at = $i;
                break;
            }
        }
        if ($at === null) {
            return;
        }

        $swap = $direction === 'up' ? $at - 1 : $at + 1;
        if ($swap < 0 || $swap >= count($rows)) {
            return;
        }

        // Rewrite the whole list's order: the stored values may have gaps or
        // duplicates, so swapping two numbers is not enough on its own.
        $order = array_column($rows, 'id');
        [$order[$at], $order[$swap]] = [$order[$swap], $order[$at]];

        foreach ($order as $i => $rowId) {
            $this->db->table($this->table())->where('id', $rowId)->update(['sort_order' => $i]);
        }
    }

    private function nextSortOrder(): int
    {
        $row = $this->db->table($this->table())->selectMax('sort_order')->get()->getRowArray();

        return (int) ($row['sort_order'] ?? -1) + 1;
    }
}

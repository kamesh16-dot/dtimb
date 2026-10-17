<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Redirects for addresses the site no longer serves — typically pages from a
 * previous website that still have links or search results pointing at them.
 *
 * Looked up by App\Controllers\NotFound, which runs only when no route
 * matched, so a redirect costs nothing on a normal request. A consequence is
 * that a redirect can never override a page the site still serves: the page
 * wins, and the admin screen says so.
 */
class RedirectModel extends Model
{
    protected $table         = 'redirects';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = true;

    protected $allowedFields = ['from_path', 'to_path', 'code', 'hits'];

    /** The codes the form offers. 301 is permanent, 302 temporary. */
    public const CODES = [
        301 => '301 — moved permanently',
        302 => '302 — moved temporarily',
    ];

    /** How far a chain is followed when looking for a loop. */
    private const MAX_CHAIN = 10;

    /**
     * Normalise a path for storing and matching: a single leading slash, no
     * trailing slash, no query string. The root stays "/".
     *
     * An absolute URL is left alone, so a redirect may point off-site.
     */
    public static function normalise(string $path): string
    {
        $path = trim($path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return rtrim($path, '/');
        }

        $path = (string) preg_replace('/[?#].*$/', '', $path);
        $path = '/' . trim($path, '/');

        return $path;
    }

    /** True when the target leaves this site. */
    public static function isExternal(string $path): bool
    {
        return preg_match('#^https?://#i', trim($path)) === 1;
    }

    // ── Lookup ────────────────────────────────────────────────────────────────

    /**
     * The redirect for a requested path, or null. Matching ignores case, so a
     * link written in mixed case still resolves.
     */
    public function match(string $path): ?array
    {
        $path = self::normalise($path);
        if ($path === '') {
            return null;
        }

        return $this->where('LOWER(from_path)', mb_strtolower($path))->first();
    }

    public function countHit(int $id): void
    {
        try {
            $this->builder()->where('id', $id)->set('hits', 'hits + 1', false)->update();
        } catch (\Throwable $e) {
            // A redirect must still work if the counter cannot be written.
            log_message('error', 'Could not count redirect hit: ' . $e->getMessage());
        }
    }

    // ── Admin ─────────────────────────────────────────────────────────────────

    public function allOrdered(): array
    {
        return $this->orderBy('from_path', 'ASC')->findAll();
    }

    /**
     * Check a redirect before saving.
     *
     * @param int|null $ignoreId the row being edited, excluded from the checks
     *
     * @return array<string,string> field => error, empty when it is fine
     */
    public function problems(string $from, string $to, ?int $ignoreId = null): array
    {
        $errors = [];
        $from   = self::normalise($from);
        $to     = self::normalise($to);

        if ($from === '' || $from === '/') {
            $errors['from_path'] = 'Give the old address, for example /old-page.';
        } elseif (self::isExternal($from)) {
            $errors['from_path'] = 'The old address must be a path on this site, not a full web address.';
        }

        if ($to === '') {
            $errors['to_path'] = 'Give the address to send visitors to.';
        }

        if ($errors === [] && mb_strtolower($from) === mb_strtolower($to)) {
            $errors['to_path'] = 'That would send the address to itself.';
        }

        if ($errors === []) {
            $clash = $this->where('LOWER(from_path)', mb_strtolower($from));
            if ($ignoreId !== null) {
                $clash->where('id !=', $ignoreId);
            }
            if ($clash->first() !== null) {
                $errors['from_path'] = 'There is already a redirect for that address.';
            }
        }

        if ($errors === [] && ! self::isExternal($to) && $this->wouldLoop($from, $to, $ignoreId)) {
            $errors['to_path'] = 'That would create a loop: following the redirects comes back here.';
        }

        return $errors;
    }

    /** Follow the chain from $to and see whether it returns to $from. */
    private function wouldLoop(string $from, string $to, ?int $ignoreId): bool
    {
        $seen    = [mb_strtolower($from) => true];
        $current = $to;

        for ($i = 0; $i < self::MAX_CHAIN; $i++) {
            $key = mb_strtolower(self::normalise($current));

            if (isset($seen[$key])) {
                return true;
            }
            $seen[$key] = true;

            $next = $this->where('LOWER(from_path)', $key);
            if ($ignoreId !== null) {
                $next->where('id !=', $ignoreId);
            }
            $row = $next->first();

            if ($row === null || self::isExternal((string) $row['to_path'])) {
                return false;
            }
            $current = (string) $row['to_path'];
        }

        // Ten hops without resolving is a loop for any practical purpose.
        return true;
    }

    /** Redirects whose target is itself redirected — works, but wastes a hop. */
    public function chained(): array
    {
        $rows  = $this->allOrdered();
        $byFrom = [];
        foreach ($rows as $r) {
            $byFrom[mb_strtolower((string) $r['from_path'])] = true;
        }

        $chained = [];
        foreach ($rows as $r) {
            $to = (string) $r['to_path'];
            if (! self::isExternal($to) && isset($byFrom[mb_strtolower(self::normalise($to))])) {
                $chained[(int) $r['id']] = true;
            }
        }

        return $chained;
    }
}

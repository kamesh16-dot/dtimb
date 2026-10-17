<?php

namespace App\Controllers;

use App\Models\RedirectModel;

/**
 * Runs when no route matched. Before showing the 404 page it looks for a
 * stored redirect for the requested address, which is how the Redirects
 * screen takes effect.
 *
 * Hooking here rather than on every request means the redirects table is only
 * read when a page genuinely was not found, so a normal page view costs no
 * extra query.
 */
class NotFound extends BaseController
{
    public function index()
    {
        $redirect = $this->lookup();

        if ($redirect !== null) {
            return $redirect;
        }

        return $this->response
            ->setStatusCode(404)
            ->setBody(view('errors/html/error_404', [
                'message' => timb_path(),
            ]));
    }

    /** The redirect response for this address, or null. */
    private function lookup(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        try {
            $model = new RedirectModel();
            $row   = $model->match(timb_path());

            if ($row === null) {
                return null;
            }

            $model->countHit((int) $row['id']);

            $to   = (string) $row['to_path'];
            $code = (int) $row['code'];
            $code = in_array($code, [301, 302], true) ? $code : 301;

            // Carry the query string across, so ?utm_source=… survives.
            $query = $this->request->getUri()->getQuery();
            $target = RedirectModel::isExternal($to) ? $to : site_url(ltrim($to, '/'));
            if ($query !== '') {
                $target .= (str_contains($target, '?') ? '&' : '?') . $query;
            }

            return redirect()->to($target, $code);
        } catch (\Throwable $e) {
            // A broken redirects table must still produce a normal 404.
            log_message('error', 'Redirect lookup failed: ' . $e->getMessage());

            return null;
        }
    }
}

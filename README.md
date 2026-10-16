# TIMB website (CodeIgniter 4)

## Run locally
    composer install
    cp .env.example .env          # set CI_ENVIRONMENT=development and app.baseURL for local
    php spark serve               # http://localhost:8080

Production docroot must be `public/`.

## Where things are
| What | Where |
|---|---|
| Page content (products, hero, stats, industries, email, address) | `app/Config/Site.php` |
| Layout / page markup | `app/Views/layout.php`, `app/Views/home.php` |
| Styles / JS | `public/assets/css/style.css`, `public/assets/js/main.js` |
| Image helpers (`img_tag`, `img_url`, `asset_url`) | `app/Helpers/site_helper.php` |
| Optimised images + manifest | `public/assets/img/machines/`, `app/Data/ImageManifest.php` |
| Spec sheets (linked from each machine card) | `public/spec-sheets/*.html` |

## Adding / replacing photos
    pip install pillow
    python3 tools/optimize_images.py /path/to/Machine_Images
Writes `-1600.webp` and `-640.webp` per photo and regenerates the manifest.
Reference an image in `Site.php` by key, e.g. `tube-mill/mill-1`.

## Open items (need TIMB confirmation)
- "70+ years" and "360°" were removed (not in any source document). Add a verified founding year / years-in-business stat in `Site::$stats`.
- Strip Forming Machine has photos (gallery only) but no spec sheet, so no machine card.
- Industries are plain text until industry pages exist.
# dtimb

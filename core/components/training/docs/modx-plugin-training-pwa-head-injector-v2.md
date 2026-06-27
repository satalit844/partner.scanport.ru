# Training PWA Head — MODX plugin source

- **MODX plugin name:** `Training PWA Head`
- **Production plugin ID:** `39`
- **Event:** `OnWebPagePrerender`
- **Scope:** only `/obuchenie` and its child pages.
- **Status at snapshot:** enabled.

This plugin lives in the MODX database, not in the filesystem. The following is the exact plugin source body to paste into the MODX plugin editor. It intentionally has no opening `<?php` tag because MODX evaluates plugin code itself.

```php
/* MODX plugin code — training-pwa-head-injector-v2 */
if (!isset($modx->resource) || !is_object($modx->resource)) return;

$requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
$path = (string) parse_url($requestUri, PHP_URL_PATH);
$path = '/' . ltrim($path, '/');

if ($path !== '/obuchenie' && strpos($path, '/obuchenie/') !== 0) return;

$output = isset($modx->resource->_output) ? (string) $modx->resource->_output : '';
if ($output === '' || stripos($output, '</head>') === false) return;

$marker = 'training-pwa-head-injector-v2';
if (strpos($output, $marker) !== false) return;

$head = "\n"
    . "    <!-- training-pwa-head-injector-v2 -->\n"
    . "    <link rel=\"manifest\" href=\"/assets/components/training/pwa/manifest.json?v=20260627_3\">\n"
    . "    <meta name=\"theme-color\" content=\"#17111f\">\n"
    . "    <meta name=\"mobile-web-app-capable\" content=\"yes\">\n"
    . "    <meta name=\"apple-mobile-web-app-capable\" content=\"yes\">\n"
    . "    <meta name=\"apple-mobile-web-app-status-bar-style\" content=\"default\">\n"
    . "    <meta name=\"apple-mobile-web-app-title\" content=\"Обучение\">\n"
    . "    <link rel=\"apple-touch-icon\" sizes=\"180x180\" href=\"/assets/components/training/pwa/icons/scanport-training-180-v2.png?v=20260627_2\">\n";

$updated = preg_replace('~</head\s*>~i', $head . '</head>', $output, 1, $count);
if ($count === 1) $modx->resource->_output = $updated;
```

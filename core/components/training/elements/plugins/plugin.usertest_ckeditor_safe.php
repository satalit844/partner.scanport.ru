<?php
/** @var modX $modx */

if (!$modx || !$modx->event || $modx->event->name !== 'OnManagerPageBeforeRender') {
    return;
}

if (!$modx->controller) {
    return;
}

$assetsPath = rtrim($modx->getOption('assets_path'), '/') . '/components/training/';
$assetsUrl = rtrim($modx->getOption('assets_url'), '/') . '/components/training/';
$file = $assetsPath . 'js/mgr/usertest-ckeditor-safe.js';

if (!is_file($file)) {
    return;
}

$version = filemtime($file);
$url = $assetsUrl . 'js/mgr/usertest-ckeditor-safe.js?v=' . $version;

// Важно: addHtml вставляет защиту в manager-страницу раньше обычного выполнения ExtJS-окон.
if (method_exists($modx->controller, 'addHtml')) {
    $modx->controller->addHtml('<script src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"></script>');
}

if (method_exists($modx->controller, 'addJavascript')) {
    $modx->controller->addJavascript($url);
}
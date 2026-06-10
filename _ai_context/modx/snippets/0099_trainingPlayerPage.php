<?php
/** @var modX $modx */
/** @var array $scriptProperties */

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

if (!function_exists('trainingPlayerPageRenderChunk')) {
    function trainingPlayerPageRenderChunk($corePath, $relativePath, array $placeholders = array())
    {
        $basePath = rtrim(str_replace('\\', '/', (string)$corePath), '/');
        $path = $basePath . '/elements/chunks/' . ltrim($relativePath, '/');
        if (!is_file($path)) {
            return '';
        }

        $content = (string)file_get_contents($path);
        if ($content === '') {
            return '';
        }

        $replace = array();
        foreach ($placeholders as $key => $value) {
            if (is_array($value) || is_object($value)) {
                $value = '';
            }
            $replace['{$' . $key . '}'] = (string)$value;
        }

        return strtr($content, $replace);
    }
}

if (!function_exists('trainingPlayerPageEsc')) {
    function trainingPlayerPageEsc($value)
    {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$module = isset($_GET['module']) ? (int)$_GET['module'] : (int)$modx->getOption('module', $scriptProperties, 0);
$lesson = isset($_GET['lesson']) ? (int)$_GET['lesson'] : (int)$modx->getOption('lesson', $scriptProperties, 0);
$video = isset($_GET['video']) ? (int)$_GET['video'] : (int)$modx->getOption('video', $scriptProperties, 0);
$contextKey = $modx->context ? $modx->context->get('key') : 'web';
$connectorUrl = $modx->getOption('assets_url') . 'components/training/web.connector.php';
$pageTpl = trim((string)$modx->getOption('pageTpl', $scriptProperties, 'training/player/page.tpl'));

return trainingPlayerPageRenderChunk($corePath, $pageTpl, array(
    'requested_module' => $module,
    'requested_lesson' => $lesson,
    'requested_video' => $video,
    'connector_url' => trainingPlayerPageEsc($connectorUrl),
    'context_key' => trainingPlayerPageEsc($contextKey),
));
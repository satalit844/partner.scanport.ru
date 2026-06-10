<?php
/** @var modX $modx */
$corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
require_once rtrim(str_replace('\\', '/', (string)$corePath), '/') . '/elements/snippets/_historyHelper.php';
return trainingHistoryRenderPage($modx, array(
    'manage' => true,
    'title' => $modx->resource ? (string)$modx->resource->get('pagetitle') : 'Управление историей',
    'subtitle' => 'Моя история',
));
<?php

define('MODX_API_MODE', true);
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/index.php';

$modx->getService('error', 'error.modError');
$modx->setLogLevel(modX::LOG_LEVEL_ERROR);
$html = $modx->runSnippet('UserTest', array('check_ajax'=>1));

echo $modx->toJSON(array(
        'success' => true,
        'html'    => $html,
    ));
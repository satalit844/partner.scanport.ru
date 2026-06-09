<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CONNECTORS_PATH . 'index.php';

/** @var modX $modx */

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

require_once $corePath . 'model/training/training.class.php';

$training = new Training($modx);

$path = $modx->getOption(
    'training.processors_path',
    null,
    $training->config['processorsPath']
);

$request = $modx->getService('request', 'modConnectorRequest');
$request->handleRequest([
    'processors_path' => $path,
    'location' => '',
]);
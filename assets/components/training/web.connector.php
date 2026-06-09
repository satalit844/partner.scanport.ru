<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.core.php';
require_once MODX_CORE_PATH . 'config/' . MODX_CONFIG_KEY . '.inc.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$contextKey = isset($_REQUEST['ctx'])
    ? preg_replace('/[^a-zA-Z0-9_-]/', '', (string)$_REQUEST['ctx'])
    : '';

if ($contextKey === '') {
    $contextKey = 'web';
}

$modx = new modX();
$modx->initialize($contextKey);

$corePath = $modx->getOption(
    'training.core_path',
    null,
    $modx->getOption('core_path') . 'components/training/'
);

require_once $corePath . 'model/training/training.class.php';

$training = new Training($modx);

$processorsPath = $modx->getOption(
    'training.processors_path',
    null,
    $training->config['processorsPath']
);

$action = isset($_REQUEST['action']) ? trim((string)$_REQUEST['action']) : '';

header('Content-Type: application/json; charset=UTF-8');

if ($action === '') {
    echo json_encode([
        'success' => false,
        'message' => 'Не указан action',
        'object' => ['code' => 400],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$properties = $_REQUEST;
unset($properties['action']);

$response = $modx->runProcessor($action, $properties, [
    'processors_path' => $processorsPath,
]);

if ($response instanceof modProcessorResponse) {
    $data = $response->getResponse();

    if (is_array($data)) {
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        echo (string)$data;
    }

    exit;
}

if (is_array($response)) {
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (is_object($response)) {
    if (method_exists($response, 'toArray')) {
        echo json_encode($response->toArray(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    echo json_encode([
        'success' => false,
        'message' => 'Процессор вернул неподдерживаемый объект',
        'object' => [
            'code' => 500,
            'class' => get_class($response),
        ],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if (is_string($response)) {
    $trimmed = trim($response);

    if ($trimmed !== '' && ($trimmed[0] === '{' || $trimmed[0] === '[')) {
        echo $trimmed;
    } else {
        echo json_encode([
            'success' => true,
            'message' => '',
            'object' => $response,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'Пустой или некорректный ответ процессора',
    'object' => [
        'code' => 500,
        'type' => gettype($response),
    ],
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
exit;
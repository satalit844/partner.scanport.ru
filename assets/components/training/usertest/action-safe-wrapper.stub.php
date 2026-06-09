<?php
/**
 * Training safe wrapper for /assets/components/usertest/action.php
 * The installer copies the original UserTest action.php to action.training-original.php
 * and puts this wrapper in its place.
 */
define('TRAINING_USERTEST_ACTION_SAFE_WRAPPER', true);

$originalFile = __DIR__ . '/action.training-original.php';
$debugFile = __DIR__ . '/action.training-debug.log';
$startedAt = microtime(true);
$finalized = false;
$bufferLevel = ob_get_level() + 1;

function training_usertest_action_log($debugFile, $message, array $context = array())
{
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) {
        $line .= "\n" . print_r($context, true);
    }
    $line .= "\n" . str_repeat('-', 100) . "\n";
    @file_put_contents($debugFile, $line, FILE_APPEND);
}

function training_usertest_action_finish($debugFile, $startedAt, &$finalized, $bufferLevel, $forceOkOnFatal = false)
{
    if ($finalized) {
        return;
    }
    $finalized = true;

    $body = '';
    while (ob_get_level() >= $bufferLevel) {
        $chunk = ob_get_clean();
        if ($chunk !== false) {
            $body = $chunk . $body;
        } else {
            break;
        }
    }

    $error = error_get_last();
    $fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR);
    $isFatal = $error && in_array((int)$error['type'], $fatalTypes, true);
    $code = function_exists('http_response_code') ? (int)http_response_code() : 200;

    if ($isFatal || $code >= 500 || $forceOkOnFatal) {
        training_usertest_action_log($debugFile, 'UserTest action intercepted', array(
            'http_code' => $code,
            'fatal' => $isFatal ? $error : null,
            'post' => $_POST,
            'get' => $_GET,
            'body_length' => strlen($body),
            'time' => round(microtime(true) - $startedAt, 4),
        ));

        if (!headers_sent()) {
            http_response_code(200);
            header('X-Training-UserTest-Action-Wrapper: 1');
        }

        if ($body !== '') {
            echo $body;
            return;
        }

        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode(array(
            'success' => false,
            'message' => 'UserTest action error intercepted. See assets/components/usertest/action.training-debug.log',
        ), JSON_UNESCAPED_UNICODE);
        return;
    }

    echo $body;
}

register_shutdown_function(function () use ($debugFile, $startedAt, &$finalized, $bufferLevel) {
    training_usertest_action_finish($debugFile, $startedAt, $finalized, $bufferLevel, false);
});

if (!is_file($originalFile)) {
    training_usertest_action_log($debugFile, 'Original action file not found', array(
        'originalFile' => $originalFile,
        'post' => $_POST,
        'get' => $_GET,
    ));
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
    }
    echo json_encode(array('success' => false, 'message' => 'Original UserTest action file not found'), JSON_UNESCAPED_UNICODE);
    $finalized = true;
    return;
}

ob_start();
try {
    include $originalFile;
    training_usertest_action_finish($debugFile, $startedAt, $finalized, $bufferLevel, false);
} catch (Throwable $e) {
    training_usertest_action_log($debugFile, 'Throwable in UserTest action', array(
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
        'post' => $_POST,
        'get' => $_GET,
    ));
    training_usertest_action_finish($debugFile, $startedAt, $finalized, $bufferLevel, true);
}

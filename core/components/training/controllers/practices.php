<?php
/**
 * Training practices manager action controller.
 * MODX 2.8 deprecated manager action include.
 * ExtJS version.
 * Debug URL: /manager/?a=ACTION_ID&namespace=training&tp_debug=1
 */
@ini_set('display_errors', '1');
@ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$tpDebug = !empty($_GET['tp_debug']);

if (!function_exists('tp_practices_print_line')) {
    function tp_practices_print_line($label, $value = '')
    {
        print htmlspecialchars((string)$label, ENT_QUOTES, 'UTF-8');
        if ($value !== '') {
            print ': ' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
        }
        print "\n";
    }
}

try {
    if (!isset($modx) || !is_object($modx)) {
        if (isset($this) && is_object($this) && isset($this->modx) && is_object($this->modx)) {
            $modx = $this->modx;
        }
    }

    if (!isset($modx) || !is_object($modx)) {
        print '<pre>';
        tp_practices_print_line('ERROR', 'MODX object is not available');
        tp_practices_print_line('FILE', __FILE__);
        tp_practices_print_line('PHP', PHP_VERSION);
        print '</pre>';
        exit;
    }

    $corePath = $modx->getOption('training.core_path', null, $modx->getOption('core_path') . 'components/training/');
    $corePath = rtrim(str_replace('\\', '/', $corePath), '/') . '/';

    $assetsUrl = $modx->getOption('training.assets_url', null, $modx->getOption('assets_url') . 'components/training/');
    $assetsUrl = rtrim($assetsUrl, '/') . '/';

    $assetsPath = $modx->getOption('training.assets_path', null, $modx->getOption('assets_path') . 'components/training/');
    $assetsPath = rtrim(str_replace('\\', '/', $assetsPath), '/') . '/';

    $connectorUrl = $assetsUrl . 'connector.php';
    $cacheBust = time();
    $cssUrl = $assetsUrl . 'css/mgr/practices.css?v=' . $cacheBust;
    $jsUrl = $assetsUrl . 'js/mgr/practices/practices.panel.js?v=' . $cacheBust;

    $checks = array(
        'controller' => __FILE__,
        'corePath' => $corePath,
        'assetsPath' => $assetsPath,
        'assetsUrl' => $assetsUrl,
        'connectorUrl' => $connectorUrl,
        'jsFile' => $assetsPath . 'js/mgr/practices/practices.panel.js',
        'cssFile' => $assetsPath . 'css/mgr/practices.css',
        'connectorFile' => $assetsPath . 'connector.php',
        'processorHelper' => $corePath . 'processors/mgr/practice/_helper.php',
        'practicesGetList' => $corePath . 'processors/mgr/practice/practices/getlist.php',
        'practicesCreate' => $corePath . 'processors/mgr/practice/practices/create.php',
        'practicesUpdate' => $corePath . 'processors/mgr/practice/practices/update.php',
        'attemptsGetList' => $corePath . 'processors/mgr/practice/attempts/getlist.php',
        'messagesGetList' => $corePath . 'processors/mgr/practice/messages/getlist.php',
    );

    if ($tpDebug) {
        print '<pre style="font:13px/1.4 monospace; padding:16px; white-space:pre-wrap;">';
        tp_practices_print_line('Training practices ExtJS controller debug');
        tp_practices_print_line('PHP', PHP_VERSION);
        foreach ($checks as $label => $path) {
            if (stripos($label, 'url') !== false || $label === 'assetsUrl') {
                tp_practices_print_line($label, $path);
                continue;
            }
            if ($label === 'corePath' || $label === 'assetsPath') {
                tp_practices_print_line($label, $path . ' | ' . (is_dir($path) ? 'OK' : 'FAIL'));
                continue;
            }
            tp_practices_print_line($label, $path . ' | ' . (is_file($path) ? 'OK' : 'FAIL'));
        }
        print '</pre>';
        return;
    }

    print '<div id="training-panel-practices-wrap"></div>';
    print '<link rel="stylesheet" type="text/css" href="' . htmlspecialchars($cssUrl, ENT_QUOTES, 'UTF-8') . '" />';
    print '<script type="text/javascript">';
    print 'window.Training=window.Training||{};';
    print 'window.Training.config=window.Training.config||{};';
    print 'window.Training.config.connector_url=' . json_encode($connectorUrl) . ';';
    print '</script>';
    print '<script type="text/javascript" src="' . htmlspecialchars($jsUrl, ENT_QUOTES, 'UTF-8') . '"></script>';
    print '<script type="text/javascript">';
    print '(function(){';
    print 'var tries=0;';
    print 'function start(){';
    print 'tries++;';
    print 'if(!window.Ext||!window.MODx||!Ext.ComponentMgr||!Ext.ComponentMgr.types||!Ext.ComponentMgr.types["training-panel-practices"]){';
    print 'if(tries<200){window.setTimeout(start,50);return;}';
    print 'var el=document.getElementById("training-panel-practices-wrap");';
    print 'if(el){el.innerHTML="<div style=\\"padding:20px;color:#c00;font:14px Arial\\">Не загрузилась ExtJS-панель практических заданий. Проверьте консоль браузера и файл assets/components/training/js/mgr/practices/practices.panel.js</div>";}';
    print 'return;';
    print '}';
    print 'Ext.onReady(function(){';
    print 'if(Ext.getCmp("training-panel-practices")){return;}';
    print 'MODx.add({xtype:"training-panel-practices"});';
    print '});';
    print '}';
    print 'start();';
    print '})();';
    print '</script>';

    return;
} catch (Exception $e) {
    print '<pre>';
    tp_practices_print_line('Training practices fatal');
    tp_practices_print_line('Message', $e->getMessage());
    tp_practices_print_line('File', $e->getFile());
    tp_practices_print_line('Line', $e->getLine());
    tp_practices_print_line('Trace', $e->getTraceAsString());
    print '</pre>';
    exit;
} catch (Throwable $e) {
    print '<pre>';
    tp_practices_print_line('Training practices fatal');
    tp_practices_print_line('Message', $e->getMessage());
    tp_practices_print_line('File', $e->getFile());
    tp_practices_print_line('Line', $e->getLine());
    tp_practices_print_line('Trace', $e->getTraceAsString());
    print '</pre>';
    exit;
}

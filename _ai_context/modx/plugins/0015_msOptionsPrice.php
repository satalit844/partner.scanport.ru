<?php
/** @var array $scriptProperties */
/** @var msoptionsprice $msoptionsprice */

$fqn = $modx->getOption('msoptionsprice_class', null, 'msoptionsprice.msoptionsprice', true);
$path = $modx->getOption('msoptionsprice_core_path', null,
    $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/msoptionsprice/');
if (!$msoptionsprice = $modx->getService($fqn, '', $path . 'model/',
    array('core_path' => $path))
) {
    return false;
}

$className = 'msOptionsPrice' . $modx->event->name;
$modx->loadClass('msOptionsPricePlugin', $msoptionsprice->getOption('modelPath') . 'msoptionsprice/systems/', true,
    true);
$modx->loadClass($className, $msoptionsprice->getOption('modelPath') . 'msoptionsprice/systems/', true, true);
if (class_exists($className)) {
    /** @var msOptionsPricePlugin $handler */
    $handler = new $className($modx, $scriptProperties);
    $handler->run();
}
return;
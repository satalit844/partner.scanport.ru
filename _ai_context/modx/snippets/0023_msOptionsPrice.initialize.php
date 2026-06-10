<?php
/** @var array $scriptProperties */
$corePath = $modx->getOption('msoptionsprice_core_path', null,
    $modx->getOption('core_path', null, MODX_CORE_PATH) . 'components/msoptionsprice/');
/** @var msoptionsprice $msoptionsprice */
$msoptionsprice = $modx->getService('msoptionsprice', 'msoptionsprice', $corePath . 'model/msoptionsprice/',
    array('core_path' => $corePath));
if (!$msoptionsprice) {
    return 'Could not load msoptionsprice class!';
}
$msoptionsprice->initialize($modx->context->key, $scriptProperties);
$msoptionsprice->loadResourceJsCss($scriptProperties);

/** @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('miniShop2');
$miniShop2->initialize($modx->context->key);

$processCart = $scriptProperties['processCart'] = (bool)$modx->getOption('processCart', $scriptProperties);

if ($processCart AND $msoptionsprice->getOption('allow_remains')) {
    $items = $miniShop2->cart->get();

    foreach ($items as $key => $item) {
        $count = $modx->getOption('count', $item, 0, true);
        $options = $modx->getOption('options', $item, array(), true);
        $mid = (int)$modx->getOption('modification', $options);
        if (empty($mid)) {
            continue;
        }
        $mo = $modx->getObject('msopModification', array('id' => $mid));
        if (!$mo) {
            continue;
        }

        $mCount = $mo->get('count');
        if ($count < $mCount) {
            continue;
        } else {
            $count = $mCount;
        }

        if ($count < 1) {
            $miniShop2->cart->remove($key);
        } else {
            $miniShop2->cart->change($key, $count);
        }
    }
}
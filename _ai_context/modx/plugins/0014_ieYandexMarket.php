<?php
/**
 * @var modX $modx
 * @var IeYandexMarket $ieyandexmarket
 * @var ieYandexMarketTools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $scriptProperties
 * @var string $mode
 * @var bool $checking
 */

$ieyandexmarket = $modx->getService('ieyandexmarket', 'IeYandexMarket');
if (!$ieyandexmarket) return;

$tools = $ieyandexmarket->getTools();
//if (!$tools->hasAddition('Addition')) return;

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($tools->getServices($mode));
        break;
}
return;
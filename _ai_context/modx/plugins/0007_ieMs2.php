<?php
/**
 * @var modX $modx
 * @var IeMs2 $iems2
 * @var MsIeService $service
 * @var array $scriptProperties
 * @var string $serviceName
 * @var bool $checking
 */

$iems2 = $modx->getService('iems2', 'IeMs2');
if (!$iems2) return;

$tools = $iems2->getTools();
if (!$tools->hasAddition('minishop2')) return;

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($iems2->getTools()->getServices($mode));
        break;
}
return;
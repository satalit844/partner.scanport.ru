<?php
/**
 * @var modX $modx
 * @var IeMsProductRemains $iemsproductremains
 * @var ieMsProductRemainsTools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $scriptProperties
 * @var string $mode
 * @var bool $checking
 */

$iemsproductremains = $modx->getService('iemsproductremains', 'IeMsProductRemains');
if (!$iemsproductremains) return;

$tools = $iemsproductremains->getTools();
if (!$tools->hasAddition('msproductremains')) return;

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($tools->getServices($mode));
        break;
    case 'msieOnGetServiceFields':
        /*if ($service instanceof MsIeResourceExportService) {
            $fields = $tools->getCustomFields();
            $modx->event->output($fields);
        }*/
        break;
}
return;
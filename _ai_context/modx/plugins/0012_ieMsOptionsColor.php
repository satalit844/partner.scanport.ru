<?php
/**
 * @var modX $modx
 * @var IeMsOptionsColor $iemsoptionscolor
 * @var ieMsOptionsColorTools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $scriptProperties
 * @var string $mode
 * @var bool $checking
 */

$iemsoptionscolor = $modx->getService('iemsoptionscolor', 'IeMsOptionsColor');
if (!$iemsoptionscolor) return;

$tools = $iemsoptionscolor->getTools();
//if (!$tools->hasAddition('Addition')) return;

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
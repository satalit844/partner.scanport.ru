<?php
/**
 * @var modX $modx
 * @var IeMsOptionsPrice2 $iemsoptionsprice2
 * @var ieMsOptionsPrice2Tools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $data
 * @var array $scriptProperties
 * @var string $mode
 * @var string $dataType
 * @var bool $checking
 */

$iemsoptionsprice2 = $modx->getService('iemsoptionsprice2', 'IeMsOptionsPrice2');
if (!$iemsoptionsprice2) return;

$tools = $iemsoptionsprice2->getTools();
if (!$tools->hasAddition('msoptionsprice')) return;

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($tools->getServices($mode));
        break;
    case 'msieOnGetServiceFields':
        if ($service instanceof IeMs2ProductImportService) {
            if ($msopService = $tools->getService(Msie::MODE_IMPORT, 'ieMsOptionsPrice2ImportService')) {
                if ($fields = $msopService->getCustomFields()) {
                    $modx->event->output($fields);
                }
            }
        }
        break;
    case 'msieOnImportStart':
        if (
            $worker instanceof IeMs2ProductImportWorker &&
            $tools->hasOptionsPrice2Fields($worker->getFields())
        ) {
            if ($msopService = $tools->getService(Msie::MODE_IMPORT, 'ieMsOptionsPrice2ImportService')) {
                if ($msopWorker = $msopService->getWorker()) {
                    $worker->addSubWorker($msopWorker, 'iemsoptionsprice2');
                    $msopWorker->initialize();
                }
            }
        }
        break;
    case 'msieOnImport':
        if (
            $worker instanceof IeMs2ProductImportWorker &&
            $worker->hasSubWorker('iemsoptionsprice2')
        ) {
            $msopWorker = $worker->getSubWorker('iemsoptionsprice2');
            $data = $msopWorker->filterOptionsPrice2Data($data);
            $data['rid'] = $modx->getOption('id', $object, 0);
            $data = $msopWorker->afterPrepareData($data);
            $msopWorker->work($data);
        }
        break;
}
return;
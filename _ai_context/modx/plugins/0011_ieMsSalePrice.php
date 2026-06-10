<?php
/**
 * @var modX $modx
 * @var IeMsSalePrice $iemssaleprice
 * @var ieMsSalePriceTools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $config
 * @var array $scriptProperties
 * @var string $mode
 * @var bool $checking
 */

$iemssaleprice = $modx->getService('iemssaleprice', 'IeMsSalePrice');
if (!$iemssaleprice) return;

$tools = $iemssaleprice->getTools();
if (!$tools->hasAddition('mssaleprice')) return;

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($tools->getServices($mode));
        break;
    case 'msieOnGetServiceFields':
        if ($service instanceof IeMs2ProductExportService) {
            $fields = $tools->getSalePriceCustomFields('mssp_', 'msSalePrice');
            $modx->event->output($fields);
        }
        break;
    case 'msieOnExportStart':
        if (
            $worker instanceof IeMs2ProductExportWorker &&
            $tools->hasSalePriceFields($worker->getFields())
        ) {
            $ids = $worker->getResourceIds();
            $index = $worker->unsetField('mssp_prices');
            if ($counts = $tools->getCountSalePrice($ids)) {
                $fields = array();
                foreach ($counts as $count) {
                    $key = "mssp_price_{$count}";
                    $fields[] = $key;
                    $worker->addFieldLexicon($key, $modx->lexicon('msie_alias_price', array('count' => $count)));
                    $worker->addPrepareFieldMethod("mssp_price_{$count}", $tools, 'prepareFieldPrices');
                }
                $worker->insertField($fields, $index);
            }
        }
        break;
}
return;
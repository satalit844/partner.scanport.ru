<?php
/**
 * @var modX $modx
 * @var IeGallery $iegallery
 * @var IeGalleryTools $tools
 * @var MsIeService $service
 * @var MsIeWorker $worker
 * @var array $scriptProperties
 * @var string $mode
 * @var bool $checking
 */

$iegallery = $modx->getService('iegallery', 'IeGallery');
if (!$iegallery) return;

$tools = $iegallery->getTools();

switch ($modx->event->name) {
    case 'msieOnLoadServices':
        $modx->event->output($tools->getServices($mode));
        break;
    case 'msieOnGetServiceFields':
        if (
            $service instanceof MsIeResourceExportService ||
            $service instanceof MsIeResourceImportService ||
            $service instanceof IeMs2CategoryImportService
        ) {
            $fields = $tools->getGalleryCustomFields('', 'Photo gallery');
            $modx->event->output($fields);
        }
        break;
    case 'msieOnExportStart':
        if (
            $worker instanceof MsIeResourceExportWorker &&
            $galleryType = $worker->getSetting('gallery_type')
        ) {
            if ($worker->hasField('gallery')) {
                $worker->addPrepareFieldMethod('gallery', $tools, 'prepareFieldGallery');
            }
            if ($worker->hasField('attach_thumb')) {
                $worker->addPrepareFieldMethod('attach_thumb', $tools, 'prepareFieldAttachThumb');
                $attachSettings = $worker->getSetting('gallery_attach_settings', '{"thumb":"small","width":150}');
                $attachSettings = $worker->tools->fromJSON($attachSettings, array());
                $worker->setSetting('attach_settings', $attachSettings);
            }
        }
        break;
    case 'msieOnExportBeforeArchive':
        if (
            $worker instanceof MsIeResourceExportWorker &&
            $worker->hasField('gallery') &&
            $galleryType = $worker->getSetting('gallery_type')
        ) {
            $serviceName = $tools->getServiceNameByGallery(Msie::MODE_EXPORT, $galleryType);
            if ($galleryService = $tools->getService(Msie::MODE_EXPORT, $serviceName)) {
                if ($galleryWorker = $galleryService->getWorker()) {
                    $galleryWorker->copyWorkerScope($worker);
                    $galleryWorker->initialize();
                    if ($galleryWorker->isAddImagesToArchive()) {
                        $files[] = $galleryWorker->getCopyImagePath();
                        $modx->event->returnedValues['files'] = $files;
                    }
                }
            }
        }
        break;

    case 'msieOnImportStart':
        if (
            $worker instanceof MsIeResourceImportWorker &&
            $worker->hasField('gallery') &&
            $galleryType = $worker->getSetting('gallery_type')
        ) {
            $galleryWorker = $worker->getSubWorker('iegallery');
            if (!$galleryWorker) {
                $serviceName = $tools->getServiceNameByGallery(Msie::MODE_IMPORT, $galleryType);
                if ($galleryService = $tools->getService(Msie::MODE_IMPORT, $serviceName)) {
                    if ($galleryWorker = $galleryService->getWorker()) {
                        $worker->addSubWorker($galleryWorker, 'iegallery');
                        $galleryWorker->setWorkingDirectory($worker->getWorkingDirectory());
                        $galleryWorker->initialize();
                    }
                }
            }
            if ($galleryWorker) {
                $worker->addPrepareFieldMethod('gallery', $galleryWorker, 'prepareFieldGallery');
            }
        }
        break;
    case 'msieOnImport':
        if (
            $worker instanceof MsIeResourceImportWorker &&
            $worker->hasSubWorker('iegallery')
        ) {
            if (empty($data['gallery'])) return;
            $galleryWorker = $worker->getSubWorker('iegallery');
            $data = array(
                'id' => $object['id'],
                'gallery' => $data['gallery'],
            );
            $galleryWorker->work($data);
        }
        break;

}
return;
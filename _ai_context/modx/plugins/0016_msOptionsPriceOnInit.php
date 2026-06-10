<?php
if ($modx->event->name != 'OnMODXInit') {
    return;
}

$classGallery = trim($this->xpdo->getOption('msoptionsprice_modification_gallery_class', null,
    'msProductFile', true));

if (!empty($classGallery)) {
    $modx->loadClass($classGallery);
    $modx->map[$classGallery]['composites']['modificationImages'] = array(
        'class'       => 'msopModificationImage',
        'local'       => 'id',
        'foreign'     => 'image',
        'cardinality' => 'many',
        'owner'       => 'local',
    );
}
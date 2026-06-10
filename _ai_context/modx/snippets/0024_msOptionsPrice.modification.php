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
/** @var pdoFetch $pdoFetch */

$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, $scriptProperties);
} else {
    return false;
}
$pdoFetch->setConfig($scriptProperties);
$pdoFetch->addTime('pdoTools loaded.');

$tpl = $scriptProperties['tpl'] = $modx->getOption('tpl', $scriptProperties, 'tpl.msOptionsPrice.modification', true);
$product = $scriptProperties['product'] = $modx->getOption('product', $scriptProperties, $modx->resource->id, true);
$type = $scriptProperties['type'] = $modx->getOption('type', $scriptProperties, 1, true);
$limit = $scriptProperties['limit'] = $modx->getOption('limit', $scriptProperties, 10, true);
$class = $scriptProperties['class'] = $modx->getOption('class', $scriptProperties, 'msopModification', true);

$oid = $scriptProperties['oid'] = $modx->getOption('oid', $scriptProperties);
$byOptions = $scriptProperties['byOptions'] = $modx->getOption('byOptions', $scriptProperties, '{}', true);
$strict = $scriptProperties['strict'] = (bool)$modx->getOption('strict', $scriptProperties,
    $msoptionsprice->getOption('search_modification_strict', null, false, true), true);
$excludeType = $scriptProperties['excludeType'] = $modx->getOption('excludeType', $scriptProperties, '0', true);
$processOptions = $scriptProperties['processOptions'] = (bool)$modx->getOption('processOptions', $scriptProperties, 0,
    true);

$outputSeparator = $scriptProperties['outputSeparator'] = $modx->getOption('outputSeparator', $scriptProperties, "\n",
    true);

$classGallery = trim($msoptionsprice->getOption('modification_gallery_class', null,
    'msProductFile', true));

// Add user parameters
foreach (array('byOptions') as $k) {
    if (!empty($scriptProperties[$k])) {
        $tmp = $scriptProperties[$k];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        ${$k} = $scriptProperties[$k] = $tmp;
    }
}

$msoptionsprice->initialize($modx->context->key, $scriptProperties);

/** @var msProduct $product */
$product = $modx->getObject('msProduct', $product);
if (!$product OR !($product instanceof msProduct)) {
    return "[msOptionsPrice] The resource with id = {$product->id} is not instance of msProduct.";
}
$price = $product->get('price');
$oldPrice = $product->get('old_price');

$type = $msoptionsprice->explodeAndClean($type);
$excludeType = array_merge(array(0), $msoptionsprice->explodeAndClean($excludeType));

$where = array(
    "{$class}.rid"     => $product->id,
    "{$class}.type:IN" => $type,
    "{$class}.active"  => true,
);
if (empty($showZeroPrice)) {
    $where["{$class}.price:>"] = 0;
}

// add filter
if (!empty($resources)) {
    $where["{$class}.id:IN"] = is_string($resources) ? array_map('trim', explode(',', $resources)) : $resources;
}

$groupby = array(
    "{$class}.id",
);
$leftJoin = array(
    "msProduct"     => array(
        "class" => "msProduct",
        "on"    => "msProduct.id = {$class}.rid",
    ),
    "msProductData" => array(
        "class" => "msProductData",
        "on"    => "msProductData.id = {$class}.rid",
    ),
    "msVendor"      => array(
        "class" => "msVendor",
        "on"    => "msProductData.vendor = msVendor.id",
    ),
    "Option"        => array(
        "class" => "msopModificationOption",
        "on"    => "Option.mid = {$class}.id",
    ),
);
$innerJoin = array();
$select = array(
    $class          => $modx->getSelectColumns($class, $class),
    'msProduct'     => $modx->getSelectColumns('msProduct', 'msProduct', 'product_'),
    'msProductData' => $modx->getSelectColumns('msProductData', 'msProductData', 'data_'),
    'msVendor'      => $modx->getSelectColumns('msVendor', 'msVendor', 'vendor_'),
);


/* TODO */
// Include thumbnails
if (!empty($includeThumbs)) {
    $thumbs = $msoptionsprice->explodeAndClean($includeThumbs);
}

foreach (array('where', 'leftJoin', 'innerJoin', 'select', 'groupby') as $v) {
    if (!empty($scriptProperties[$v])) {
        $tmp = $scriptProperties[$v];
        if (!is_array($tmp)) {
            $tmp = json_decode($tmp, true);
        }
        if (is_array($tmp)) {
            $$v = array_merge($$v, $tmp);
        }
    }
    unset($scriptProperties[$v]);
}
$pdoFetch->addTime('Conditions prepared');

if (!empty($byOptions)) {
    $oid = 0;
    if ($modification = $msoptionsprice->getModificationByOptions(
        $product->id,
        $byOptions,
        $strict,
        array(0),
        array_diff($excludeType, $type)
    )
    ) {
        $oid = $modification['id'];
    }
}

if (!is_null($oid)) {
    $where["{$class}.id"] = (int)$oid;
}

$default = array(
    'class'             => $class,
    'where'             => $where,
    'leftJoin'          => $leftJoin,
    'innerJoin'         => $innerJoin,
    'select'            => $select,
    'sortby'            => "CAST({$class}.price AS DECIMAL(10,2))",
    'sortdir'           => 'ASC',
    'groupby'           => implode(', ', $groupby),
    'return'            => !empty($returnIds) ? 'ids' : 'data',
    'nestedChunkPrefix' => 'minishop2_',
);

// Merge all properties and run!
$pdoFetch->setConfig(array_merge($default, $scriptProperties), false);
$rows = $pdoFetch->run();

// Process rows
$output = array();
if (!empty($rows) AND is_array($rows)) {
    $idx = $pdoFetch->idx;
    foreach ($rows as $k => $row) {
        /** @var msopModification $m */
        if (!$m = $modx->getObject('msopModification', (int)$row['id'])) {
            continue;
        }
        $row['idx'] = $idx++;

        $options = $modx->call('msopModificationOption', 'getOptions',
            array(&$modx, $row['id'], $row['rid'], null, $processOptions));
        $row = array_merge($row, array('options' => $options));

        $row['modification'] = $row;
        $row['price'] = $msoptionsprice->getCostByType($row['type'], $row['price'], $price);
        $row['old_price'] = $msoptionsprice->getOldCostByModification($row['modification']);

        $row['price'] = $msoptionsprice->miniShop2->formatPrice($row['price']);
        $row['old_price'] = $msoptionsprice->miniShop2->formatPrice($row['old_price']);
        $row['weight'] = $msoptionsprice->miniShop2->formatWeight($row['weight']);

        // process modification thumbs
        $row['thumbs'] = null;
        if (!empty($thumbs)) {
            $images = $m->loadThumbs($thumbs);
            if (empty($images)) {
                $images = $m->loadThumb($thumbs);
            }
            foreach ($thumbs as $thumb) {
                if (!empty($images[$thumb][0])) {
                    $row[$thumb] = $images[$thumb][0];
                }
            }
            $row['thumbs'] = $images;
        }

        $output[] = $pdoFetch->getChunk($tpl, $row);
        $rows[$k] = $row;
    }

}

if ($scriptProperties['return'] == 'data') {
    return $rows;
}

$log = '';
if ($modx->user->hasSessionContext('mgr') AND !empty($showLog)) {
    $log .= '<pre class="msOptionsPriceLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}
// Return output
if (!empty($returnIds) AND is_string($rows)) {
    $modx->setPlaceholder('msOptionsPrice.log', $log);
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $rows);
    } else {
        return $rows;
    }
} else {
    $output = implode($outputSeparator, $output) . $log;
    if (!empty($toPlaceholder)) {
        $modx->setPlaceholder($toPlaceholder, $output);
    } else {
        return $output;
    }
}
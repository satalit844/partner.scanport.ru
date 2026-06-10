<?php
$classModification = 'msopModification';
$classOption = 'msopModificationOption';

if (!function_exists('getModificationOptions')) {
    function getModificationOptions(modX & $modx, $rid = null, $showZeroCount = true)
    {
        $options = [];

        $classModification = 'msopModification';
        $classOption = 'msopModificationOption';
        $classMsOption = 'msOption';

        $q = $modx->newQuery($classOption);
        $q->innerJoin($classModification, $classModification, "{$classModification}.id = {$classOption}.mid");
        $q->leftJoin($classMsOption, $classMsOption, "{$classOption}.key = {$classMsOption}.key");

        $q->select($modx->getSelectColumns($classOption, $classOption));
        $q->select($modx->getSelectColumns($classMsOption, $classMsOption, '', ['caption'], false));

        $q->where([
            "{$classOption}.rid"          => "{$rid}",
            "{$classModification}.active" => true,
        ]);
        if (!$showZeroCount) {
            $q->andCondition([
                "{$classModification}.count:>" => 0,
            ]);
        }

        if ($q->prepare() AND $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $k = $row['key'];
                if (!isset($options[$k])) {
                    $options[$k] = [$row['value']];
                } else {
                    $options[$k][] = $row['value'];
                }

                foreach ($row as $x => $value) {
                    $options[$k . '.' . $x] = $value;
                }
            }
        }

        return $options;
    }
}

if (!function_exists('getOptionColors')) {
    function getOptionColors(modX & $modx, $rid = null, $key = null)
    {
        $colors = [];

        $classColor = 'msocColor';
        $q = $modx->newQuery($classColor);
        $q->where([
            "{$classColor}.rid" => "{$rid}",
            "{$classColor}.key" => "{$key}",
        ]);
        $q->andCondition([
            "{$classColor}.color:!="       => "",
            "OR:{$classColor}.color2:!="   => "",
            "OR:{$classColor}.pattern:!="  => "",
            "OR:{$classColor}.pattern2:!=" => "",
        ]);

        $q->select($modx->getSelectColumns($classColor, $classColor, '', ['rid', 'key'], true));
        if ($q->prepare() AND $q->stmt->execute()) {
            while ($row = $q->stmt->fetch(PDO::FETCH_ASSOC)) {
                $k = $row['value'];
                $colors[$k] = $row;
            }
        }

        return $colors;
    }
}

/** @var modX $modx */
/** @var array $scriptProperties */
$tpl = $modx->getOption('tpl', $scriptProperties, 'tpl.msOptions');
$showZeroCount = (bool)$modx->getOption('showZeroCount', $scriptProperties, true);
$showProductOptions = (bool)$modx->getOption('showProductOptions', $scriptProperties, false);
$processColors = (bool)$modx->getOption('processColors', $scriptProperties, false);

if (!empty($input) && empty($product)) {
    $product = $input;
}
if (!empty($name) && empty($options)) {
    $options = $name;
}
$names = array_map('trim', explode(',', $options));
$names = array_diff($names, ['']);

$product = !empty($product) && $product != $modx->resource->id
    ? $modx->getObject('msProduct', $product)
    : $modx->resource;
if (!($product instanceof msProduct)) {
    return "[msOptions] The resource with id = {$product->id} is not instance of msProduct.";
}
$modx->lexicon->load('minishop2:product');

$constraints = $modx->getOption('constraintOptions', $scriptProperties);
if ($constraints AND !is_array($constraints)) {
    $constraints = json_decode($constraints, true);
}

$data = $productData = getModificationOptions($modx, $product->id, $showZeroCount);
if ($showProductOptions) {
    $productData = $product->loadOptions();
}

$options = $captions = $relations = $colors = [];
foreach ($names as $name) {
    $option = $modx->getOption($name, $data);
    if (!$option AND $showProductOptions) {
        $option = $modx->getOption($name, $productData);
    }
    if ($option) {
        $option = array_unique($option);
        sort($option);
        $options[$name] = $option;

        // process captions
        if (isset($data[$name . '.caption'])) {
            $captions[$name] = $data[$name . '.caption'];
        } else {
            $captions[$name] = $modx->lexicon('ms2_product_' . $name);
        }

        // process relations
        if (!empty($constraints) AND array_key_exists($name, $constraints)) {
            $relations[$name] = [];

            $q = $modx->newQuery($classOption);
            $q->innerJoin($classModification, $classModification, "{$classModification}.id = {$classOption}.mid");
            $q->leftJoin($classOption, 'Values', "{$classOption}.mid = Values.mid");
            $q->where([
                "{$classOption}.rid"          => "{$product->id}",
                "{$classOption}.key"          => "{$name}",
                "{$classModification}.active" => true,
            ]);
            if (!$showZeroCount) {
                $q->andCondition([
                    "{$classModification}.count:>" => 0,
                ]);
            }

            $q->limit(0);
            $q->sortby("{$classOption}.key", "ASC");
            $q->groupby("{$classOption}.mid");
            $q->select("{$classOption}.value, GROUP_CONCAT(CONCAT_WS('=',`Values`.`key`,`Values`.`value`) SEPARATOR '&') as value");

            $rows = [];
            if ($q->prepare() && $q->stmt->execute()) {
                if (!$rows = $q->stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP)) {
                    $rows = [];
                }
                foreach ($rows as $key => &$row) {
                    foreach ($row as $k => $v) {
                        parse_str($v, $row[$k]);
                        ksort($row[$k]);
                        unset($row[$k][$name]);

                        $v = [];
                        foreach ($row[$k] as $param => $value) {
                            $v[] = "{$param}={$value}";
                        }
                        $row[$k] = implode('&', $v);
                    }
                }
                $relations[$name] = $rows;
            }
        }

        // process colors
        if ($processColors) {
            $colors[$name] = getOptionColors($modx, $product->id, $name);
        }
    }
}

if (!empty($scriptProperties['sortOptions'])) {
    $sorts = array_map('trim', explode(',', $scriptProperties['sortOptions']));
    foreach ($sorts as $sort) {
        $sort = explode(':', $sort);
        $key = $sort[0];
        $order = SORT_ASC;
        if (!empty($sort[1])) {
            $order = constant($sort[1]);
        }
        $type = SORT_STRING;
        if (!empty($sort[2])) {
            $type = constant($sort[2]);
        }

        $first = null;
        if (!empty($sort[3])) {
            $first = $sort[3];
        }

        if (array_key_exists($key, $options) AND is_array($options[$key]) AND !empty($options[$key])) {

            if ($type) {
                array_multisort($options[$key], $order, $type);
            } /* @var modSnippet $script */
            elseif (!empty($sort[2]) AND $script = $modx->getObject('modSnippet', ['name' => $sort[2]])) {
                $script->_cacheable = false;
                $script->_processed = false;
                if ($values = $script->process(['key' => $key, 'values' => $options[$key], 'order' => $order])) {
                    $options[$key] = $values;
                }
            }

            if ($first && ($index = array_search($first, $options[$key])) !== false) {
                unset($options[$key][$index]);
                array_unshift($options[$key], $first);
            }
        }
    }
}

/** @var pdoTools $pdoTools */
$pdoTools = $modx->getService('pdoTools');

return $pdoTools->getChunk($tpl, [
    'product'     => isset($product) ? $product->toArray() : [],
    'options'     => $options,
    'captions'    => $captions,
    'relations'   => $relations,
    'constraints' => $constraints,
    'colors'      => $colors,
]);
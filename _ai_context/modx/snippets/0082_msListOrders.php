<?php
/** @var array $scriptProperties */
/** @var mslistorders $mslistorders */
if (!$mslistorders = $modx->getService('mslistorders', 'mslistorders', $modx->getOption('mslistorders_core_path', null,
        $modx->getOption('core_path') . 'components/mslistorders/'
    ) . 'model/mslistorders/', $scriptProperties
)
) {
    return 'Could not load mslistorders class!';
}
$mslistorders->initialize($modx->context->key, $scriptProperties);

$id = $scriptProperties['id'] = (int)$modx->getOption('id', $scriptProperties, 0, true);
$user = $scriptProperties['user'] = (int)$modx->getOption('user', $scriptProperties, $modx->user->id);
$tpl = $scriptProperties['tpl'] = $modx->getOption('tpl', $scriptProperties, 'tpl.msListOrders', true);
$tplOrder = $scriptProperties['tplOrder'] = $modx->getOption('tplOrder', $scriptProperties, 'tpl.msListOrders.order',
    true
);

$includeTVs = $scriptProperties['includeTVs'] = $modx->getOption('includeTVs', $scriptProperties, '', true);
$includeProducts = $scriptProperties['includeProducts'] = (boolean)$modx->getOption('includeProducts',
    $scriptProperties, false, true
);
$includeThumbs = $scriptProperties['includeThumbs'] = $modx->getOption('includeThumbs', $scriptProperties, '', true);
$actions = $scriptProperties['actions'] = $modx->getOption('actions', $scriptProperties, 'view,repeat,cart,pay,cancel',
    true
);
$tvPrefix = $scriptProperties['tvPrefix'] = $modx->getOption('tvPrefix', $scriptProperties, 'tv.', true);
$propkey = $scriptProperties['propkey'] = $modx->getOption('propkey', $scriptProperties,
    sha1(serialize($scriptProperties)), true
);

$mslistorders->saveProperties($scriptProperties);
$isVersionMiniShopNew = $mslistorders->isVersionMiniShopNew();

/** @var miniShop2 $miniShop2 */
$miniShop2 = $modx->getService('miniShop2');
$miniShop2->initialize($modx->context->key);

/** @var pdoFetch $pdoFetch */
$fqn = $modx->getOption('pdoFetch.class', null, 'pdotools.pdofetch', true);
$path = $modx->getOption('pdofetch_class_path', null, MODX_CORE_PATH . 'components/pdotools/model/', true);
if ($pdoClass = $modx->loadClass($fqn, $path, false, true)) {
    $pdoFetch = new $pdoClass($modx, []);
} else {
    return false;
}
$pdoFetch->setConfig(array_merge($scriptProperties, ['includeTVs' => '']));
$pdoFetch->addTime('pdoTools loaded.');


// default
$class = 'msOrder';

// where
$where = [];

if ($user) {
    $where[$class . '.user_id'] = $user;
}
if ($id) {
    $where[$class . '.id'] = $id;
    $tpl = $tplOrder;
}

// get auth
if (!$mslistorders->authenticated) {
    return $mslistorders->lexicon('err_no_auth');
}
// join

$leftJoin = [
    'msOrderStatus' => [
        'class' => 'msOrderStatus',
        'on' => 'msOrder.status = msOrderStatus.id',
    ],
    'msDelivery' => [
        'class' => 'msDelivery',
        'on' => 'msOrder.delivery = msDelivery.id',
    ],
    'msPayment' => [
        'class' => 'msPayment',
        'on' => 'msOrder.payment = msPayment.id',
    ],
    'msOrderAddress' => [
        'class' => 'msOrderAddress',
        'on' => $isVersionMiniShopNew ? 'msOrder.id = msOrderAddress.order_id' : 'msOrder.address = msOrderAddress.id',
    ],
    'msCustomerProfile' => [
        'class' => 'msCustomerProfile',
        'on' => 'msOrder.user_id = msCustomerProfile.id',
    ],
    'modUser' => [
        'class' => 'modUser',
        'on' => 'msOrder.user_id=modUser.id',
    ],
    'modUserProfile' => [
        'class' => 'modUserProfile',
        'on' => 'msOrder.user_id = modUserProfile.internalKey',
    ],
];
 
// select
$select = [
    'msOrder' => $modx->getSelectColumns('msOrder', 'msOrder', ''),
    'msOrderStatus' => $modx->getSelectColumns('msOrderStatus', 'msOrderStatus', 'status_'),
    'msDelivery' => $modx->getSelectColumns('msDelivery', 'msDelivery', 'delivery_'),
    'msPayment' => $modx->getSelectColumns('msPayment', 'msPayment', 'payment_'),
    'msOrderAddress' => $modx->getSelectColumns('msOrderAddress', 'msOrderAddress', 'address_'),
    'msCustomerProfile' => $modx->getSelectColumns('msCustomerProfile', 'msCustomerProfile', 'customer_'),
    'modUser' => $modx->getSelectColumns('modUser', 'modUser', 'user_'),
    'modUserProfile' => $modx->getSelectColumns('modUserProfile', 'modUserProfile', 'profile_'),
];

// Add custom parameters
foreach (['where', 'leftJoin', 'select'] as $v) {
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

$default = [
    'class' => $class,
    'leftJoin' => $leftJoin,
    'where' => $where,
    'select' => $select,
    'groupby' => $class . '.id',
    'sortby' => $class . '.id',
    'sortdir' => 'DESC',
    'return' => 'data',
    'nestedChunkPrefix' => 'mslistorders_',
    'disableConditions' => true,
];

// Merge all properties
$config = array_merge($default, $scriptProperties, ['includeTVs' => '']);
$orders = $total = [];

// total orders
$config['groupby'] = '';
$config['limit'] = $config['offset'] = 0;
$config['select'] = [
    'msOrder' => 'SUM(msOrder.cost) as sum, COUNT(msOrder.id) as orders',
];
$pdoFetch->setConfig($config, false);
$tmp = $pdoFetch->run();
$total = reset($tmp);

// total products
$config['groupby'] = '';
$config['limit'] = $config['offset'] = 0;
$config['leftJoin']['msOrderProduct'] = [
    'class' => 'msOrderProduct',
    'on' => 'msOrder.id = msOrderProduct.order_id',
];
$config['select'] = [
    'msOrderProduct' => 'SUM(msOrderProduct.count) as products',
];
$pdoFetch->setConfig($config, false);
$tmp = $pdoFetch->run();
$total = array_merge($total, reset($tmp));

$pdoFetch->setConfig(array_merge($default, $scriptProperties, ['includeTVs' => '', 'return' => !empty($returnIds) ? 'ids' : 'data']), false);
$rows = $pdoFetch->run();
if (!empty($returnIds)) {
    return $rows;
}

foreach ($rows as $order) {
    $order['propkey'] = $propkey;
    $order['idx'] = $pdoFetch->idx++;

    $order['cost'] = $miniShop2->formatPrice($order['cost']);
    $order['cart_cost'] = $miniShop2->formatPrice($order['cart_cost']);
    $order['delivery_cost'] = $miniShop2->formatPrice($order['delivery_cost']);

    $order['total_position'] = 0;
    $order['total_product'] = 0;

    $products = [];
    if ($includeProducts or $id) {
        $q = $modx->newQuery('msOrderProduct');
        $q->innerJoin('msProduct', 'msProduct', 'msOrderProduct.product_id = msProduct.id');
        $q->innerJoin('msProductData', 'msProductData', 'msOrderProduct.product_id = msProductData.id');
        $q->leftJoin('msVendor', 'msVendor', 'msProductData.vendor = msVendor.id');
        $q->where([
            'order_id' => $order['id'],
        ]);
        $q->groupby('msOrderProduct.id');
        $q->select($modx->getSelectColumns('msOrderProduct', 'msOrderProduct'));

        // Include products thumbnails
        $thumbs = $mslistorders->explodeAndClean($includeThumbs);
        if (!empty($thumbs)) {
            foreach ($thumbs as $thumb) {
                $q->leftJoin('msProductFile', $thumb,
                    "`{$thumb}`.product_id = msOrderProduct.product_id AND `{$thumb}`.parent != 0 AND `{$thumb}`.path LIKE '%/{$thumb}/%'"
                );
                $q->select("`{$thumb}`.url as '{$thumb}'");
            }
        }

        // Include products tvs
        $includeTVs = $mslistorders->explodeAndClean($includeTVs);
        if (!empty($includeTVs)) {
            $c = $modx->newQuery('modTemplateVar', ['name:IN' => $includeTVs]);
            $c->select('id,name,type,default_text');
            if ($c->prepare() and $c->stmt->execute()) {
                $tvs = [];
                while ($tv = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                    $name = strtolower($tv['name']);
                    $alias = 'TV' . $name;

                    $q->leftJoin('modTemplateVarResource', $alias, '`TV' . $name . '`.`contentid` = `' . 'msProduct' . '`.`id` AND `TV' . $name . '`.`tmplvarid` = ' . $tv['id']);
                    $q->query['columns']['`' . $tvPrefix . $tv['name'] . '`'] = 'IFNULL(`' . $alias . '`.`value`, ' . $modx->quote($tv['default_text']) . ')';
                }
            }
        }

        $q->select($modx->getSelectColumns('msProduct', 'msProduct', 'product_'));
        $q->select($modx->getSelectColumns('msProductData', 'msProductData', 'data_'));
        $q->select($modx->getSelectColumns('msVendor', 'msVendor', 'vendor_'));
        $q->groupby("msOrderProduct.product_id");

        if ($q->prepare() and $q->stmt->execute()) {
            while ($product = $q->stmt->fetch(PDO::FETCH_ASSOC)) {

                $product['price'] = $miniShop2->formatPrice($product['price']);
                $product['cost'] = $miniShop2->formatPrice($product['cost']);
                $product['weight'] = $miniShop2->formatWeight($product['weight']);
                $product['options'] = json_decode($product['options'], true);

                $options = $modx->call('msProductData', 'loadOptions', [&$modx, $product['product_id']]);
                $product = array_merge($product, $options);

                $products[] = $product;
                $order['total_product'] += $product['count'];
            }
        }

    }
    $order['products'] = $products;
    $order['total_position'] = count($products);
    $orders[] = $order;
}

$data = [
    'total' => $total,
    'orders' => $orders,
    'actions' => $mslistorders->explodeAndClean($actions),
];

$output = $pdoFetch->getChunk($tpl, $data);

if (!empty($showLog)) {
    $mslistorders->log('[mslistorders]', $data);
    $output .= '<pre class="msOrdersLog">' . print_r($pdoFetch->getTime(), 1) . '</pre>';
}

if (!empty($toPlaceholder)) {
    $modx->setPlaceholder($toPlaceholder, $output);
} else {
    return $output;
}
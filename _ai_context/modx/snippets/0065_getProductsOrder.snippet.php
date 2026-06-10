<?php
if(!$id) return;

$q = $modx->newQuery('msOrderProduct', array('order_id' => $id));
$q->leftjoin('msProductData', 'product', 'product.id = msOrderProduct.product_id');

$q->limit(1000);
// если не нужны все поля, то можно раскомментировать строку ниже
//$q->select('product_id, name, price,count,cost,weight, options, product.image, product.article');
$q->select(array(
    'msOrderProduct.*',
    'product.*'
));

$q->prepare();
$q->stmt->execute();
$res = $q->stmt->fetchAll(PDO::FETCH_ASSOC);

$pdoTools = $modx->getService('pdoTools');
$output = '';

$modx->log(1,'[getProductsOrder.snippet $res] '. print_r($res,1));

foreach($res as $v){
    $output .=  $pdoTools->getChunk($tpl, $v);
}

return $output;
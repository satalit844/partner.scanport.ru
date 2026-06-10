<?php
$xpdo_meta_map['UserTestResultCategorys']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_result_categorys',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'result_id' => NULL,
    'category_id' => NULL,
    'variant_id' => NULL,
    'cat_point' => NULL,
    'max_point' => 0.0,
  ),
  'fieldMeta' => 
  array (
    'result_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'category_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'variant_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'cat_point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
    ),
    'max_point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
      'default' => 0.0,
    ),
  ),
  'indexes' => 
  array (
    'result_id' => 
    array (
      'alias' => 'result_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'result_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'category_id' => 
    array (
      'alias' => 'category_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'category_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'variant_id' => 
    array (
      'alias' => 'variant_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'variant_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'UserTestResults' => 
    array (
      'class' => 'UserTestResults',
      'local' => 'result_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

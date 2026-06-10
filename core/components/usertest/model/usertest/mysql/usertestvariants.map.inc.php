<?php
$xpdo_meta_map['UserTestVariants']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_variants',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'test_id' => NULL,
    'variant_set_id' => NULL,
    'start_point' => NULL,
    'end_point' => NULL,
    'passed' => 0,
    'result' => '',
    'category_id' => 0,
    'haker' => 0,
  ),
  'fieldMeta' => 
  array (
    'test_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'variant_set_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'start_point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
    ),
    'end_point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
    ),
    'passed' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'result' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'category_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'haker' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
  ),
  'indexes' => 
  array (
    'variant_set_id' => 
    array (
      'alias' => 'variant_set_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'variant_set_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'test_id' => 
    array (
      'alias' => 'test_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'test_id' => 
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
  ),
  'composites' => 
  array (
    'UserTestTestVariantLink' => 
    array (
      'class' => 'UserTestTestVariantLink',
      'local' => 'id',
      'foreign' => 'variant_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'UserTestCategorys' => 
    array (
      'class' => 'UserTestCategorys',
      'local' => 'category_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'UserTestVariantSets' => 
    array (
      'class' => 'UserTestVariantSets',
      'local' => 'variant_set_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

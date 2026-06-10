<?php
$xpdo_meta_map['UserTestGroupsLink']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_groups_link',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'menuindex' => 0,
    'group_id' => NULL,
    'test_id' => NULL,
  ),
  'fieldMeta' => 
  array (
    'menuindex' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
      'index' => 'index',
    ),
    'group_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'test_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'group_id' => 
    array (
      'alias' => 'group_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'group_id' => 
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
    'menuindex' => 
    array (
      'alias' => 'menuindex',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'menuindex' => 
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
    'UserTestGroups' => 
    array (
      'class' => 'UserTestGroups',
      'local' => 'group_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'UserTestTests' => 
    array (
      'class' => 'UserTestTests',
      'local' => 'test_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

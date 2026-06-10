<?php
$xpdo_meta_map['UserTestResults']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_results',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'test_id' => NULL,
    'user_id' => NULL,
    'user_name' => '',
    'user_email' => '',
    'date' => NULL,
    'test_point' => NULL,
    'max_point' => 0.0,
    'test_time' => NULL,
    'variant_id' => NULL,
    'status_id' => NULL,
    'comment' => '',
    'session' => '',
    'invite_id' => 0,
    'properties' => '',
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
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'user_name' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'user_email' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '100',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'date' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'test_point' => 
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
    'test_time' => 
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
    'status_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '4',
      'phptype' => 'integer',
      'null' => true,
    ),
    'comment' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'session' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'invite_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'properties' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
  ),
  'indexes' => 
  array (
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
    'user_id' => 
    array (
      'alias' => 'user_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'user_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'user_name' => 
    array (
      'alias' => 'user_name',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'user_name' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'status_id' => 
    array (
      'alias' => 'status_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'status_id' => 
        array (
          'length' => '',
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'invite_id' => 
    array (
      'alias' => 'invite_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'invite_id' => 
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
    'UserTestResultAnswers' => 
    array (
      'class' => 'UserTestResultAnswers',
      'local' => 'id',
      'foreign' => 'result_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UserTestResultCategorys' => 
    array (
      'class' => 'UserTestResultCategorys',
      'local' => 'id',
      'foreign' => 'result_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);

<?php
$xpdo_meta_map['UserTestAnswers']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_answers',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'menuindex' => 0,
    'question_id' => NULL,
    'answer' => '',
    'type_file' => NULL,
    'file' => '',
    'point' => NULL,
    'right' => 0,
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
    'question_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'answer' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'type_file' => 
    array (
      'dbtype' => 'int',
      'precision' => '4',
      'phptype' => 'integer',
      'null' => true,
    ),
    'file' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
    ),
    'right' => 
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
    'question_id' => 
    array (
      'alias' => 'question_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'question_id' => 
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
    'right' => 
    array (
      'alias' => 'right',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'right' => 
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
    'UserTestQuestions' => 
    array (
      'class' => 'UserTestQuestions',
      'local' => 'question_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

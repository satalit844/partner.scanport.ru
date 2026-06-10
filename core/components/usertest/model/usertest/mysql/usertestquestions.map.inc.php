<?php
$xpdo_meta_map['UserTestQuestions']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_questions',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'menuindex' => 0,
    'test_id' => NULL,
    'parent' => 0,
    'category_id' => NULL,
    'question' => '',
    'type' => NULL,
    'type_file' => NULL,
    'file' => '',
    'extended' => '',
    'max_point' => 0.0,
    'random_answer' => 0,
    'validate' => 0,
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
    'test_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'parent' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'category_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
    ),
    'question' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'type' => 
    array (
      'dbtype' => 'int',
      'precision' => '4',
      'phptype' => 'integer',
      'null' => true,
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
    'extended' => 
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
      'default' => '',
    ),
    'max_point' => 
    array (
      'dbtype' => 'double',
      'phptype' => 'double',
      'null' => true,
      'default' => 0.0,
    ),
    'random_answer' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'validate' => 
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
    'parent' => 
    array (
      'alias' => 'parent',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'parent' => 
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
    'UserTestTestQuestionLink' => 
    array (
      'class' => 'UserTestTestQuestionLink',
      'local' => 'id',
      'foreign' => 'question_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UserTestAnswers' => 
    array (
      'class' => 'UserTestAnswers',
      'local' => 'id',
      'foreign' => 'question_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);

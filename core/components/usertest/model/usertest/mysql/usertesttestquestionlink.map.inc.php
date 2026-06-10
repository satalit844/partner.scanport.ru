<?php
$xpdo_meta_map['UserTestTestQuestionLink']= array (
  'package' => 'usertest',
  'version' => '1.1',
  'table' => 'usertest_test_question_link',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'MyISAM',
  ),
  'fields' => 
  array (
    'menuindex' => 0,
    'test_id' => NULL,
    'question_id' => NULL,
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
    'question_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'phptype' => 'integer',
      'null' => true,
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
    'UserTestQuestions' => 
    array (
      'class' => 'UserTestQuestions',
      'local' => 'question_id',
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

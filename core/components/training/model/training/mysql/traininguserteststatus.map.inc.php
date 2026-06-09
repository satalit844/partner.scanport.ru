<?php
$xpdo_meta_map['TrainingUserTestStatus']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_user_test_status',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'course_id' => 0,
    'module_id' => 0,
    'usertest_test_id' => 0,
    'user_id' => 0,
    'last_result_id' => 0,
    'attempts' => 0,
    'passed' => 0,
    'status' => 'not_started',
    'last_score' => 0.0,
    'last_passedon' => NULL,
    'updatedon' => NULL,
  ),
  'fieldMeta' => 
  array (
    'course_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'module_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'usertest_test_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'user_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'last_result_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'attempts' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'passed' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 0,
    ),
    'status' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '32',
      'phptype' => 'string',
      'null' => false,
      'default' => 'not_started',
    ),
    'last_score' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '7,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0.0,
    ),
    'last_passedon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'updatedon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'test_user' => 
    array (
      'alias' => 'test_user',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'usertest_test_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'user_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'module_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'course_user' => 
    array (
      'alias' => 'course_user',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'course_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'user_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' => 
  array (
    'Course' => 
    array (
      'class' => 'TrainingCourse',
      'local' => 'course_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'Module' => 
    array (
      'class' => 'TrainingModule',
      'local' => 'module_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
    'User' => 
    array (
      'class' => 'modUser',
      'local' => 'user_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

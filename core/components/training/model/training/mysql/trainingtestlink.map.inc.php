<?php
$xpdo_meta_map['TrainingTestLink']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_test_links',
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
    'link_type' => 'module',
    'sort_order' => 0,
    'is_required' => 1,
    'max_attempts' => 0,
    'min_pass_percent' => 0.0,
    'block_next_module_until_passed' => 0,
    'createdon' => NULL,
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
    'link_type' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '32',
      'phptype' => 'string',
      'null' => false,
      'default' => 'module',
    ),
    'sort_order' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'is_required' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 1,
    ),
    'max_attempts' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'min_pass_percent' => 
    array (
      'dbtype' => 'decimal',
      'precision' => '5,2',
      'phptype' => 'float',
      'null' => false,
      'default' => 0.0,
    ),
    'block_next_module_until_passed' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 0,
    ),
    'createdon' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
  ),
  'indexes' => 
  array (
    'course_id' => 
    array (
      'alias' => 'course_id',
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
      ),
    ),
    'module_id' => 
    array (
      'alias' => 'module_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'module_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'usertest_test_id' => 
    array (
      'alias' => 'usertest_test_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'usertest_test_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'unique_link' => 
    array (
      'alias' => 'unique_link',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'course_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'module_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'usertest_test_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'link_type' => 
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
  ),
);

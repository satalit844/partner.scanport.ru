<?php
$xpdo_meta_map['TrainingCourseAccess']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_course_access',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'course_id' => 0,
    'principal_type' => 'user',
    'principal_id' => 0,
    'access_role' => 'employee',
    'assigned_by' => 0,
    'active_from' => NULL,
    'active_to' => NULL,
    'is_active' => 1,
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
    'principal_type' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '16',
      'phptype' => 'string',
      'null' => false,
      'default' => 'user',
    ),
    'principal_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'access_role' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '16',
      'phptype' => 'string',
      'null' => false,
      'default' => 'employee',
    ),
    'assigned_by' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'active_from' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'active_to' => 
    array (
      'dbtype' => 'datetime',
      'phptype' => 'datetime',
      'null' => true,
    ),
    'is_active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 1,
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
    'principal' => 
    array (
      'alias' => 'principal',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' => 
      array (
        'principal_type' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'principal_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'course_principal' => 
    array (
      'alias' => 'course_principal',
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
        'principal_type' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'principal_id' => 
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
    'AssignedByUser' => 
    array (
      'class' => 'modUser',
      'local' => 'assigned_by',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

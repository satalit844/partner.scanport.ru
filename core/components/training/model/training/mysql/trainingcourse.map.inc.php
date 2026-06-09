<?php
$xpdo_meta_map['TrainingCourse']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_courses',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' => 
  array (
    'engine' => 'InnoDB',
  ),
  'fields' => 
  array (
    'resource_id' => 0,
    'is_active' => 1,
    'is_sequential' => 1,
    'source_presentation' => '',
    'presentation_pdf' => '',
    'slides_dir' => '',
    'presentation_status' => 'none',
    'createdon' => NULL,
    'updatedon' => NULL,
  ),
  'fieldMeta' => 
  array (
    'resource_id' => 
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'is_active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 1,
    ),
    'is_sequential' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 1,
    ),
    'source_presentation' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'presentation_pdf' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'slides_dir' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'presentation_status' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '32',
      'phptype' => 'string',
      'null' => false,
      'default' => 'none',
    ),
    'createdon' => 
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
    'resource_id' => 
    array (
      'alias' => 'resource_id',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' => 
      array (
        'resource_id' => 
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'composites' => 
  array (
    'Modules' => 
    array (
      'class' => 'TrainingModule',
      'local' => 'id',
      'foreign' => 'course_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Accesses' => 
    array (
      'class' => 'TrainingCourseAccess',
      'local' => 'id',
      'foreign' => 'course_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'UserCourses' => 
    array (
      'class' => 'TrainingUserCourse',
      'local' => 'id',
      'foreign' => 'course_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'TestLinks' => 
    array (
      'class' => 'TrainingTestLink',
      'local' => 'id',
      'foreign' => 'course_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
  'aggregates' => 
  array (
    'Resource' => 
    array (
      'class' => 'modResource',
      'local' => 'resource_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

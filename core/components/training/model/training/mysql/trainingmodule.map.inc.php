<?php
$xpdo_meta_map['TrainingModule']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_modules',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' =>
  array (
    'engine' => 'InnoDB',
  ),
  'fields' =>
  array (
    'course_id' => 0,
    'resource_id' => 0,
    'is_active' => 1,
    'is_required' => 1,
    'duration_seconds' => 0,
    'video_status' => 'none',
    'presentation_status' => 'none',
    'source_video' => '',
    'source_presentation' => '',
    'presentation_pdf' => '',
    'slides_dir' => '',
    'createdon' => NULL,
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
    'is_required' =>
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => false,
      'default' => 1,
    ),
    'duration_seconds' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'video_status' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '32',
      'phptype' => 'string',
      'null' => false,
      'default' => 'none',
    ),
    'presentation_status' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '32',
      'phptype' => 'string',
      'null' => false,
      'default' => 'none',
    ),
    'source_video' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
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
    'Lessons' =>
    array (
      'class' => 'TrainingModuleLesson',
      'local' => 'id',
      'foreign' => 'module_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Videos' =>
    array (
      'class' => 'TrainingModuleVideo',
      'local' => 'id',
      'foreign' => 'module_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Slides' =>
    array (
      'class' => 'TrainingModuleSlide',
      'local' => 'id',
      'foreign' => 'module_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Progresses' =>
    array (
      'class' => 'TrainingUserModuleProgress',
      'local' => 'id',
      'foreign' => 'module_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'TestLinks' =>
    array (
      'class' => 'TrainingTestLink',
      'local' => 'id',
      'foreign' => 'module_id',
      'cardinality' => 'many',
      'owner' => 'local',
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

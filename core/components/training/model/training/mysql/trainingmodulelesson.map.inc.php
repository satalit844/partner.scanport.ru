<?php
$xpdo_meta_map['TrainingModuleLesson']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_module_lessons',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' =>
  array (
    'engine' => 'InnoDB',
  ),
  'fields' =>
  array (
    'module_id' => 0,
    'title' => '',
    'description' => NULL,
    'sort_order' => 0,
    'preview_image' => '',
    'source_video' => '',
    'duration_seconds' => 0,
    'video_status' => 'none',
    'source_presentation' => '',
    'presentation_pdf' => '',
    'slides_dir' => '',
    'presentation_status' => 'none',
    'is_default' => 0,
    'is_active' => 1,
    'createdon' => NULL,
    'updatedon' => NULL,
  ),
  'fieldMeta' =>
  array (
    'module_id' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'title' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'description' =>
    array (
      'dbtype' => 'text',
      'phptype' => 'string',
      'null' => true,
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
    'preview_image' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'source_video' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
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
    'is_default' =>
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
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
    'module_sort' =>
    array (
      'alias' => 'module_sort',
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
        'sort_order' =>
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
  ),
  'aggregates' =>
  array (
    'Module' =>
    array (
      'class' => 'TrainingModule',
      'local' => 'module_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
  'composites' =>
  array (
    'Videos' =>
    array (
      'class' => 'TrainingModuleVideo',
      'local' => 'id',
      'foreign' => 'lesson_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
    'Slides' =>
    array (
      'class' => 'TrainingModuleSlide',
      'local' => 'id',
      'foreign' => 'lesson_id',
      'cardinality' => 'many',
      'owner' => 'local',
    ),
  ),
);

<?php
$xpdo_meta_map['TrainingModuleSlide']= array (
  'package' => 'training',
  'version' => '1.1',
  'table' => 'training_module_slides',
  'extends' => 'xPDOSimpleObject',
  'tableMeta' =>
  array (
    'engine' => 'InnoDB',
  ),
  'fields' =>
  array (
    'module_id' => 0,
    'lesson_id' => 0,
    'slide_no' => 0,
    'image' => '',
    'timecode_ms' => 0,
    'is_active' => 1,
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
    'lesson_id' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'slide_no' =>
    array (
      'dbtype' => 'int',
      'precision' => '10',
      'attributes' => 'unsigned',
      'phptype' => 'integer',
      'null' => false,
      'default' => 0,
    ),
    'image' =>
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'timecode_ms' =>
    array (
      'dbtype' => 'bigint',
      'precision' => '20',
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
    'lesson_id' =>
    array (
      'alias' => 'lesson_id',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' =>
      array (
        'lesson_id' =>
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'lesson_slide' =>
    array (
      'alias' => 'lesson_slide',
      'primary' => false,
      'unique' => true,
      'type' => 'BTREE',
      'columns' =>
      array (
        'lesson_id' =>
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'slide_no' =>
        array (
          'collation' => 'A',
          'null' => false,
        ),
      ),
    ),
    'lesson_timecode' =>
    array (
      'alias' => 'lesson_timecode',
      'primary' => false,
      'unique' => false,
      'type' => 'BTREE',
      'columns' =>
      array (
        'lesson_id' =>
        array (
          'collation' => 'A',
          'null' => false,
        ),
        'timecode_ms' =>
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
    'Lesson' =>
    array (
      'class' => 'TrainingModuleLesson',
      'local' => 'lesson_id',
      'foreign' => 'id',
      'cardinality' => 'one',
      'owner' => 'foreign',
    ),
  ),
);

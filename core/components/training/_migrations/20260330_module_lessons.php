<?php
/**
 * Migration for TrainingModuleLesson.
 * Run once via CLI or MODX Console.
 */

$root = dirname(dirname(dirname(dirname(__DIR__))));
if (!defined('MODX_API_MODE')) {
    define('MODX_API_MODE', true);
}
require_once $root . '/index.php';

/** @var modX $modx */
if (!$modx instanceof modX) {
    die("MODX bootstrap failed\n");
}

$prefix = $modx->getOption('table_prefix', null, 'modx_');
$tables = array(
    'lessons' => $prefix . 'training_module_lessons',
    'modules' => $prefix . 'training_modules',
    'videos' => $prefix . 'training_module_videos',
    'slides' => $prefix . 'training_module_slides',
    'site_content' => $prefix . 'site_content',
);

function execSql(modX $modx, $sql, $silent = false) {
    try {
        $stmt = $modx->prepare($sql);
        $ok = $stmt && $stmt->execute();
        if (!$ok && !$silent) {
            $info = $stmt ? $stmt->errorInfo() : array('no_stmt');
            echo "SQL ERROR: " . print_r($info, true) . "\n";
            echo $sql . "\n\n";
        }
        return $ok;
    } catch (Exception $e) {
        if (!$silent) {
            echo "EXCEPTION: " . $e->getMessage() . "\n";
            echo $sql . "\n\n";
        }
        return false;
    }
}

function columnExists(modX $modx, $table, $column) {
    $sql = "SHOW COLUMNS FROM `{$table}` LIKE '" . addslashes($column) . "'";
    $stmt = $modx->query($sql);
    return $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
}

function indexExists(modX $modx, $table, $index) {
    $sql = "SHOW INDEX FROM `{$table}` WHERE Key_name = '" . addslashes($index) . "'";
    $stmt = $modx->query($sql);
    return $stmt && $stmt->fetch(PDO::FETCH_ASSOC);
}

function tableExists(modX $modx, $table) {
    $sql = "SHOW TABLES LIKE '" . addslashes($table) . "'";
    $stmt = $modx->query($sql);
    return $stmt && $stmt->fetchColumn();
}

echo "Using prefix: {$prefix}\n";

if (!tableExists($modx, $tables['lessons'])) {
    execSql($modx, "
        CREATE TABLE `{$tables['lessons']}` (
          `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
          `module_id` INT(10) UNSIGNED NOT NULL DEFAULT 0,
          `title` VARCHAR(255) NOT NULL DEFAULT '',
          `description` TEXT NULL,
          `sort_order` INT(10) UNSIGNED NOT NULL DEFAULT 0,
          `preview_image` VARCHAR(255) NOT NULL DEFAULT '',
          `source_video` VARCHAR(255) NOT NULL DEFAULT '',
          `duration_seconds` INT(10) UNSIGNED NOT NULL DEFAULT 0,
          `video_status` VARCHAR(32) NOT NULL DEFAULT 'none',
          `source_presentation` VARCHAR(255) NOT NULL DEFAULT '',
          `presentation_pdf` VARCHAR(255) NOT NULL DEFAULT '',
          `slides_dir` VARCHAR(255) NOT NULL DEFAULT '',
          `presentation_status` VARCHAR(32) NOT NULL DEFAULT 'none',
          `is_default` TINYINT(1) NOT NULL DEFAULT 0,
          `is_active` TINYINT(1) NOT NULL DEFAULT 1,
          `createdon` DATETIME NULL,
          `updatedon` DATETIME NULL,
          PRIMARY KEY (`id`),
          KEY `module_id` (`module_id`),
          KEY `module_sort` (`module_id`, `sort_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
    echo "Created table {$tables['lessons']}\n";
} else {
    echo "Table already exists: {$tables['lessons']}\n";
}

if (!columnExists($modx, $tables['videos'], 'lesson_id')) {
    execSql($modx, "ALTER TABLE `{$tables['videos']}` ADD COLUMN `lesson_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `module_id`");
    echo "Added lesson_id to {$tables['videos']}\n";
}

if (!columnExists($modx, $tables['slides'], 'lesson_id')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` ADD COLUMN `lesson_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `module_id`");
    echo "Added lesson_id to {$tables['slides']}\n";
}

if (indexExists($modx, $tables['videos'], 'module_quality')) {
    execSql($modx, "ALTER TABLE `{$tables['videos']}` DROP INDEX `module_quality`");
    echo "Dropped module_quality on {$tables['videos']}\n";
}
if (!indexExists($modx, $tables['videos'], 'lesson_id')) {
    execSql($modx, "ALTER TABLE `{$tables['videos']}` ADD KEY `lesson_id` (`lesson_id`)");
}
if (!indexExists($modx, $tables['videos'], 'lesson_quality')) {
    execSql($modx, "ALTER TABLE `{$tables['videos']}` ADD UNIQUE KEY `lesson_quality` (`lesson_id`, `quality`)");
}

if (indexExists($modx, $tables['slides'], 'module_slide')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` DROP INDEX `module_slide`");
    echo "Dropped module_slide on {$tables['slides']}\n";
}
if (indexExists($modx, $tables['slides'], 'module_timecode')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` DROP INDEX `module_timecode`");
    echo "Dropped module_timecode on {$tables['slides']}\n";
}
if (!indexExists($modx, $tables['slides'], 'lesson_id')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` ADD KEY `lesson_id` (`lesson_id`)");
}
if (!indexExists($modx, $tables['slides'], 'lesson_slide')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` ADD UNIQUE KEY `lesson_slide` (`lesson_id`, `slide_no`)");
}
if (!indexExists($modx, $tables['slides'], 'lesson_timecode')) {
    execSql($modx, "ALTER TABLE `{$tables['slides']}` ADD KEY `lesson_timecode` (`lesson_id`, `timecode_ms`)");
}

execSql($modx, "
    INSERT INTO `{$tables['lessons']}` (
      `module_id`, `title`, `description`, `sort_order`, `preview_image`, `source_video`, `duration_seconds`,
      `video_status`, `source_presentation`, `presentation_pdf`, `slides_dir`, `presentation_status`,
      `is_default`, `is_active`, `createdon`, `updatedon`
    )
    SELECT
      m.`id` AS `module_id`,
      CASE
        WHEN sc.`pagetitle` IS NOT NULL AND sc.`pagetitle` <> '' THEN sc.`pagetitle`
        ELSE CONCAT('Видео ', m.`id`)
      END AS `title`,
      NULL AS `description`,
      1 AS `sort_order`,
      '' AS `preview_image`,
      m.`source_video`,
      m.`duration_seconds`,
      m.`video_status`,
      m.`source_presentation`,
      m.`presentation_pdf`,
      m.`slides_dir`,
      m.`presentation_status`,
      1 AS `is_default`,
      m.`is_active`,
      m.`createdon`,
      m.`updatedon`
    FROM `{$tables['modules']}` m
    LEFT JOIN `{$tables['site_content']}` sc ON sc.`id` = m.`resource_id`
    WHERE NOT EXISTS (
      SELECT 1 FROM `{$tables['lessons']}` l WHERE l.`module_id` = m.`id`
    )
    AND (
      m.`source_video` <> ''
      OR m.`source_presentation` <> ''
      OR EXISTS (SELECT 1 FROM `{$tables['videos']}` v WHERE v.`module_id` = m.`id`)
      OR EXISTS (SELECT 1 FROM `{$tables['slides']}` s WHERE s.`module_id` = m.`id`)
    )
", true);

echo "Seeded default lessons where needed\n";

execSql($modx, "
    UPDATE `{$tables['videos']}` v
    JOIN (
      SELECT `module_id`, MIN(`id`) AS `lesson_id`
      FROM `{$tables['lessons']}`
      GROUP BY `module_id`
    ) l ON l.`module_id` = v.`module_id`
    SET v.`lesson_id` = l.`lesson_id`
    WHERE v.`lesson_id` = 0
", true);

echo "Bound existing videos to default lessons\n";

execSql($modx, "
    UPDATE `{$tables['slides']}` s
    JOIN (
      SELECT `module_id`, MIN(`id`) AS `lesson_id`
      FROM `{$tables['lessons']}`
      GROUP BY `module_id`
    ) l ON l.`module_id` = s.`module_id`
    SET s.`lesson_id` = l.`lesson_id`
    WHERE s.`lesson_id` = 0
", true);

echo "Bound existing slides to default lessons\n";
echo "Done\n";

CREATE TABLE IF NOT EXISTS `modx_partnerstraining_module_lessons` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `modx_partnerstraining_module_videos`
  ADD COLUMN `lesson_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `module_id`;

ALTER TABLE `modx_partnerstraining_module_slides`
  ADD COLUMN `lesson_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER `module_id`;

ALTER TABLE `modx_partnerstraining_module_videos`
  DROP INDEX `module_quality`,
  ADD KEY `lesson_id` (`lesson_id`),
  ADD UNIQUE KEY `lesson_quality` (`lesson_id`, `quality`);

ALTER TABLE `modx_partnerstraining_module_slides`
  DROP INDEX `module_slide`,
  DROP INDEX `module_timecode`,
  ADD KEY `lesson_id` (`lesson_id`),
  ADD UNIQUE KEY `lesson_slide` (`lesson_id`, `slide_no`),
  ADD KEY `lesson_timecode` (`lesson_id`, `timecode_ms`);

INSERT INTO `modx_partnerstraining_module_lessons` (
  `module_id`,
  `title`,
  `description`,
  `sort_order`,
  `preview_image`,
  `source_video`,
  `duration_seconds`,
  `video_status`,
  `source_presentation`,
  `presentation_pdf`,
  `slides_dir`,
  `presentation_status`,
  `is_default`,
  `is_active`,
  `createdon`,
  `updatedon`
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
FROM `modx_partnerstraining_modules` m
LEFT JOIN `modx_partnerssite_content` sc ON sc.`id` = m.`resource_id`
WHERE NOT EXISTS (
  SELECT 1
  FROM `modx_partnerstraining_module_lessons` l
  WHERE l.`module_id` = m.`id`
)
AND (
  m.`source_video` <> ''
  OR m.`source_presentation` <> ''
  OR EXISTS (
    SELECT 1
    FROM `modx_partnerstraining_module_videos` v
    WHERE v.`module_id` = m.`id`
  )
  OR EXISTS (
    SELECT 1
    FROM `modx_partnerstraining_module_slides` s
    WHERE s.`module_id` = m.`id`
  )
);

UPDATE `modx_partnerstraining_module_videos` v
JOIN (
  SELECT `module_id`, MIN(`id`) AS `lesson_id`
  FROM `modx_partnerstraining_module_lessons`
  GROUP BY `module_id`
) l ON l.`module_id` = v.`module_id`
SET v.`lesson_id` = l.`lesson_id`
WHERE v.`lesson_id` = 0;

UPDATE `modx_partnerstraining_module_slides` s
JOIN (
  SELECT `module_id`, MIN(`id`) AS `lesson_id`
  FROM `modx_partnerstraining_module_lessons`
  GROUP BY `module_id`
) l ON l.`module_id` = s.`module_id`
SET s.`lesson_id` = l.`lesson_id`
WHERE s.`lesson_id` = 0;

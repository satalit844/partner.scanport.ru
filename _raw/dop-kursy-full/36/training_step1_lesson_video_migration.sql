-- Шаг 1. Добавляем отдельную сущность "видео урока"
-- Логика после миграции:
-- курс -> модуль -> урок -> видео урока -> качества/слайды
-- тесты и практики остаются на модуле

CREATE TABLE IF NOT EXISTS `modx_partnerstraining_lesson_videos` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `lesson_id` int UNSIGNED NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `source_video` varchar(255) NOT NULL DEFAULT '',
  `duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `video_status` varchar(32) NOT NULL DEFAULT 'none',
  `source_presentation` varchar(255) NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) NOT NULL DEFAULT '',
  `slides_dir` varchar(255) NOT NULL DEFAULT '',
  `presentation_status` varchar(32) NOT NULL DEFAULT 'none',
  `preview_image` varchar(255) NOT NULL DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_sort` (`lesson_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `modx_partnerstraining_module_videos`
  ADD COLUMN `lesson_video_id` int UNSIGNED NOT NULL DEFAULT '0' AFTER `lesson_id`;

ALTER TABLE `modx_partnerstraining_module_slides`
  ADD COLUMN `lesson_video_id` int UNSIGNED NOT NULL DEFAULT '0' AFTER `lesson_id`;

ALTER TABLE `modx_partnerstraining_module_videos`
  ADD KEY `lesson_video_id` (`lesson_video_id`);

ALTER TABLE `modx_partnerstraining_module_slides`
  ADD KEY `lesson_video_id` (`lesson_video_id`);

-- Переносим текущее содержимое уроков в первый видеоблок урока.
INSERT INTO `modx_partnerstraining_lesson_videos`
(
  `lesson_id`,
  `title`,
  `description`,
  `sort_order`,
  `source_video`,
  `duration_seconds`,
  `video_status`,
  `source_presentation`,
  `presentation_pdf`,
  `slides_dir`,
  `presentation_status`,
  `preview_image`,
  `is_default`,
  `is_active`,
  `createdon`,
  `updatedon`
)
SELECT
  l.`id` AS `lesson_id`,
  CASE
    WHEN TRIM(COALESCE(l.`title`, '')) = '' THEN CONCAT('Видео урока #', l.`id`)
    ELSE l.`title`
  END AS `title`,
  l.`description`,
  1 AS `sort_order`,
  l.`source_video`,
  l.`duration_seconds`,
  l.`video_status`,
  l.`source_presentation`,
  l.`presentation_pdf`,
  l.`slides_dir`,
  l.`presentation_status`,
  l.`preview_image`,
  1 AS `is_default`,
  l.`is_active`,
  l.`createdon`,
  l.`updatedon`
FROM `modx_partnerstraining_module_lessons` l
LEFT JOIN `modx_partnerstraining_lesson_videos` lv ON lv.`lesson_id` = l.`id`
WHERE lv.`id` IS NULL;

UPDATE `modx_partnerstraining_module_videos` mv
JOIN `modx_partnerstraining_lesson_videos` lv ON lv.`lesson_id` = mv.`lesson_id` AND lv.`sort_order` = 1
SET mv.`lesson_video_id` = lv.`id`
WHERE mv.`lesson_id` > 0 AND mv.`lesson_video_id` = 0;

UPDATE `modx_partnerstraining_module_slides` ms
JOIN `modx_partnerstraining_lesson_videos` lv ON lv.`lesson_id` = ms.`lesson_id` AND lv.`sort_order` = 1
SET ms.`lesson_video_id` = lv.`id`
WHERE ms.`lesson_id` > 0 AND ms.`lesson_video_id` = 0;

-- На первом шаге legacy-поля у уроков НЕ удаляем.
-- Это нужно, чтобы не поломать уже существующий фронт и прогресс до полной перепривязки процессоров.

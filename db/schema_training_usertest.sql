-- Training/UserTest tables schema
-- Generated: 2026-06-09T17:42:47+03:00



-- ----------------------------
-- Table structure for modx_partnerstraining_certificate_templates
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_certificate_templates`;
CREATE TABLE `modx_partnerstraining_certificate_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `template_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `template_preview` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `output_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `page_no` int unsigned NOT NULL DEFAULT '1',
  `fullname_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `fullname_font_size` decimal(10,2) NOT NULL DEFAULT '28.00',
  `fullname_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `fullname_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `course_title_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `course_title_font_size` decimal(10,2) NOT NULL DEFAULT '24.00',
  `course_title_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `course_title_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `completed_date_x` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_y` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_max_width` decimal(10,2) NOT NULL DEFAULT '0.00',
  `completed_date_font_size` decimal(10,2) NOT NULL DEFAULT '20.00',
  `completed_date_color` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '#7B4F92',
  `completed_date_align` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'left',
  `date_format` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'd.m.Y',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_id` (`course_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_course_access
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_course_access`;
CREATE TABLE `modx_partnerstraining_course_access` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `principal_type` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `principal_id` int unsigned NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `usergroup_id` int unsigned NOT NULL DEFAULT '0',
  `assigned_by` int unsigned NOT NULL DEFAULT '0',
  `active_from` datetime DEFAULT NULL,
  `active_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_principal` (`course_id`,`principal_type`,`principal_id`),
  KEY `course_id` (`course_id`),
  KEY `user_id` (`user_id`),
  KEY `usergroup_id` (`usergroup_id`),
  KEY `principal` (`principal_type`,`principal_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_courses
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_courses`;
CREATE TABLE `modx_partnerstraining_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_sequential` tinyint(1) NOT NULL DEFAULT '1',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource_id` (`resource_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_lesson_videos
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_lesson_videos`;
CREATE TABLE `modx_partnerstraining_lesson_videos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `preview_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_sort` (`lesson_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=529 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_module_lessons
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_module_lessons`;
CREATE TABLE `modx_partnerstraining_module_lessons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `source_video` varchar(255) NOT NULL DEFAULT '',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
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
  KEY `module_id` (`module_id`),
  KEY `module_sort` (`module_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_module_slides
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_module_slides`;
CREATE TABLE `modx_partnerstraining_module_slides` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `slide_no` int unsigned NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `timecode_ms` bigint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_slide` (`lesson_video_id`,`slide_no`),
  KEY `module_id` (`module_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_timecode` (`lesson_id`,`timecode_ms`),
  KEY `lesson_video_id` (`lesson_video_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_module_videos
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_module_videos`;
CREATE TABLE `modx_partnerstraining_module_videos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `quality` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `mime` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'video/mp4',
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `width` int unsigned NOT NULL DEFAULT '0',
  `height` int unsigned NOT NULL DEFAULT '0',
  `bitrate` int unsigned NOT NULL DEFAULT '0',
  `filesize` bigint unsigned NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_quality` (`lesson_video_id`,`quality`),
  KEY `module_id` (`module_id`),
  KEY `lesson_id` (`lesson_id`),
  KEY `lesson_video_id` (`lesson_video_id`)
) ENGINE=InnoDB AUTO_INCREMENT=1503 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_modules
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_modules`;
CREATE TABLE `modx_partnerstraining_modules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `resource_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `resource_id` (`resource_id`),
  KEY `course_id` (`course_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_practice_attempts
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_practice_attempts`;
CREATE TABLE `modx_partnerstraining_practice_attempts` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `test_link_id` int unsigned NOT NULL DEFAULT '0',
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `attempt_num` int unsigned NOT NULL DEFAULT '1',
  `attempt_no` int unsigned NOT NULL DEFAULT '1',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `deadline_at` datetime DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `max_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `review_comment` text COLLATE utf8mb4_general_ci,
  `reviewer_user_id` int unsigned NOT NULL DEFAULT '0',
  `submittedon` datetime DEFAULT NULL,
  `reviewedon` datetime DEFAULT NULL,
  `reviewedby` int unsigned NOT NULL DEFAULT '0',
  `is_latest` tinyint unsigned NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `test_link_id` (`test_link_id`),
  KEY `course_module_user` (`course_id`,`module_id`,`user_id`),
  KEY `status` (`status`),
  KEY `practice_user` (`practice_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_practice_files
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_practice_files`;
CREATE TABLE `modx_partnerstraining_practice_files` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL DEFAULT '0',
  `message_id` int unsigned NOT NULL DEFAULT '0',
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `original_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `mime` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `extension` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `size` int unsigned NOT NULL DEFAULT '0',
  `hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `message_id` (`message_id`),
  KEY `practice_id` (`practice_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_practice_messages
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_practice_messages`;
CREATE TABLE `modx_partnerstraining_practice_messages` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `attempt_id` int unsigned NOT NULL DEFAULT '0',
  `practice_id` int unsigned NOT NULL DEFAULT '0',
  `author_id` int unsigned NOT NULL DEFAULT '0',
  `author_type` varchar(50) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `sender_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `message` mediumtext COLLATE utf8mb4_general_ci,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attempt_id` (`attempt_id`),
  KEY `createdon` (`createdon`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_practices
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_practices`;
CREATE TABLE `modx_partnerstraining_practices` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `description` mediumtext COLLATE utf8mb4_unicode_ci,
  `template_file` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `template_file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `image` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `deadline_at` datetime DEFAULT NULL,
  `deadline_days` int unsigned NOT NULL DEFAULT '0',
  `allowed_extensions` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
  `max_file_size` int unsigned NOT NULL DEFAULT '52428800',
  `active` tinyint unsigned NOT NULL DEFAULT '1',
  `rank` int unsigned NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  `editedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `course_id` (`course_id`),
  KEY `module_id` (`module_id`),
  KEY `active` (`active`),
  KEY `rank` (`rank`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_test_links
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_test_links`;
CREATE TABLE `modx_partnerstraining_test_links` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `usertest_test_id` int unsigned NOT NULL DEFAULT '0',
  `link_type` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'module',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `max_attempts` int unsigned NOT NULL DEFAULT '0',
  `min_pass_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `block_next_module_until_passed` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_link` (`course_id`,`module_id`,`usertest_test_id`,`link_type`),
  KEY `course_id` (`course_id`),
  KEY `module_id` (`module_id`),
  KEY `usertest_test_id` (`usertest_test_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_certificates
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_certificates`;
CREATE TABLE `modx_partnerstraining_user_certificates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `template_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `user_course_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'issued',
  `fullname` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `course_title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `completedon` datetime DEFAULT NULL,
  `issuedon` datetime DEFAULT NULL,
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `preview_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user` (`course_id`,`user_id`),
  KEY `template_id` (`template_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `issuedon` (`issuedon`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_courses
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_courses`;
CREATE TABLE `modx_partnerstraining_user_courses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'assigned',
  `current_module_id` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed_modules` int unsigned NOT NULL DEFAULT '0',
  `total_modules` int unsigned NOT NULL DEFAULT '0',
  `startedon` datetime DEFAULT NULL,
  `completedon` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `course_user` (`course_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_lesson_progress
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_lesson_progress`;
CREATE TABLE `modx_partnerstraining_user_lesson_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_user` (`lesson_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_lesson_video_progress
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_lesson_video_progress`;
CREATE TABLE `modx_partnerstraining_user_lesson_video_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_id` int unsigned NOT NULL DEFAULT '0',
  `lesson_video_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `lesson_video_user` (`lesson_video_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`),
  KEY `module_user` (`module_id`,`user_id`),
  KEY `lesson_user` (`lesson_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=683 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_manager_link
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_manager_link`;
CREATE TABLE `modx_partnerstraining_user_manager_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `manager_user_id` int unsigned NOT NULL DEFAULT '0',
  `employee_user_id` int unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `createdby` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `manager_employee` (`manager_user_id`,`employee_user_id`),
  KEY `manager_user_id` (`manager_user_id`),
  KEY `employee_user_id` (`employee_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_module_progress
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_module_progress`;
CREATE TABLE `modx_partnerstraining_user_module_progress` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int unsigned NOT NULL DEFAULT '0',
  `max_time` int unsigned NOT NULL DEFAULT '0',
  `watched_seconds` int unsigned NOT NULL DEFAULT '0',
  `duration_seconds` int unsigned NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `module_user` (`module_id`,`user_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_user_test_status
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_user_test_status`;
CREATE TABLE `modx_partnerstraining_user_test_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `course_id` int unsigned NOT NULL DEFAULT '0',
  `module_id` int unsigned NOT NULL DEFAULT '0',
  `usertest_test_id` int unsigned NOT NULL DEFAULT '0',
  `user_id` int unsigned NOT NULL DEFAULT '0',
  `last_result_id` int unsigned NOT NULL DEFAULT '0',
  `attempts` int unsigned NOT NULL DEFAULT '0',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `last_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `last_passedon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `test_user` (`usertest_test_id`,`user_id`,`module_id`),
  KEY `course_user` (`course_id`,`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8585 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnerstraining_usertest_bad_results_backup
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnerstraining_usertest_bad_results_backup`;
CREATE TABLE `modx_partnerstraining_usertest_bad_results_backup` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `test_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  `test_time` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `session` text COLLATE utf8mb4_general_ci,
  `invite_id` int NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `status_id` (`status_id`),
  KEY `invite_id` (`invite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=173 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_answers
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_answers`;
CREATE TABLE `modx_partnersusertest_answers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `question_id` int DEFAULT NULL,
  `answer` text COLLATE utf8mb4_general_ci,
  `type_file` int DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `point` double DEFAULT NULL,
  `right` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `menuindex` (`menuindex`),
  KEY `right` (`right`)
) ENGINE=MyISAM AUTO_INCREMENT=2507 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_categorys
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_categorys`;
CREATE TABLE `modx_partnersusertest_categorys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_groups
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_groups`;
CREATE TABLE `modx_partnersusertest_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `parent` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_groups_link
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_groups_link`;
CREATE TABLE `modx_partnersusertest_groups_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `group_id` int DEFAULT NULL,
  `test_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `group_id` (`group_id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_questions
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_questions`;
CREATE TABLE `modx_partnersusertest_questions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `test_id` int DEFAULT NULL,
  `parent` int NOT NULL DEFAULT '0',
  `category_id` int DEFAULT NULL,
  `question` text COLLATE utf8mb4_general_ci,
  `type` int DEFAULT NULL,
  `type_file` int DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_general_ci DEFAULT '',
  `extended` text COLLATE utf8mb4_general_ci,
  `max_point` double DEFAULT '0',
  `random_answer` tinyint(1) DEFAULT '0',
  `validate` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`),
  KEY `parent` (`parent`)
) ENGINE=MyISAM AUTO_INCREMENT=1133 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_result_categorys
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_result_categorys`;
CREATE TABLE `modx_partnersusertest_result_categorys` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `result_id` int DEFAULT NULL,
  `category_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `cat_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`),
  KEY `category_id` (`category_id`),
  KEY `variant_id` (`variant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_result_invites
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_result_invites`;
CREATE TABLE `modx_partnersusertest_result_invites` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_pass` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `active` tinyint(1) DEFAULT '1',
  `test_page_id` int DEFAULT NULL,
  `auth_page_id` int DEFAULT NULL,
  `user_auth_code` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `url_scheme` varchar(5) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0',
  `url` varchar(400) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `result_id` int NOT NULL DEFAULT '0',
  `date_expired` datetime DEFAULT NULL,
  `send_email_if_empty_test` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_email` (`user_email`),
  KEY `user_auth_code` (`user_auth_code`),
  KEY `active` (`active`),
  KEY `send_email_if_empty_test` (`send_email_if_empty_test`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_result_status
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_result_status`;
CREATE TABLE `modx_partnersusertest_result_status` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_resultanswers
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_resultanswers`;
CREATE TABLE `modx_partnersusertest_resultanswers` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `result_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  `answer_id` int NOT NULL DEFAULT '0',
  `answer_ids` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `answer` text COLLATE utf8mb4_general_ci,
  `point` double DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `time` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `result_id` (`result_id`),
  KEY `question_id` (`question_id`)
) ENGINE=MyISAM AUTO_INCREMENT=462 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_results
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_results`;
CREATE TABLE `modx_partnersusertest_results` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `user_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `user_email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `date` datetime DEFAULT NULL,
  `test_point` double DEFAULT NULL,
  `max_point` double DEFAULT '0',
  `test_time` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `status_id` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_general_ci,
  `session` text COLLATE utf8mb4_general_ci,
  `invite_id` int NOT NULL DEFAULT '0',
  `properties` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `test_id` (`test_id`),
  KEY `user_id` (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `status_id` (`status_id`),
  KEY `invite_id` (`invite_id`)
) ENGINE=MyISAM AUTO_INCREMENT=242 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_test_question_link
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_test_question_link`;
CREATE TABLE `modx_partnersusertest_test_question_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `menuindex` int NOT NULL DEFAULT '0',
  `test_id` int DEFAULT NULL,
  `question_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `question_id` (`question_id`),
  KEY `test_id` (`test_id`),
  KEY `menuindex` (`menuindex`)
) ENGINE=MyISAM AUTO_INCREMENT=3122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_test_variant_link
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_test_variant_link`;
CREATE TABLE `modx_partnersusertest_test_variant_link` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `variant_id` int DEFAULT NULL,
  `use_custom_point` tinyint(1) DEFAULT '0',
  `start_point` double DEFAULT NULL,
  `end_point` double DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variant_id` (`variant_id`),
  KEY `test_id` (`test_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_tests
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_tests`;
CREATE TABLE `modx_partnersusertest_tests` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `count_questions` int DEFAULT NULL,
  `count_questions_on_page` int DEFAULT NULL,
  `count_test_answer` int DEFAULT NULL,
  `time_test` int DEFAULT NULL,
  `type` int DEFAULT NULL,
  `use_category` tinyint(1) DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `customer` text COLLATE utf8mb4_general_ci,
  `appeal` text COLLATE utf8mb4_general_ci,
  `instruction` text COLLATE utf8mb4_general_ci,
  `use_block_q_number` tinyint(1) DEFAULT '1',
  `pub_date` int NOT NULL DEFAULT '0',
  `unpub_date` int NOT NULL DEFAULT '0',
  `variant_set_id` int DEFAULT NULL,
  `test_type` int NOT NULL DEFAULT '1',
  `ask_user_data` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `name` (`name`(250)),
  KEY `active` (`active`),
  KEY `type` (`type`),
  KEY `variant_set_id` (`variant_set_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_variant_sets
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_variant_sets`;
CREATE TABLE `modx_partnersusertest_variant_sets` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- ----------------------------
-- Table structure for modx_partnersusertest_variants
-- ----------------------------
DROP TABLE IF EXISTS `modx_partnersusertest_variants`;
CREATE TABLE `modx_partnersusertest_variants` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `test_id` int DEFAULT NULL,
  `variant_set_id` int DEFAULT NULL,
  `start_point` double DEFAULT NULL,
  `end_point` double DEFAULT NULL,
  `passed` tinyint(1) DEFAULT '0',
  `result` text COLLATE utf8mb4_general_ci,
  `category_id` int NOT NULL DEFAULT '0',
  `haker` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `variant_set_id` (`variant_set_id`),
  KEY `test_id` (`test_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

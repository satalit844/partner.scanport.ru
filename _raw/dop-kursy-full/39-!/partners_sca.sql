-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Апр 06 2026 г., 15:17
-- Версия сервера: 8.0.45-0ubuntu0.24.04.1
-- Версия PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `partners_sca`
--

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_courses`
--

CREATE TABLE `modx_partnerstraining_courses` (
  `id` int UNSIGNED NOT NULL,
  `resource_id` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_sequential` tinyint(1) NOT NULL DEFAULT '1',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_courses`
--

INSERT INTO `modx_partnerstraining_courses` (`id`, `resource_id`, `is_active`, `is_sequential`, `source_presentation`, `presentation_pdf`, `slides_dir`, `presentation_status`, `createdon`, `updatedon`) VALUES
(1, 159, 1, 1, '/assets/training/courses/1/presentation/course_1_presentation_source.pptx', '/assets/training/courses/1/presentation/course_1_presentation.pdf', '/assets/training/courses/1/presentation/slides/', 'ready', '2026-03-23 15:06:02', '2026-04-06 11:28:32'),
(2, 162, 1, 1, '/assets/training/courses/2/presentation/source.pptx', '/assets/training/courses/2/presentation/slides.pdf', '/assets/training/courses/2/presentation/slides/', 'ready', '2026-03-23 15:06:02', '2026-04-06 11:28:32');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_course_access`
--

CREATE TABLE `modx_partnerstraining_course_access` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `principal_type` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'user',
  `principal_id` int UNSIGNED NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `usergroup_id` int UNSIGNED NOT NULL DEFAULT '0',
  `assigned_by` int UNSIGNED NOT NULL DEFAULT '0',
  `active_from` datetime DEFAULT NULL,
  `active_to` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_course_access`
--

INSERT INTO `modx_partnerstraining_course_access` (`id`, `course_id`, `principal_type`, `principal_id`, `access_role`, `user_id`, `usergroup_id`, `assigned_by`, `active_from`, `active_to`, `is_active`, `createdon`) VALUES
(6, 1, 'user', 2, 'director', 0, 0, 2, '2026-03-24 14:40:00', '2026-04-03 14:40:00', 1, '2026-03-25 14:40:50'),
(7, 2, 'user', 2, 'director', 0, 0, 2, '2026-03-22 15:23:11', '2026-03-28 15:23:13', 1, '2026-03-25 15:23:13'),
(8, 1, 'user', 3, 'director', 0, 0, 3, '2026-03-30 10:05:00', '2026-04-30 10:05:00', 1, '2026-03-30 10:05:48');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_lesson_videos`
--

CREATE TABLE `modx_partnerstraining_lesson_videos` (
  `id` int UNSIGNED NOT NULL,
  `lesson_id` int UNSIGNED NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8mb4_general_ci,
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `preview_image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_lesson_videos`
--

INSERT INTO `modx_partnerstraining_lesson_videos` (`id`, `lesson_id`, `title`, `description`, `sort_order`, `source_video`, `duration_seconds`, `video_status`, `source_presentation`, `presentation_pdf`, `slides_dir`, `presentation_status`, `preview_image`, `is_default`, `is_active`, `createdon`, `updatedon`) VALUES
(1, 2, 'Модуль 1. Урок 1. Базовые термины', '', 1, '/assets/training/courses/1/modules/2/lessons/2/source/course_1_module_2_lesson_2_source.mkv', 11, 'ready', '', '', '', 'available', '', 1, 1, '2026-03-23 15:06:02', '2026-04-02 11:58:08'),
(2, 3, 'Модуль 2. Оборудование', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(3, 4, 'Модуль 3. DataMobile. Общие сведения', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(4, 5, 'Модуль 4. DataMobile. Установка и первоначальная настройка', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(8, 19, 'Модуль 1. Урок 2. Участники рынка AutoID', '', 1, '', 0, 'none', '', '', '', 'none', '', 1, 1, '2026-04-02 11:56:31', '2026-04-02 11:56:31'),
(19, 15, '1 урок', '', 1, '/assets/training/courses/1/modules/1/lessons/15/videos/19/source/course_1_module_1_lesson_15_video_19_source.mkv', 17, 'available', '', '', '', 'none', '', 1, 1, '2026-04-06 12:12:10', '2026-04-06 13:57:31'),
(22, 15, '2 урок', '', 2, '/assets/training/courses/1/modules/1/lessons/15/videos/22/source/course_1_module_1_lesson_15_video_22_source.mkv', 23, 'ready', '', '', '', 'none', '', 0, 1, '2026-04-06 13:02:50', '2026-04-06 13:04:12'),
(23, 15, '3 урок', '', 3, '/assets/training/courses/1/modules/1/lessons/15/videos/23/source/course_1_module_1_lesson_15_video_23_source.mkv', 33, 'ready', '', '', '', 'none', '', 0, 1, '2026-04-06 13:17:09', '2026-04-06 13:42:40'),
(26, 15, '4 урок', '', 4, '/assets/training/courses/1/modules/1/lessons/15/videos/26/source/course_1_module_1_lesson_15_video_26_source.mkv', 49, 'ready', '', '', '', 'none', '', 0, 1, '2026-04-06 14:05:02', '2026-04-06 14:05:51'),
(27, 15, '5 урок', '', 5, '/assets/training/courses/1/modules/1/lessons/15/videos/27/source/course_1_module_1_lesson_15_video_27_source.mkv', 33, 'ready', '', '', '', 'none', '', 0, 1, '2026-04-06 15:13:02', '2026-04-06 15:13:38');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_modules`
--

CREATE TABLE `modx_partnerstraining_modules` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `resource_id` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `video_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `presentation_status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'none',
  `source_video` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `source_presentation` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `presentation_pdf` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `slides_dir` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_modules`
--

INSERT INTO `modx_partnerstraining_modules` (`id`, `course_id`, `resource_id`, `is_active`, `is_required`, `duration_seconds`, `video_status`, `presentation_status`, `source_video`, `source_presentation`, `presentation_pdf`, `slides_dir`, `createdon`, `updatedon`) VALUES
(1, 1, 163, 1, 1, 17, 'ready', 'ready', '/assets/training/courses/1/lessons/1/source/source.mkv', '/assets/training/courses/1/modules/1/presentation/course_1_module_1_presentation_source.pptx', '/assets/training/courses/1/modules/1/presentation/course_1_module_1_presentation.pdf', '/assets/training/courses/1/modules/1/presentation/slides/', '2026-03-23 15:06:02', '2026-04-06 11:28:32'),
(2, 1, 164, 1, 1, 0, 'none', 'ready', '', '/var/www/partners_sca_usr/data/www/partners.scanport.ru/assets/Модуль 1. Урок 1. Базовые термины/', '', '', '2026-03-23 15:06:02', '2026-04-06 11:28:32'),
(3, 1, 165, 1, 1, 0, 'none', 'ready', '', '', '', '', '2026-03-23 15:06:02', '2026-04-06 11:28:32'),
(4, 1, 166, 1, 1, 0, 'none', 'ready', '', '', '', '', '2026-03-23 15:06:02', '2026-04-06 11:28:32'),
(5, 1, 167, 1, 1, 0, 'none', 'ready', '', '', '', '', '2026-03-23 15:06:02', '2026-04-06 11:28:32');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_module_lessons`
--

CREATE TABLE `modx_partnerstraining_module_lessons` (
  `id` int UNSIGNED NOT NULL,
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
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
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_module_lessons`
--

INSERT INTO `modx_partnerstraining_module_lessons` (`id`, `module_id`, `title`, `description`, `sort_order`, `source_video`, `duration_seconds`, `video_status`, `source_presentation`, `presentation_pdf`, `slides_dir`, `presentation_status`, `preview_image`, `is_default`, `is_active`, `createdon`, `updatedon`) VALUES
(2, 2, 'Модуль 1. Урок 1. Базовые термины', '', 1, '/assets/training/courses/1/modules/2/lessons/2/source/course_1_module_2_lesson_2_source.mkv', 11, 'ready', '', '', '', 'available', '', 1, 1, '2026-03-23 15:06:02', '2026-04-02 11:58:08'),
(3, 3, 'Модуль 2. Оборудование', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(4, 4, 'Модуль 3. DataMobile. Общие сведения', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(5, 5, 'Модуль 4. DataMobile. Установка и первоначальная настройка', NULL, 1, '', 0, 'none', '', '', '', 'ready', '', 1, 1, '2026-03-23 15:06:02', '2026-03-24 13:03:21'),
(15, 1, 'Модуль 0. Урок 1. Приветствие. О чем курс', '', 1, '/assets/training/courses/1/modules/1/lessons/15/source/course_1_module_1_lesson_15_source.mkv', 155, 'ready', '/assets/training/courses/1/modules/1/lessons/15/presentation/course_1_module_1_lesson_15_presentation.pptx', '/assets/training/courses/1/modules/1/lessons/15/presentation/course_1_module_1_lesson_15_presentation.pdf', '/assets/training/courses/1/modules/1/lessons/15/presentation/slides/', 'available', '', 1, 1, '2026-03-31 16:45:26', '2026-04-06 15:15:21'),
(19, 2, 'Модуль 1. Урок 2. Участники рынка AutoID', '', 2, '', 0, 'none', '', '', '', 'none', '', 0, 1, '2026-04-02 11:56:31', '2026-04-02 11:56:31');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_module_slides`
--

CREATE TABLE `modx_partnerstraining_module_slides` (
  `id` int UNSIGNED NOT NULL,
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `lesson_id` int UNSIGNED NOT NULL DEFAULT '0',
  `lesson_video_id` int UNSIGNED NOT NULL DEFAULT '0',
  `slide_no` int UNSIGNED NOT NULL DEFAULT '0',
  `image` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `timecode_ms` bigint UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_module_slides`
--

INSERT INTO `modx_partnerstraining_module_slides` (`id`, `module_id`, `lesson_id`, `lesson_video_id`, `slide_no`, `image`, `timecode_ms`, `is_active`) VALUES
(30, 3, 3, 2, 3, '/assets/training/courses/1/presentation/slides/003.jpg', 0, 1),
(31, 4, 4, 3, 4, '/assets/training/courses/1/presentation/slides/004.jpg', 0, 1),
(32, 5, 5, 4, 5, '/assets/training/courses/1/presentation/slides/005.jpg', 0, 1),
(322, 2, 2, 1, 1, '/assets/training/courses/1/presentation/slides/course_1_slide_001.jpg', 0, 1),
(323, 2, 2, 1, 2, '/assets/training/courses/1/presentation/slides/course_1_slide_002.jpg', 0, 1),
(324, 2, 2, 1, 3, '/assets/training/courses/1/presentation/slides/course_1_slide_003.jpg', 0, 1),
(325, 2, 2, 1, 4, '/assets/training/courses/1/presentation/slides/course_1_slide_004.jpg', 0, 1),
(326, 2, 2, 1, 5, '/assets/training/courses/1/presentation/slides/course_1_slide_005.jpg', 0, 1),
(327, 2, 2, 1, 6, '/assets/training/courses/1/presentation/slides/course_1_slide_006.jpg', 0, 1),
(328, 2, 2, 1, 7, '/assets/training/courses/1/presentation/slides/course_1_slide_007.jpg', 0, 1),
(329, 2, 2, 1, 8, '/assets/training/courses/1/presentation/slides/course_1_slide_008.jpg', 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_module_videos`
--

CREATE TABLE `modx_partnerstraining_module_videos` (
  `id` int UNSIGNED NOT NULL,
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `lesson_id` int UNSIGNED NOT NULL DEFAULT '0',
  `lesson_video_id` int UNSIGNED NOT NULL DEFAULT '0',
  `quality` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `mime` varchar(64) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'video/mp4',
  `file_path` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `width` int UNSIGNED NOT NULL DEFAULT '0',
  `height` int UNSIGNED NOT NULL DEFAULT '0',
  `bitrate` int UNSIGNED NOT NULL DEFAULT '0',
  `filesize` bigint UNSIGNED NOT NULL DEFAULT '0',
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_module_videos`
--

INSERT INTO `modx_partnerstraining_module_videos` (`id`, `module_id`, `lesson_id`, `lesson_video_id`, `quality`, `mime`, `file_path`, `width`, `height`, `bitrate`, `filesize`, `is_default`, `is_active`) VALUES
(59, 2, 2, 1, '720p', 'video/mp4', '/assets/training/courses/1/modules/2/lessons/2/video/720p/course_1_module_2_lesson_2_720p.mp4', 1280, 720, 1872, 2755589, 1, 1),
(60, 2, 2, 1, '480p', 'video/mp4', '/assets/training/courses/1/modules/2/lessons/2/video/480p/course_1_module_2_lesson_2_480p.mp4', 854, 480, 525, 866094, 0, 1),
(61, 2, 2, 1, '320p', 'video/mp4', '/assets/training/courses/1/modules/2/lessons/2/video/320p/course_1_module_2_lesson_2_320p.mp4', 568, 320, 208, 431490, 0, 1),
(73, 1, 15, 19, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/19/qualities/720p/course_1_module_1_lesson_15_video_19_720p.mp4', 1280, 720, 1989, 4646419, 1, 1),
(74, 1, 15, 19, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/19/qualities/480p/course_1_module_1_lesson_15_video_19_480p.mp4', 854, 480, 574, 1487399, 0, 1),
(75, 1, 15, 19, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/19/qualities/320p/course_1_module_1_lesson_15_video_19_320p.mp4', 568, 320, 232, 741486, 0, 1),
(82, 1, 15, 22, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/22/qualities/720p/course_1_module_1_lesson_15_video_22_720p.mp4', 1280, 720, 1780, 5590203, 1, 1),
(83, 1, 15, 22, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/22/qualities/480p/course_1_module_1_lesson_15_video_22_480p.mp4', 854, 480, 486, 1727006, 0, 1),
(84, 1, 15, 22, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/22/qualities/320p/course_1_module_1_lesson_15_video_22_320p.mp4', 568, 320, 187, 856049, 0, 1),
(85, 1, 15, 23, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/23/qualities/720p/course_1_module_1_lesson_15_video_23_720p.mp4', 1280, 720, 1930, 8646745, 1, 1),
(86, 1, 15, 23, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/23/qualities/480p/course_1_module_1_lesson_15_video_23_480p.mp4', 854, 480, 581, 2874293, 0, 1),
(87, 1, 15, 23, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/23/qualities/320p/course_1_module_1_lesson_15_video_23_320p.mp4', 568, 320, 237, 1438996, 0, 1),
(88, 1, 15, 24, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/24/qualities/720p/course_1_module_1_lesson_15_video_24_720p.mp4', 1280, 720, 1705, 18579471, 1, 1),
(89, 1, 15, 24, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/24/qualities/480p/course_1_module_1_lesson_15_video_24_480p.mp4', 854, 480, 466, 5774100, 0, 1),
(90, 1, 15, 24, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/24/qualities/320p/course_1_module_1_lesson_15_video_24_320p.mp4', 568, 320, 184, 2933216, 0, 1),
(91, 1, 15, 26, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/26/qualities/720p/course_1_module_1_lesson_15_video_26_720p.mp4', 1280, 720, 1719, 11302185, 1, 1),
(92, 1, 15, 26, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/26/qualities/480p/course_1_module_1_lesson_15_video_26_480p.mp4', 854, 480, 502, 3705739, 0, 1),
(93, 1, 15, 26, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/26/qualities/320p/course_1_module_1_lesson_15_video_26_320p.mp4', 568, 320, 201, 1874822, 0, 1),
(94, 1, 15, 27, '720p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/27/qualities/720p/course_1_module_1_lesson_15_video_27_720p.mp4', 1280, 720, 1930, 8646745, 1, 1),
(95, 1, 15, 27, '480p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/27/qualities/480p/course_1_module_1_lesson_15_video_27_480p.mp4', 854, 480, 581, 2874293, 0, 1),
(96, 1, 15, 27, '320p', 'video/mp4', '/assets/training/courses/1/modules/1/lessons/15/videos/27/qualities/320p/course_1_module_1_lesson_15_video_27_320p.mp4', 568, 320, 237, 1438996, 0, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_practice_attempts`
--

CREATE TABLE `modx_partnerstraining_practice_attempts` (
  `id` int UNSIGNED NOT NULL,
  `test_link_id` int UNSIGNED NOT NULL DEFAULT '0',
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `attempt_no` int UNSIGNED NOT NULL DEFAULT '1',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `title` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `max_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `review_comment` text COLLATE utf8mb4_general_ci,
  `reviewer_user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `submittedon` datetime DEFAULT NULL,
  `reviewedon` datetime DEFAULT NULL,
  `createdon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_practice_messages`
--

CREATE TABLE `modx_partnerstraining_practice_messages` (
  `id` int UNSIGNED NOT NULL,
  `attempt_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `sender_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `message` mediumtext COLLATE utf8mb4_general_ci,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_test_links`
--

CREATE TABLE `modx_partnerstraining_test_links` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `usertest_test_id` int UNSIGNED NOT NULL DEFAULT '0',
  `link_type` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'module',
  `sort_order` int UNSIGNED NOT NULL DEFAULT '0',
  `is_required` tinyint(1) NOT NULL DEFAULT '1',
  `max_attempts` int UNSIGNED NOT NULL DEFAULT '0',
  `min_pass_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `block_next_module_until_passed` tinyint(1) NOT NULL DEFAULT '0',
  `createdon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_test_links`
--

INSERT INTO `modx_partnerstraining_test_links` (`id`, `course_id`, `module_id`, `usertest_test_id`, `link_type`, `sort_order`, `is_required`, `max_attempts`, `min_pass_percent`, `block_next_module_until_passed`, `createdon`) VALUES
(1, 1, 1, 1, 'test', 1, 1, 10, 80.00, 1, '2026-04-01 12:57:40'),
(2, 1, 1, 2, 'practice', 1, 1, 5, 90.00, 1, '2026-04-01 12:58:05');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_user_courses`
--

CREATE TABLE `modx_partnerstraining_user_courses` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `access_role` varchar(16) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'employee',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'assigned',
  `current_module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed_modules` int UNSIGNED NOT NULL DEFAULT '0',
  `total_modules` int UNSIGNED NOT NULL DEFAULT '0',
  `startedon` datetime DEFAULT NULL,
  `completedon` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_user_courses`
--

INSERT INTO `modx_partnerstraining_user_courses` (`id`, `course_id`, `user_id`, `access_role`, `status`, `current_module_id`, `progress_percent`, `completed_modules`, `total_modules`, `startedon`, `completedon`, `last_activity`) VALUES
(1, 1, 2, 'director', 'in_progress', 1, 0.00, 0, 5, '2026-03-31 19:58:17', NULL, '2026-04-06 10:59:19'),
(2, 2, 2, 'director', 'revoked', 0, 0.00, 0, 0, NULL, NULL, '2026-04-01 12:32:14'),
(3, 1, 3, 'director', 'in_progress', 1, 0.00, 0, 5, '2026-04-01 11:34:51', NULL, '2026-04-06 13:54:46');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_user_lesson_progress`
--

CREATE TABLE `modx_partnerstraining_user_lesson_progress` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `lesson_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int UNSIGNED NOT NULL DEFAULT '0',
  `max_time` int UNSIGNED NOT NULL DEFAULT '0',
  `watched_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_user_lesson_progress`
--

INSERT INTO `modx_partnerstraining_user_lesson_progress` (`id`, `course_id`, `lesson_id`, `user_id`, `status`, `current_time`, `max_time`, `watched_seconds`, `duration_seconds`, `progress_percent`, `completed`, `completedon`, `last_watch`) VALUES
(1, 1, 15, 2, 'completed', 2, 17, 17, 17, 100.00, 1, '2026-03-31 20:02:11', '2026-04-02 11:53:38'),
(2, 1, 16, 2, 'completed', 1, 9, 9, 9, 100.00, 1, '2026-03-31 21:04:45', '2026-04-02 11:54:20'),
(3, 1, 17, 2, 'completed', 3, 17, 17, 17, 100.00, 1, '2026-03-31 22:12:25', '2026-04-01 23:52:52'),
(4, 1, 15, 3, 'completed', 2, 17, 17, 17, 100.00, 1, '2026-04-01 11:35:06', '2026-04-06 11:53:35'),
(5, 1, 16, 3, 'completed', 1, 9, 9, 9, 100.00, 1, '2026-04-01 11:36:37', '2026-04-03 11:03:59'),
(6, 1, 17, 3, 'completed', 17, 17, 17, 17, 100.00, 1, '2026-04-01 12:12:36', '2026-04-03 11:49:01');

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_user_manager_link`
--

CREATE TABLE `modx_partnerstraining_user_manager_link` (
  `id` int UNSIGNED NOT NULL,
  `manager_user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `employee_user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `createdon` datetime DEFAULT NULL,
  `createdby` int UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_user_manager_link`
--

INSERT INTO `modx_partnerstraining_user_manager_link` (`id`, `manager_user_id`, `employee_user_id`, `is_active`, `createdon`, `createdby`) VALUES
(3, 1336, 1320, 1, '2026-03-24 20:52:57', 2),
(4, 3, 1, 1, '2026-03-30 10:10:16', 3);

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_user_module_progress`
--

CREATE TABLE `modx_partnerstraining_user_module_progress` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `current_time` int UNSIGNED NOT NULL DEFAULT '0',
  `max_time` int UNSIGNED NOT NULL DEFAULT '0',
  `watched_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `duration_seconds` int UNSIGNED NOT NULL DEFAULT '0',
  `progress_percent` decimal(5,2) NOT NULL DEFAULT '0.00',
  `completed` tinyint(1) NOT NULL DEFAULT '0',
  `completedon` datetime DEFAULT NULL,
  `last_watch` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_user_module_progress`
--

INSERT INTO `modx_partnerstraining_user_module_progress` (`id`, `course_id`, `module_id`, `user_id`, `status`, `current_time`, `max_time`, `watched_seconds`, `duration_seconds`, `progress_percent`, `completed`, `completedon`, `last_watch`) VALUES
(1, 1, 1, 2, 'in_progress', 0, 0, 43, 44, 60.00, 0, NULL, '2026-04-02 12:03:32'),
(2, 1, 1, 3, 'in_progress', 0, 0, 17, 73, 33.33, 0, NULL, '2026-04-06 13:54:46'),
(3, 1, 2, 2, 'not_started', 0, 0, 0, 11, 0.00, 0, NULL, NULL),
(4, 1, 3, 2, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL),
(5, 1, 4, 2, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL),
(6, 1, 5, 2, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL),
(7, 1, 2, 3, 'not_started', 0, 0, 0, 11, 0.00, 0, NULL, NULL),
(8, 1, 3, 3, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL),
(9, 1, 4, 3, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL),
(10, 1, 5, 3, 'not_started', 0, 0, 0, 0, 0.00, 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `modx_partnerstraining_user_test_status`
--

CREATE TABLE `modx_partnerstraining_user_test_status` (
  `id` int UNSIGNED NOT NULL,
  `course_id` int UNSIGNED NOT NULL DEFAULT '0',
  `module_id` int UNSIGNED NOT NULL DEFAULT '0',
  `usertest_test_id` int UNSIGNED NOT NULL DEFAULT '0',
  `user_id` int UNSIGNED NOT NULL DEFAULT '0',
  `last_result_id` int UNSIGNED NOT NULL DEFAULT '0',
  `attempts` int UNSIGNED NOT NULL DEFAULT '0',
  `passed` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(32) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'not_started',
  `last_score` decimal(7,2) NOT NULL DEFAULT '0.00',
  `last_passedon` datetime DEFAULT NULL,
  `updatedon` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `modx_partnerstraining_user_test_status`
--

INSERT INTO `modx_partnerstraining_user_test_status` (`id`, `course_id`, `module_id`, `usertest_test_id`, `user_id`, `last_result_id`, `attempts`, `passed`, `status`, `last_score`, `last_passedon`, `updatedon`) VALUES
(1, 1, 1, 2, 2, 0, 0, 0, 'not_started', 0.00, NULL, NULL),
(2, 1, 1, 2, 3, 0, 0, 0, 'not_started', 0.00, NULL, NULL),
(3, 1, 1, 1, 2, 3, 3, 0, 'in_progress', 0.00, NULL, '2026-04-06 10:59:19'),
(4, 1, 1, 1, 3, 7, 4, 0, 'in_progress', 0.00, NULL, '2026-04-06 13:54:46');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `modx_partnerstraining_courses`
--
ALTER TABLE `modx_partnerstraining_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resource_id` (`resource_id`);

--
-- Индексы таблицы `modx_partnerstraining_course_access`
--
ALTER TABLE `modx_partnerstraining_course_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_principal` (`course_id`,`principal_type`,`principal_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `usergroup_id` (`usergroup_id`),
  ADD KEY `principal` (`principal_type`,`principal_id`);

--
-- Индексы таблицы `modx_partnerstraining_lesson_videos`
--
ALTER TABLE `modx_partnerstraining_lesson_videos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `lesson_sort` (`lesson_id`,`sort_order`);

--
-- Индексы таблицы `modx_partnerstraining_modules`
--
ALTER TABLE `modx_partnerstraining_modules`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `resource_id` (`resource_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Индексы таблицы `modx_partnerstraining_module_lessons`
--
ALTER TABLE `modx_partnerstraining_module_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `module_sort` (`module_id`,`sort_order`);

--
-- Индексы таблицы `modx_partnerstraining_module_slides`
--
ALTER TABLE `modx_partnerstraining_module_slides`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_video_slide` (`lesson_video_id`,`slide_no`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `lesson_timecode` (`lesson_id`,`timecode_ms`),
  ADD KEY `lesson_video_id` (`lesson_video_id`);

--
-- Индексы таблицы `modx_partnerstraining_module_videos`
--
ALTER TABLE `modx_partnerstraining_module_videos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_video_quality` (`lesson_video_id`,`quality`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `lesson_id` (`lesson_id`),
  ADD KEY `lesson_video_id` (`lesson_video_id`);

--
-- Индексы таблицы `modx_partnerstraining_practice_attempts`
--
ALTER TABLE `modx_partnerstraining_practice_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `test_link_id` (`test_link_id`),
  ADD KEY `course_module_user` (`course_id`,`module_id`,`user_id`),
  ADD KEY `status` (`status`);

--
-- Индексы таблицы `modx_partnerstraining_practice_messages`
--
ALTER TABLE `modx_partnerstraining_practice_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attempt_id` (`attempt_id`),
  ADD KEY `createdon` (`createdon`);

--
-- Индексы таблицы `modx_partnerstraining_test_links`
--
ALTER TABLE `modx_partnerstraining_test_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_link` (`course_id`,`module_id`,`usertest_test_id`,`link_type`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `module_id` (`module_id`),
  ADD KEY `usertest_test_id` (`usertest_test_id`);

--
-- Индексы таблицы `modx_partnerstraining_user_courses`
--
ALTER TABLE `modx_partnerstraining_user_courses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `course_user` (`course_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Индексы таблицы `modx_partnerstraining_user_lesson_progress`
--
ALTER TABLE `modx_partnerstraining_user_lesson_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lesson_user` (`lesson_id`,`user_id`),
  ADD KEY `course_user` (`course_id`,`user_id`);

--
-- Индексы таблицы `modx_partnerstraining_user_manager_link`
--
ALTER TABLE `modx_partnerstraining_user_manager_link`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `manager_employee` (`manager_user_id`,`employee_user_id`),
  ADD KEY `manager_user_id` (`manager_user_id`),
  ADD KEY `employee_user_id` (`employee_user_id`);

--
-- Индексы таблицы `modx_partnerstraining_user_module_progress`
--
ALTER TABLE `modx_partnerstraining_user_module_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `module_user` (`module_id`,`user_id`),
  ADD KEY `course_user` (`course_id`,`user_id`);

--
-- Индексы таблицы `modx_partnerstraining_user_test_status`
--
ALTER TABLE `modx_partnerstraining_user_test_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `test_user` (`usertest_test_id`,`user_id`,`module_id`),
  ADD KEY `course_user` (`course_id`,`user_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_courses`
--
ALTER TABLE `modx_partnerstraining_courses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_course_access`
--
ALTER TABLE `modx_partnerstraining_course_access`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_lesson_videos`
--
ALTER TABLE `modx_partnerstraining_lesson_videos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_modules`
--
ALTER TABLE `modx_partnerstraining_modules`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_module_lessons`
--
ALTER TABLE `modx_partnerstraining_module_lessons`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_module_slides`
--
ALTER TABLE `modx_partnerstraining_module_slides`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=371;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_module_videos`
--
ALTER TABLE `modx_partnerstraining_module_videos`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=97;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_practice_attempts`
--
ALTER TABLE `modx_partnerstraining_practice_attempts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_practice_messages`
--
ALTER TABLE `modx_partnerstraining_practice_messages`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_test_links`
--
ALTER TABLE `modx_partnerstraining_test_links`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_user_courses`
--
ALTER TABLE `modx_partnerstraining_user_courses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_user_lesson_progress`
--
ALTER TABLE `modx_partnerstraining_user_lesson_progress`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_user_manager_link`
--
ALTER TABLE `modx_partnerstraining_user_manager_link`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_user_module_progress`
--
ALTER TABLE `modx_partnerstraining_user_module_progress`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT для таблицы `modx_partnerstraining_user_test_status`
--
ALTER TABLE `modx_partnerstraining_user_test_status`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

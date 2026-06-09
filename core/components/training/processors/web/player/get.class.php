<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebPlayerGetProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    protected function buildSlideItems(array $rows, array $videoRows, $moduleResourceId, $lessonId, $currentVideoId, array $videoItems = array())
    {
        $videoRows = array_values($videoRows);
        $items = array();
        $videoCount = count($videoRows);
        $videoPositions = array();
        $videoMeta = array();
        foreach ($videoRows as $index => $videoRow) {
            $videoPositions[(int)$videoRow['id']] = $index + 1;
        }
        foreach ($videoItems as $videoItem) {
            $videoMeta[(int)$videoItem['id']] = $videoItem;
        }

        foreach ($rows as $row) {
            $slideVideoId = (int)$row['lesson_video_id'];
            if ($slideVideoId <= 0) {
                continue;
            }
            $videoPosition = isset($videoPositions[$slideVideoId]) ? (int)$videoPositions[$slideVideoId] : 0;
            $videoTitle = trim((string)$row['video_title']);
            if ($videoTitle === '') {
                $videoTitle = 'Видео ' . ($videoPosition > 0 ? $videoPosition : $slideVideoId);
            }

            $meta = 'Видео ' . ($videoPosition > 0 ? $videoPosition : 1) . ' из ' . ($videoCount > 0 ? $videoCount : 1);
            $videoState = isset($videoMeta[$slideVideoId]) ? $videoMeta[$slideVideoId] : array();
            $locked = !empty($videoState['locked']) ? 1 : 0;

            $items[] = array(
                'id' => (int)$row['id'],
                'title' => $videoTitle,
                'image' => (string)$row['image'],
                'slide_no' => (int)$row['slide_no'],
                'timecode_ms' => (int)$row['timecode_ms'],
                'lesson_video_id' => $slideVideoId,
                'video_title' => $videoTitle,
                'video_position' => $videoPosition,
                'video_total' => $videoCount,
                'meta' => $meta,
                'url' => $locked ? '' : TrainingWebHelper::makePlayerUrl($this->modx, $moduleResourceId, $lessonId, $slideVideoId),
                'locked' => $locked,
                'is_current_video' => ($slideVideoId === (int)$currentVideoId) ? 1 : 0,
            );
        }

        return $items;
    }

    protected function buildTimelineSlides(array $rows)
    {
        $items = array();
        foreach ($rows as $row) {
            $items[] = array(
                'id' => (int)$row['id'],
                'title' => 'Слайд ' . (int)$row['slide_no'],
                'image' => (string)$row['image'],
                'slide_no' => (int)$row['slide_no'],
                'timecode_ms' => (int)$row['timecode_ms'],
                'lesson_video_id' => (int)$row['lesson_video_id'],
            );
        }
        return $items;
    }


    protected function buildVideoItems(array $videoRows, array $progressMap, $moduleResourceId, $lessonId, $currentVideoId, array $qualitiesMap, array $timelineMap)
    {
        $items = array();
        $total = count($videoRows);
        $previousCompleted = true;

        foreach ($videoRows as $index => $videoRow) {
            $videoId = (int)$videoRow['id'];
            $progress = isset($progressMap[$videoId]) ? $progressMap[$videoId] : array();
            $defaultQuality = null;
            if (isset($qualitiesMap[$videoId]) && is_array($qualitiesMap[$videoId])) {
                foreach ($qualitiesMap[$videoId] as $qualityRow) {
                    if (!empty($qualityRow['is_default'])) {
                        $defaultQuality = $qualityRow;
                        break;
                    }
                }
                if (!$defaultQuality && !empty($qualitiesMap[$videoId])) {
                    $defaultQuality = reset($qualitiesMap[$videoId]);
                }
            }

            $resumeTime = 0;
            if ($progress && (int)$progress['completed'] !== 1) {
                $resumeTime = max((int)$progress['current_time'], (int)$progress['max_time']);
            }

            $hasOwnProgress = $progress && (
                !empty($progress['completed'])
                || (int)$progress['watched_seconds'] > 0
                || (int)$progress['current_time'] > 0
                || (int)$progress['max_time'] > 0
            );

            $locked = $index === 0 ? 0 : (($previousCompleted || $hasOwnProgress || $videoId === (int)$currentVideoId) ? 0 : 1);
            $completed = $progress ? (!empty($progress['completed']) ? 1 : 0) : 0;

            $timelineSlides = isset($timelineMap[$videoId]) ? $timelineMap[$videoId] : array();
            $poster = trim((string)$videoRow['preview_image']);
            if ($poster === '' && !empty($timelineSlides)) {
                $firstTimelineSlide = reset($timelineSlides);
                if ($firstTimelineSlide && !empty($firstTimelineSlide['image'])) {
                    $poster = (string)$firstTimelineSlide['image'];
                }
            }

            $items[] = array(
                'id' => $videoId,
                'title' => trim((string)$videoRow['title']),
                'sort_order' => (int)$videoRow['sort_order'],
                'position' => $index + 1,
                'total' => $total,
                'duration_seconds' => (int)$videoRow['duration_seconds'],
                'duration_text' => TrainingWebHelper::formatDuration((int)$videoRow['duration_seconds']),
                'progress_percent' => $progress ? round((float)$progress['progress_percent']) : 0,
                'completed' => $completed,
                'current_time' => $progress ? (int)$progress['current_time'] : 0,
                'max_time' => $progress ? (int)$progress['max_time'] : 0,
                'resume_time' => $resumeTime,
                'preview_image' => $poster,
                'video_url' => $defaultQuality ? (string)$defaultQuality['file_path'] : '',
                'quality' => $defaultQuality ? (string)$defaultQuality['quality'] : '',
                'locked' => $locked,
                'url' => $locked ? '' : TrainingWebHelper::makePlayerUrl($this->modx, $moduleResourceId, $lessonId, $videoId),
                'is_current' => $videoId === (int)$currentVideoId ? 1 : 0,
            );

            $previousCompleted = $completed === 1;
        }

        return $items;
    }

    protected function buildTimelineMap(array $rows)
    {
        $map = array();
        foreach ($rows as $row) {
            $videoId = (int)$row['lesson_video_id'];
            if ($videoId <= 0) {
                continue;
            }
            if (!isset($map[$videoId])) {
                $map[$videoId] = array();
            }
            $map[$videoId][] = array(
                'id' => (int)$row['id'],
                'title' => 'Слайд ' . (int)$row['slide_no'],
                'image' => (string)$row['image'],
                'slide_no' => (int)$row['slide_no'],
                'timecode_ms' => (int)$row['timecode_ms'],
                'lesson_video_id' => $videoId,
            );
        }
        return $map;
    }

    public function process()
    {
        if ($failure = TrainingWebHelper::requireAuth($this)) {
            return $failure;
        }

        $service = TrainingWebHelper::getProgressService($this->modx);
        $userId = (int)$this->modx->user->get('id');
        $moduleResourceId = (int)$this->getProperty('module', 0);
        $lessonId = (int)$this->getProperty('lesson', 0);
        $requestedVideoId = (int)$this->getProperty('video', 0);

        if ($moduleResourceId <= 0) {
            return $this->failure('Не указан модуль', array('code' => 400));
        }

        $resolved = $service->resolvePlayerContext($moduleResourceId, $lessonId, $userId);
        if (empty($resolved['success'])) {
            return $this->failure(isset($resolved['message']) ? $resolved['message'] : 'Не удалось открыть урок', $resolved);
        }

        /** @var TrainingModule $module */
        $module = $resolved['module'];
        /** @var TrainingModuleLesson $lesson */
        $lesson = $resolved['lesson'];
        $courseId = (int)$resolved['course_id'];
        $moduleId = (int)$module->get('id');
        $lessonId = (int)$lesson->get('id');
        $moduleResourceId = (int)$resolved['resolved_module_resource_id'];

        $service->syncUserCourseForUser($courseId, $userId);
        TrainingWebHelper::syncLegacyLessonProgressFromVideos($this->modx, $service, $courseId, $moduleId, $lessonId, $userId);
        $moduleProgress = $service->recalculateModuleProgressFromLessons($courseId, $moduleId, $userId);
        $userCourse = $service->recalculateUserCourse($courseId, $userId);

        /** @var TrainingCourse $course */
        $course = $this->modx->getObject('TrainingCourse', array('id' => $courseId));
        /** @var modResource $courseResource */
        $courseResource = $course ? $course->getOne('Resource') : null;
        /** @var modResource $moduleResource */
        $moduleResource = $module->getOne('Resource');

        $lessonVideoRows = TrainingWebHelper::fetchLessonVideos($this->modx, $lessonId, true);
        if (empty($lessonVideoRows)) {
            return $this->failure('У урока нет активного видео', array('code' => 404));
        }

        $currentVideoId = TrainingWebHelper::getPreferredLessonVideoId($this->modx, $courseId, $lessonId, $userId, $requestedVideoId);
        if ($currentVideoId <= 0) {
            $firstVideo = reset($lessonVideoRows);
            $currentVideoId = $firstVideo ? (int)$firstVideo['id'] : 0;
        }

        $currentVideoRow = TrainingWebHelper::fetchLessonVideo($this->modx, $currentVideoId, $lessonId, true);
        if (!$currentVideoRow) {
            $firstVideo = reset($lessonVideoRows);
            $currentVideoRow = $firstVideo ?: null;
            $currentVideoId = $currentVideoRow ? (int)$currentVideoRow['id'] : 0;
        }
        if (!$currentVideoRow || $currentVideoId <= 0) {
            return $this->failure('У урока нет активного видео', array('code' => 404));
        }

        $qualities = TrainingWebHelper::fetchLessonVideoQualities($this->modx, $currentVideoId, true);
        $defaultQuality = TrainingWebHelper::fetchDefaultVideoQuality($this->modx, $currentVideoId);
        if (!$defaultQuality) {
            return $this->failure('У выбранного видео нет активных качеств', array('code' => 404));
        }

        $progressMap = TrainingWebHelper::getLessonVideoProgressMap($this->modx, $courseId, $lessonId, $userId);
        $allSlidesRows = array_values(array_filter(TrainingWebHelper::fetchAllLessonSlides($this->modx, $lessonId), function ($row) {
            return (int)$row['lesson_video_id'] > 0;
        }));
        $timelineMap = $this->buildTimelineMap($allSlidesRows);
        $qualitiesMap = array();
        foreach ($lessonVideoRows as $lessonVideoRow) {
            $videoId = (int)$lessonVideoRow['id'];
            $qualitiesMap[$videoId] = TrainingWebHelper::fetchLessonVideoQualities($this->modx, $videoId, true);
        }
        $videoItems = $this->buildVideoItems($lessonVideoRows, $progressMap, $moduleResourceId, $lessonId, $currentVideoId, $qualitiesMap, $timelineMap);
        $currentVideoProgress = isset($progressMap[$currentVideoId]) ? $progressMap[$currentVideoId] : null;
        $currentVideoResumeTime = 0;
        if ($currentVideoProgress && (int)$currentVideoProgress['completed'] !== 1) {
            $currentVideoResumeTime = max((int)$currentVideoProgress['current_time'], (int)$currentVideoProgress['max_time']);
        }
        $allSlides = $this->buildSlideItems($allSlidesRows, $lessonVideoRows, $moduleResourceId, $lessonId, $currentVideoId, $videoItems);
        $timelineSlides = isset($timelineMap[$currentVideoId]) ? $timelineMap[$currentVideoId] : array();

        list($currentVideoPosition, $totalVideos) = TrainingWebHelper::getLessonVideoPosition($lessonVideoRows, $currentVideoId);

        $poster = '';
        foreach ($timelineSlides as $timelineSlide) {
            if (!empty($timelineSlide['image'])) {
                $poster = (string)$timelineSlide['image'];
                break;
            }
        }
        if ($poster === '' && trim((string)$currentVideoRow['preview_image']) !== '') {
            $poster = trim((string)$currentVideoRow['preview_image']);
        }
        if ($poster === '' && $courseResource) {
            $poster = trim((string)$courseResource->getTVValue('image_curse'));
        }

        $prevLessonId = $service->getPreviousLessonId($moduleId, $lessonId, true);
        $nextLessonId = $service->getNextLessonId($moduleId, $lessonId, true);
        $prevVideoId = 0;
        $nextVideoId = 0;
        foreach ($lessonVideoRows as $index => $videoRow) {
            if ((int)$videoRow['id'] !== $currentVideoId) {
                continue;
            }
            if ($index > 0) {
                $prevVideoId = (int)$lessonVideoRows[$index - 1]['id'];
            }
            if ($index < (count($lessonVideoRows) - 1)) {
                $nextVideoId = (int)$lessonVideoRows[$index + 1]['id'];
            }
            break;
        }
        $videoMetaById = array();
        foreach ($videoItems as $videoItem) {
            $videoMetaById[(int)$videoItem['id']] = $videoItem;
        }
        $prevUrl = ($prevVideoId > 0 && (!isset($videoMetaById[$prevVideoId]) || empty($videoMetaById[$prevVideoId]['locked']))) ? TrainingWebHelper::makePlayerUrl($this->modx, (int)$module->get('resource_id'), $lessonId, $prevVideoId) : '';
        $nextUrl = ($nextVideoId > 0 && (!isset($videoMetaById[$nextVideoId]) || empty($videoMetaById[$nextVideoId]['locked']))) ? TrainingWebHelper::makePlayerUrl($this->modx, (int)$module->get('resource_id'), $lessonId, $nextVideoId) : '';

        $lessonRows = $service->getModuleLessons($moduleId, true, false);
        $currentLessonIndex = 0;
        $currentLessonTotal = count($lessonRows);
        foreach ($lessonRows as $index => $lessonRow) {
            if ((int)$lessonRow->get('id') === $lessonId) {
                $currentLessonIndex = $index + 1;
                break;
            }
        }

        return $this->success('', array(
            'redirected' => !empty($resolved['redirected']) ? 1 : 0,
            'requested_module_resource_id' => (int)$resolved['requested_module_resource_id'],
            'requested_lesson_id' => (int)$resolved['requested_lesson_id'],
            'requested_video_id' => $requestedVideoId,
            'resolved_module_resource_id' => $moduleResourceId,
            'resolved_lesson_id' => $lessonId,
            'resolved_video_id' => $currentVideoId,
            'completion_threshold_percent' => (int)$service->getLessonCompletionThresholdPercent(),
            'course' => array(
                'id' => $courseId,
                'resource_id' => $course ? (int)$course->get('resource_id') : 0,
                'title' => $courseResource ? (string)$courseResource->get('pagetitle') : '',
                'url' => $courseResource ? $this->modx->makeUrl((int)$courseResource->get('id')) : '',
                'progress_percent' => $userCourse ? round((float)$userCourse->get('progress_percent')) : 0,
                'completed_modules' => $userCourse ? (int)$userCourse->get('completed_modules') : 0,
                'total_modules' => $userCourse ? (int)$userCourse->get('total_modules') : 0,
                'status' => $userCourse ? (string)$userCourse->get('status') : 'assigned',
            ),
            'module' => array(
                'id' => $moduleId,
                'resource_id' => (int)$module->get('resource_id'),
                'title' => $moduleResource ? (string)$moduleResource->get('pagetitle') : '',
                'progress_percent' => $moduleProgress ? round((float)$moduleProgress->get('progress_percent')) : 0,
                'completed' => $moduleProgress ? ((int)$moduleProgress->get('completed') === 1 ? 1 : 0) : 0,
                'duration_seconds' => $moduleProgress ? (int)$moduleProgress->get('duration_seconds') : 0,
                'duration_text' => TrainingWebHelper::formatDuration($moduleProgress ? (int)$moduleProgress->get('duration_seconds') : 0),
            ),
            'lesson' => array(
                'id' => $lessonId,
                'title' => (string)$lesson->get('title'),
                'description' => (string)$lesson->get('description'),
                'duration_seconds' => max((int)$lesson->get('duration_seconds'), (int)$currentVideoRow['duration_seconds']),
                'duration_text' => TrainingWebHelper::formatDuration(max((int)$lesson->get('duration_seconds'), (int)$currentVideoRow['duration_seconds'])),
                'poster' => $poster,
            ),
            'video_items' => array_values($videoItems),
            'video_qualities_map' => $qualitiesMap,
            'timeline_slides_map' => $timelineMap,
            'current_video' => array(
                'id' => $currentVideoId,
                'title' => trim((string)$currentVideoRow['title']),
                'sort_order' => (int)$currentVideoRow['sort_order'],
                'position' => $currentVideoPosition,
                'total' => $totalVideos,
                'duration_seconds' => (int)$currentVideoRow['duration_seconds'],
                'duration_text' => TrainingWebHelper::formatDuration((int)$currentVideoRow['duration_seconds']),
                'progress_percent' => $currentVideoProgress ? round((float)$currentVideoProgress['progress_percent']) : 0,
                'completed' => $currentVideoProgress ? (!empty($currentVideoProgress['completed']) ? 1 : 0) : 0,
                'current_time' => $currentVideoProgress ? (int)$currentVideoProgress['current_time'] : 0,
                'max_time' => $currentVideoProgress ? (int)$currentVideoProgress['max_time'] : 0,
                'resume_time' => $currentVideoResumeTime,
                'preview_image' => trim((string)$currentVideoRow['preview_image']),
                'video_url' => (string)$defaultQuality['file_path'],
                'quality' => (string)$defaultQuality['quality'],
            ),
            'videos' => array_values(array_map(function ($row) {
                return array(
                    'id' => (int)$row['id'],
                    'quality' => (string)$row['quality'],
                    'mime' => (string)$row['mime'],
                    'file_path' => (string)$row['file_path'],
                    'width' => (int)$row['width'],
                    'height' => (int)$row['height'],
                    'bitrate' => (int)$row['bitrate'],
                    'is_default' => !empty($row['is_default']) ? 1 : 0,
                );
            }, $qualities)),
            'slides_all' => $allSlides,
            'timeline_slides' => $timelineSlides,
            'nav' => array(
                'current_index' => $currentVideoPosition,
                'total_lessons' => $totalVideos,
                'prev_lesson_id' => $prevVideoId,
                'prev_url' => $prevUrl,
                'next_lesson_id' => $nextVideoId,
                'next_url' => $nextUrl,
                'next_locked' => ($nextVideoId > 0 && isset($videoMetaById[$nextVideoId]) && !empty($videoMetaById[$nextVideoId]['locked'])) ? 1 : 0,
                'prev_video_id' => $prevVideoId,
                'next_video_id' => $nextVideoId,
                'prev_lesson_url' => $prevLessonId > 0 ? TrainingWebHelper::makePlayerUrl($this->modx, (int)$module->get('resource_id'), $prevLessonId, TrainingWebHelper::getPreferredLessonVideoId($this->modx, $courseId, $prevLessonId, $userId, 0)) : '',
                'next_lesson_url' => ($nextLessonId > 0 && $service->canAccessLesson($courseId, $moduleId, $nextLessonId, $userId)) ? TrainingWebHelper::makePlayerUrl($this->modx, (int)$module->get('resource_id'), $nextLessonId, TrainingWebHelper::getPreferredLessonVideoId($this->modx, $courseId, $nextLessonId, $userId, 0)) : '',
            ),
        ));
    }
}

return 'TrainingWebPlayerGetProcessor';

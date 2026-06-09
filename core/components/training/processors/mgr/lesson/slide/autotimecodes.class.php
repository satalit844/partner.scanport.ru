<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonSlideAutotimecodesProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) { return $this->failure('Сначала выбери видео урока'); }
        $durationSeconds = (int)$video['duration_seconds'];
        if ($durationSeconds <= 0) {
            return $this->failure('У выбранного видео нет длительности. Сначала обработай видео');
        }
        $updated = TrainingLessonVideoHelper::applyEvenSlideTimecodes($this->modx, $lessonVideoId, $durationSeconds * 1000);
        return $this->success('Таймкоды расставлены', ['updated' => $updated]);
    }
}
return 'TrainingLessonSlideAutotimecodesProcessor';

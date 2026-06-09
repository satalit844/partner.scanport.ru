<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleSlideAutotimecodesProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->failure('Сначала выбери видео');
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return $this->failure('Видео не найдено');
        }

        $durationSeconds = (int)$lesson->get('duration_seconds');
        if ($durationSeconds <= 0) {
            return $this->failure('У выбранного видео пока нет длительности. Сначала обработай видео');
        }

        $updated = TrainingMediaHelper::applyEvenLessonSlideTimecodes($this->modx, $lessonId, $durationSeconds * 1000);
        if ($updated > 0) {
            $lesson->set('presentation_status', 'ready');
            $lesson->save();
        }

        return $this->success('Таймкоды расставлены', [
            'lesson_id' => $lessonId,
            'updated' => $updated,
            'duration_seconds' => $durationSeconds,
            'duration_human' => TrainingMediaHelper::formatSeconds($durationSeconds),
        ]);
    }
}

return 'TrainingModuleSlideAutotimecodesProcessor';

<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleVideoRemoveProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $ids = $this->collectIds();
        if (empty($ids)) {
            return $this->failure('Не выбраны видео');
        }

        $removed = 0;
        $affectedLessons = [];

        foreach ($ids as $id) {
            /** @var TrainingModuleVideo $video */
            $video = $this->modx->getObject('TrainingModuleVideo', ['id' => $id]);
            if (!$video) {
                continue;
            }

            $lessonId = (int)$video->get('lesson_id');
            if (TrainingMediaHelper::deleteLessonVideoRecord($this->modx, $video, true)) {
                $removed++;
                if ($lessonId > 0) {
                    $affectedLessons[$lessonId] = $lessonId;
                }
            }
        }

        foreach ($affectedLessons as $lessonId) {
            /** @var TrainingModuleLesson $lesson */
            $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
            if (!$lesson) {
                continue;
            }

            TrainingMediaHelper::refreshLessonVideoState($this->modx, $lesson);
            TrainingMediaHelper::refreshLessonPresentationState($this->modx, $lesson);
        }

        return $this->success('Видео удалены', [
            'removed' => $removed,
            'lesson_ids' => array_values($affectedLessons),
        ]);
    }

    protected function collectIds()
    {
        $raw = $this->getProperty('ids', $this->getProperty('id', ''));
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', array_map('trim', explode(',', $raw)))));
    }
}

return 'TrainingModuleVideoRemoveProcessor';

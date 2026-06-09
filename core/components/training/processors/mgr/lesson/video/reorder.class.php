<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoReorderProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $id = (int)$this->getProperty('id');
        $targetId = (int)$this->getProperty('target_id');
        $position = strtolower((string)$this->getProperty('position', 'after'));

        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $id);
        if (!$video) {
            return $this->failure('Видео урока не найдено');
        }

        $target = TrainingLessonVideoHelper::fetchVideo($this->modx, $targetId);
        if (!$target) {
            return $this->failure('Целевое видео урока не найдено');
        }

        $lessonId = (int)$video['lesson_id'];
        if ($lessonId <= 0 || $lessonId !== (int)$target['lesson_id']) {
            return $this->failure('Видео должны относиться к одному уроку');
        }

        if (!TrainingLessonVideoHelper::reorderRows(
            $this->modx,
            TrainingLessonVideoHelper::lessonVideosTable($this->modx),
            'lesson_id',
            $lessonId,
            'sort_order',
            $id,
            $targetId,
            $position
        )) {
            return $this->failure('Не удалось изменить порядок видео урока');
        }

        return $this->success('Порядок видео урока обновлён');
    }
}
return 'TrainingLessonVideoReorderProcessor';

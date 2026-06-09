<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonSlideReorderProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    protected function fetchSlide($id)
    {
        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        $stmt = $this->modx->prepare('SELECT * FROM `' . $table . '` WHERE `id` = :id LIMIT 1');
        if (!$stmt || !$stmt->execute([':id' => (int)$id])) {
            return null;
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function process()
    {
        $id = (int)$this->getProperty('id');
        $targetId = (int)$this->getProperty('target_id');
        $position = strtolower((string)$this->getProperty('position', 'after'));

        $slide = $this->fetchSlide($id);
        if (!$slide) {
            return $this->failure('Слайд не найден');
        }

        $target = $this->fetchSlide($targetId);
        if (!$target) {
            return $this->failure('Целевой слайд не найден');
        }

        $lessonVideoId = (int)$slide['lesson_video_id'];
        if ($lessonVideoId <= 0 || $lessonVideoId !== (int)$target['lesson_video_id']) {
            return $this->failure('Слайды должны относиться к одному видео урока');
        }

        if (!TrainingLessonVideoHelper::reorderRows(
            $this->modx,
            TrainingLessonVideoHelper::slidesTable($this->modx),
            'lesson_video_id',
            $lessonVideoId,
            'slide_no',
            $id,
            $targetId,
            $position
        )) {
            return $this->failure('Не удалось изменить порядок слайдов');
        }

        return $this->success('Порядок слайдов обновлён');
    }
}
return 'TrainingLessonSlideReorderProcessor';

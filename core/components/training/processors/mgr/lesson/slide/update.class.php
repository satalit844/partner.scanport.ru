<?php

require_once dirname(__DIR__) . '/_video_helper.php';
require_once dirname(dirname(__DIR__)) . '/module/slide/_helpers.php';

class TrainingLessonSlideUpdateProcessor extends modProcessor
{
    public function checkPermissions(){return true;}
    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function process()
    {
        $id = (int)$this->getProperty('id');
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        if ($id <= 0) { return $this->failure('Не указан слайд'); }
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) { return $this->failure('Сначала выбери видео урока'); }

        $image = trim((string)$this->getProperty('image'));
        if ($image === '') { return $this->failure('Выберите изображение'); }
        $image = TrainingModuleSlideHelper::normalizeWebPath($this->modx, $image);
        $slideNo = (int)$this->getProperty('slide_no', 0);
        if ($slideNo <= 0) { return $this->failure('Укажите номер слайда'); }

        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        $sql = 'UPDATE `' . $table . '` SET `slide_no`=:slide_no,`image`=:image,`timecode_ms`=:timecode_ms,`is_active`=:is_active WHERE `id`=:id';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute([
            ':slide_no' => $slideNo,
            ':image' => $image,
            ':timecode_ms' => max(0, (int)$this->getProperty('timecode_ms', 0)),
            ':is_active' => $this->boolValue($this->getProperty('is_active', 1)),
            ':id' => $id,
        ])) {
            return $this->failure('Не удалось сохранить слайд');
        }
        return $this->success('Слайд сохранён');
    }
}
return 'TrainingLessonSlideUpdateProcessor';

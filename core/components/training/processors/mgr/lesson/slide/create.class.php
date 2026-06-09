<?php

require_once dirname(__DIR__) . '/_video_helper.php';
require_once dirname(dirname(__DIR__)) . '/module/slide/_helpers.php';

class TrainingLessonSlideCreateProcessor extends modProcessor
{
    public function checkPermissions(){return true;}
    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) { return $this->failure('Сначала выбери видео урока'); }
        $lessonId = (int)$video['lesson_id'];
        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, $lessonId);
        if (!$lesson) { return $this->failure('Урок не найден'); }

        $image = trim((string)$this->getProperty('image'));
        if ($image === '') { return $this->failure('Выберите изображение слайда'); }
        $image = TrainingModuleSlideHelper::normalizeWebPath($this->modx, $image);
        $slideNo = (int)$this->getProperty('slide_no', 0);
        if ($slideNo <= 0) {
            $stmt = $this->modx->prepare('SELECT MAX(`slide_no`) FROM `' . TrainingLessonVideoHelper::slidesTable($this->modx) . '` WHERE `lesson_video_id` = :lesson_video_id');
            $slideNo = 1;
            if ($stmt && $stmt->execute([':lesson_video_id' => $lessonVideoId])) {
                $slideNo = max(1, (int)$stmt->fetchColumn() + 1);
            }
        }

        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        $sql = 'INSERT INTO `' . $table . '` (`module_id`,`lesson_id`,`lesson_video_id`,`slide_no`,`image`,`timecode_ms`,`is_active`) VALUES (:module_id,:lesson_id,:lesson_video_id,:slide_no,:image,:timecode_ms,:is_active)';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute([
            ':module_id' => (int)$lesson->get('module_id'),
            ':lesson_id' => $lessonId,
            ':lesson_video_id' => $lessonVideoId,
            ':slide_no' => $slideNo,
            ':image' => $image,
            ':timecode_ms' => max(0, (int)$this->getProperty('timecode_ms', 0)),
            ':is_active' => $this->boolValue($this->getProperty('is_active', 1)),
        ])) {
            return $this->failure('Не удалось добавить слайд');
        }

        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);
        return $this->success('Слайд добавлен');
    }
}
return 'TrainingLessonSlideCreateProcessor';

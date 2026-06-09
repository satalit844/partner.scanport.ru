<?php

require_once dirname(__DIR__) . '/_video_helper.php';
require_once dirname(dirname(__DIR__)) . '/module/slide/_helpers.php';

class TrainingLessonSlideImportProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) { return $this->failure('Сначала выбери видео урока'); }
        $lessonId = (int)$video['lesson_id'];
        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, $lessonId);
        if (!$lesson) { return $this->failure('Урок не найден'); }

        $scan = TrainingModuleSlideHelper::scanLessonSlides($this->modx, $lessonId);
        if (empty($scan['dir']['exists'])) {
            $checked = !empty($scan['dir']['checked']) ? implode("\n", $scan['dir']['checked']) : '—';
            return $this->failure("Папка со слайдами урока не найдена.\nПроверено:\n" . $checked);
        }

        $table = TrainingLessonVideoHelper::slidesTable($this->modx);
        $existing = [];
        $stmt = $this->modx->prepare('SELECT `image` FROM `' . $table . '` WHERE `lesson_video_id` = :lesson_video_id');
        if ($stmt && $stmt->execute([':lesson_video_id' => $lessonVideoId])) {
            foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $existing[(string)$row['image']] = true;
            }
        }

        $maxSlideNo = 0;
        $stmt = $this->modx->prepare('SELECT MAX(`slide_no`) FROM `' . $table . '` WHERE `lesson_video_id` = :lesson_video_id');
        if ($stmt && $stmt->execute([':lesson_video_id' => $lessonVideoId])) {
            $maxSlideNo = (int)$stmt->fetchColumn();
        }

        $created = 0;
        $insert = $this->modx->prepare('INSERT INTO `' . $table . '` (`module_id`,`lesson_id`,`lesson_video_id`,`slide_no`,`image`,`timecode_ms`,`is_active`) VALUES (:module_id,:lesson_id,:lesson_video_id,:slide_no,:image,0,1)');
        foreach ((array)$scan['slides'] as $slide) {
            if (!empty($existing[$slide['path']])) { continue; }
            $slideNo = (int)$slide['slide_no'];
            if ($slideNo <= 0 || $slideNo <= $maxSlideNo) {
                $slideNo = $maxSlideNo + 1;
            }
            if ($insert && $insert->execute([
                ':module_id' => (int)$lesson->get('module_id'),
                ':lesson_id' => $lessonId,
                ':lesson_video_id' => $lessonVideoId,
                ':slide_no' => $slideNo,
                ':image' => $slide['path'],
            ])) {
                $created++;
                $existing[$slide['path']] = true;
                $maxSlideNo = max($maxSlideNo, $slideNo);
            }
        }

        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);
        return $this->success('Импорт завершён', ['created' => $created]);
    }
}
return 'TrainingLessonSlideImportProcessor';

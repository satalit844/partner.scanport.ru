<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonQualityCreateProcessor extends modProcessor
{
    public function checkPermissions(){return true;}
    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) { return $this->failure('Сначала выбери видео урока'); }
        $quality = trim((string)$this->getProperty('quality'));
        $filePath = trim((string)$this->getProperty('file_path'));
        if ($quality === '') { return $this->failure('Укажите качество'); }
        if ($filePath === '') { return $this->failure('Укажите файл'); }

        $table = TrainingLessonVideoHelper::qualitiesTable($this->modx);
        $sql = 'INSERT INTO `' . $table . '` (`module_id`,`lesson_id`,`lesson_video_id`,`quality`,`mime`,`file_path`,`width`,`height`,`bitrate`,`filesize`,`is_default`,`is_active`) VALUES (:module_id,:lesson_id,:lesson_video_id,:quality,:mime,:file_path,:width,:height,:bitrate,:filesize,:is_default,:is_active)';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute([
            ':module_id' => (int)$this->getProperty('module_id', 0) ?: (int)(TrainingLessonVideoHelper::getLesson($this->modx, (int)$video['lesson_id']) ? TrainingLessonVideoHelper::getLesson($this->modx, (int)$video['lesson_id'])->get('module_id') : 0),
            ':lesson_id' => (int)$video['lesson_id'],
            ':lesson_video_id' => $lessonVideoId,
            ':quality' => $quality,
            ':mime' => trim((string)$this->getProperty('mime', 'video/mp4')),
            ':file_path' => $filePath,
            ':width' => (int)$this->getProperty('width', 0),
            ':height' => (int)$this->getProperty('height', 0),
            ':bitrate' => (int)$this->getProperty('bitrate', 0),
            ':filesize' => (int)$this->getProperty('filesize', 0),
            ':is_default' => $this->boolValue($this->getProperty('is_default', 0)),
            ':is_active' => $this->boolValue($this->getProperty('is_active', 1)),
        ])) {
            return $this->failure('Не удалось добавить качество');
        }
        $id = (int)$this->modx->lastInsertId();
        if ($this->boolValue($this->getProperty('is_default', 0))) {
            TrainingLessonVideoHelper::clearDefaultQuality($this->modx, $lessonVideoId, $id);
        }
        return $this->success('Качество добавлено', ['id' => $id]);
    }
}
return 'TrainingLessonQualityCreateProcessor';

<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonQualityUpdateProcessor extends modProcessor
{
    public function checkPermissions(){return true;}
    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function process()
    {
        $id = (int)$this->getProperty('id');
        if ($id <= 0) { return $this->failure('Не указано качество'); }
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        if ($lessonVideoId <= 0) { return $this->failure('Сначала выбери видео урока'); }
        $quality = trim((string)$this->getProperty('quality'));
        $filePath = trim((string)$this->getProperty('file_path'));
        if ($quality === '') { return $this->failure('Укажите качество'); }
        if ($filePath === '') { return $this->failure('Укажите файл'); }

        $table = TrainingLessonVideoHelper::qualitiesTable($this->modx);
        $sql = 'UPDATE `' . $table . '` SET `quality`=:quality,`mime`=:mime,`file_path`=:file_path,`width`=:width,`height`=:height,`bitrate`=:bitrate,`filesize`=:filesize,`is_default`=:is_default,`is_active`=:is_active WHERE `id`=:id';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute([
            ':quality' => $quality,
            ':mime' => trim((string)$this->getProperty('mime', 'video/mp4')),
            ':file_path' => $filePath,
            ':width' => (int)$this->getProperty('width', 0),
            ':height' => (int)$this->getProperty('height', 0),
            ':bitrate' => (int)$this->getProperty('bitrate', 0),
            ':filesize' => (int)$this->getProperty('filesize', 0),
            ':is_default' => $this->boolValue($this->getProperty('is_default', 0)),
            ':is_active' => $this->boolValue($this->getProperty('is_active', 1)),
            ':id' => $id,
        ])) {
            return $this->failure('Не удалось сохранить качество');
        }
        if ($this->boolValue($this->getProperty('is_default', 0))) {
            TrainingLessonVideoHelper::clearDefaultQuality($this->modx, $lessonVideoId, $id);
        }
        return $this->success('Качество сохранено');
    }
}
return 'TrainingLessonQualityUpdateProcessor';

<?php

class TrainingModuleVideoCreateProcessor extends modObjectCreateProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public $classKey = 'TrainingModuleVideo';
    protected function boolValue($value)
    {
        return in_array((string)$value, ['1', 'true', 'yes', 'on'], true) || $value === true || $value === 1 ? 1 : 0;
    }

    public $objectType = 'training.module.video';

    public function beforeSet()
    {
        $moduleId = (int)$this->getProperty('module_id');
        $lessonId = (int)$this->getProperty('lesson_id');
        $quality = trim((string)$this->getProperty('quality'));
        $filePath = trim((string)$this->getProperty('file_path'));

        if ($moduleId <= 0 && $lessonId <= 0) {
            return 'Не указан модуль или урок';
        }
        if ($lessonId > 0 && $moduleId <= 0) {
            $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
            if (!$lesson) return 'Урок не найден';
            $moduleId = (int)$lesson->get('module_id');
        }

        if ($quality === '') {
            return 'Укажите качество видео';
        }

        if ($filePath === '') {
            return 'Укажите путь к видеофайлу';
        }

        require_once dirname(__DIR__) . '/slide/_helpers.php';

        $filePath = TrainingModuleSlideHelper::normalizeWebPath($this->modx, $filePath);
        $mime = trim((string)$this->getProperty('mime'));
        if ($mime === '') {
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mime = $ext === 'm3u8' ? 'application/vnd.apple.mpegurl' : 'video/mp4';
        }

        $filesize = (int)$this->getProperty('filesize');
        if ($filesize <= 0) {
            $filesize = (int)TrainingModuleSlideHelper::detectFileSize($this->modx, $filePath);
        }

        $this->setProperty('module_id', $moduleId);
        $this->setProperty('lesson_id', $lessonId);
        $this->setProperty('quality', $quality);
        $this->setProperty('file_path', $filePath);
        $this->setProperty('mime', $mime);
        $this->setProperty('width', (int)$this->getProperty('width', 0));
        $this->setProperty('height', (int)$this->getProperty('height', 0));
        $this->setProperty('bitrate', (int)$this->getProperty('bitrate', 0));
        $this->setProperty('filesize', $filesize);
        $this->setProperty('is_default', $this->boolValue($this->getProperty('is_default', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));

        return parent::beforeSet();
    }

    public function afterSave()
    {
        if ((int)$this->object->get('is_default') === 1) {
            $this->clearOtherDefaults();
        }

        return parent::afterSave();
    }

    protected function clearOtherDefaults()
    {
        $moduleId = (int)$this->object->get('module_id');
        $id = (int)$this->object->get('id');
        $c = $this->modx->newQuery('TrainingModuleVideo');
        $c->where([
            'lesson_id' => (int)$this->object->get('lesson_id'),
            'id:!=' => $id,
        ]);

        /** @var TrainingModuleVideo[] $items */
        $items = $this->modx->getCollection('TrainingModuleVideo', $c);
        foreach ($items as $item) {
            if ((int)$item->get('is_default') === 1) {
                $item->set('is_default', 0);
                $item->save();
            }
        }
    }
}

return 'TrainingModuleVideoCreateProcessor';

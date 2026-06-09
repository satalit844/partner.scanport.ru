<?php

class TrainingModuleVideoUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingModuleVideo';
    public $objectType = 'training.module.video';

    public function checkPermissions()
    {
        return true;
    }

    protected function boolValue($value)
    {
        return in_array((string)$value, ['1', 'true', 'yes', 'on'], true) || $value === true || $value === 1 ? 1 : 0;
    }

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $lessonId = (int)$this->getProperty('lesson_id');
        $quality = trim((string)$this->getProperty('quality'));
        $filePath = trim((string)$this->getProperty('file_path'));

        if ($id <= 0) {
            return 'Не указано видео';
        }
        if ($lessonId <= 0) {
            return 'Сначала выбери видео';
        }
        if ($quality === '') {
            return 'Укажите качество';
        }
        if ($filePath === '') {
            return 'Укажите файл';
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return 'Видео не найдено';
        }

        require_once dirname(__DIR__) . '/slide/_helpers.php';
        $filePath = TrainingModuleSlideHelper::normalizeWebPath($this->modx, $filePath);

        $this->setProperty('file_path', $filePath);
        $this->setProperty('lesson_id', $lessonId);
        $this->setProperty('module_id', (int)$lesson->get('module_id'));
        $this->setProperty('is_default', $this->boolValue($this->getProperty('is_default', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));

        return parent::beforeSet();
    }

    public function afterSave()
    {
        if ((int)$this->object->get('is_default') === 1) {
            $c = $this->modx->newQuery('TrainingModuleVideo');
            $c->where([
                'lesson_id' => (int)$this->object->get('lesson_id'),
                'id:!=' => (int)$this->object->get('id')
            ]);
            foreach ($this->modx->getCollection('TrainingModuleVideo', $c) as $item) {
                if ((int)$item->get('is_default') === 1) {
                    $item->set('is_default', 0);
                    $item->save();
                }
            }
        }

        return parent::afterSave();
    }
}

return 'TrainingModuleVideoUpdateProcessor';

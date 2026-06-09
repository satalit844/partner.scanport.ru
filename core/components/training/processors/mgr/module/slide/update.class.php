<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingModuleSlideUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingModuleSlide';
    public $objectType = 'training.module.slide';

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
        $image = trim((string)$this->getProperty('image'));
        $slideNo = (int)$this->getProperty('slide_no', 0);

        if ($id <= 0) {
            return 'Не указан ID слайда';
        }
        if ($lessonId <= 0) {
            return 'Сначала выбери видео';
        }
        if ($image === '') {
            return 'Выберите изображение слайда';
        }
        if ($slideNo <= 0) {
            return 'Укажите номер слайда';
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return 'Видео не найдено';
        }

        $this->setProperty('module_id', (int)$lesson->get('module_id'));
        $this->setProperty('lesson_id', $lessonId);
        $this->setProperty('image', TrainingModuleSlideHelper::normalizeWebPath($this->modx, $image));
        $this->setProperty('slide_no', $slideNo);
        $this->setProperty('timecode_ms', max(0, (int)$this->getProperty('timecode_ms', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));

        return parent::beforeSet();
    }
}

return 'TrainingModuleSlideUpdateProcessor';

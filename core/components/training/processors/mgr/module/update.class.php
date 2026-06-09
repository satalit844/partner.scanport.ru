<?php

class TrainingModuleUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingModule';
    public $objectType = 'training.module';

    public function checkPermissions()
    {
        return true;
    }

    protected function hasIncomingProperty($key)
    {
        return is_array($this->properties) && array_key_exists($key, $this->properties);
    }

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if ($id <= 0) {
            return 'Не указан ID модуля';
        }

        /** @var TrainingModule $object */
        $object = $this->modx->getObject($this->classKey, ['id' => $id]);
        if (!$object) {
            return 'Модуль не найден';
        }

        $this->setProperty(
            'course_id',
            $this->hasIncomingProperty('course_id') ? (int)$this->getProperty('course_id', 0) : (int)$object->get('course_id')
        );

        $this->setProperty(
            'is_active',
            $this->hasIncomingProperty('is_active') ? (int)$this->getProperty('is_active', 0) : (int)$object->get('is_active')
        );

        $this->setProperty(
            'is_required',
            $this->hasIncomingProperty('is_required') ? (int)$this->getProperty('is_required', 0) : (int)$object->get('is_required')
        );

        $this->setProperty(
            'source_video',
            $this->hasIncomingProperty('source_video')
                ? trim((string)$this->getProperty('source_video', ''))
                : (string)$object->get('source_video')
        );

        $this->setProperty(
            'source_presentation',
            $this->hasIncomingProperty('source_presentation')
                ? trim((string)$this->getProperty('source_presentation', ''))
                : (string)$object->get('source_presentation')
        );

        $this->setProperty(
            'presentation_pdf',
            $this->hasIncomingProperty('presentation_pdf')
                ? trim((string)$this->getProperty('presentation_pdf', ''))
                : (string)$object->get('presentation_pdf')
        );

        $this->setProperty(
            'slides_dir',
            $this->hasIncomingProperty('slides_dir')
                ? trim((string)$this->getProperty('slides_dir', ''))
                : (string)$object->get('slides_dir')
        );

        $videoStatus = $this->hasIncomingProperty('video_status')
            ? trim((string)$this->getProperty('video_status', ''))
            : (string)$object->get('video_status');
        if ($videoStatus === '') {
            $videoStatus = 'none';
        }
        $this->setProperty('video_status', $videoStatus);

        $presentationStatus = $this->hasIncomingProperty('presentation_status')
            ? trim((string)$this->getProperty('presentation_status', ''))
            : (string)$object->get('presentation_status');
        if ($presentationStatus === '') {
            $presentationStatus = 'none';
        }
        $this->setProperty('presentation_status', $presentationStatus);

        return parent::beforeSet();
    }

    public function beforeSave()
    {
        $this->object->set('updatedon', date('Y-m-d H:i:s'));
        return parent::beforeSave();
    }
}

return 'TrainingModuleUpdateProcessor';

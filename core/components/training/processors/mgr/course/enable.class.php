<?php

class TrainingCourseEnableProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingCourse';
    public $objectType = 'training.course';

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (!$id) {
            return 'Не указан ID курса';
        }

        $this->setProperty('is_active', 1);
        return parent::beforeSet();
    }
}

return 'TrainingCourseEnableProcessor';
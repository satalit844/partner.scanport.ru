<?php

class TrainingCourseDisableProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingCourse';
    public $objectType = 'training.course';

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        if (!$id) {
            return 'Не указан ID курса';
        }

        $this->setProperty('is_active', 0);
        return parent::beforeSet();
    }
}

return 'TrainingCourseDisableProcessor';
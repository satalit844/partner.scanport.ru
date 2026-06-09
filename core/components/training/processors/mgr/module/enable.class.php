<?php

class TrainingModuleEnableProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingModule';
    public $objectType = 'training.module';

    public function initialize()
    {
        $id = (int)$this->getProperty('id');
        if (!$id) {
            return 'Не указан ID модуля';
        }

        return parent::initialize();
    }

    public function beforeSet()
    {
        $this->setProperty('is_active', 1);
        return parent::beforeSet();
    }
}

return 'TrainingModuleEnableProcessor';
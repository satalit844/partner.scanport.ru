<?php
class TrainingModuleLessonUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingModuleLesson';
    public $objectType = 'training.module.lesson';

    public function checkPermissions(){return true;}

    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $title = trim((string)$this->getProperty('title'));
        if ($id <= 0) return 'Не указан урок';
        if ($title === '') return 'Укажи название';
        $sort = (int)$this->getProperty('sort_order');
        if ($sort <= 0) $sort = 1;
        $this->setProperty('title', $title);
        $this->setProperty('description', trim((string)$this->getProperty('description', '')));
        $this->setProperty('sort_order', $sort);
        $this->setProperty('source_presentation', trim((string)$this->getProperty('source_presentation', $this->object ? $this->object->get('source_presentation') : '')));
        $this->setProperty('is_default', $this->boolValue($this->getProperty('is_default', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));
        $this->setProperty('updatedon', date('Y-m-d H:i:s'));
        return parent::beforeSet();
    }

    public function afterSave()
    {
        if ((int)$this->object->get('is_default') === 1) {
            $c = $this->modx->newQuery('TrainingModuleLesson');
            $c->where(['module_id' => (int)$this->object->get('module_id'), 'id:!=' => (int)$this->object->get('id')]);
            foreach ($this->modx->getCollection('TrainingModuleLesson', $c) as $item) {
                $item->set('is_default', 0);
                $item->save();
            }
        }
        return parent::afterSave();
    }
}
return 'TrainingModuleLessonUpdateProcessor';

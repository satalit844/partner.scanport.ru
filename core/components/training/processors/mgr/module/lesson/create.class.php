<?php
class TrainingModuleLessonCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'TrainingModuleLesson';
    public $objectType = 'training.module.lesson';

    public function checkPermissions(){return true;}

    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}

    public function beforeSet()
    {
        $moduleId = (int)$this->getProperty('module_id');
        $title = trim((string)$this->getProperty('title'));
        if ($moduleId <= 0) return 'Не указан модуль';
        if ($title === '') $title = 'Новый урок';
        $sort = (int)$this->getProperty('sort_order');
        if ($sort <= 0) {
            $q = $this->modx->newQuery('TrainingModuleLesson');
            $q->where(['module_id' => $moduleId]);
            $q->sortby('sort_order', 'DESC');
            $q->limit(1);
            $last = $this->modx->getObject('TrainingModuleLesson', $q);
            $sort = $last ? ((int)$last->get('sort_order') + 1) : 1;
        }
        $now = date('Y-m-d H:i:s');
        $this->setProperty('module_id', $moduleId);
        $this->setProperty('title', $title);
        $this->setProperty('description', trim((string)$this->getProperty('description', '')));
        $this->setProperty('sort_order', $sort);
        $this->setProperty('is_default', $this->boolValue($this->getProperty('is_default', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));
        $this->setProperty('createdon', $now);
        $this->setProperty('updatedon', $now);
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
return 'TrainingModuleLessonCreateProcessor';

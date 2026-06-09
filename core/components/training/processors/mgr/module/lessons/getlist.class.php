<?php

require_once dirname(dirname(__DIR__)) . '/lesson/_video_helper.php';

class TrainingModuleLessonsGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingModuleLesson';
    public $objectType = 'training.module.lesson';
    public $defaultSortField = 'sort_order';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $moduleId = (int)$this->getProperty('module_id');
        if ($moduleId <= 0) {
            $c->where(['1 = 0']);
            return $c;
        }
        $query = trim((string)$this->getProperty('query', ''));
        $c->where(['module_id' => $moduleId]);
        if ($query !== '') {
            $c->where([
                'title:LIKE' => '%' . $query . '%',
                'OR:id:=' => (int)$query,
            ]);
        }
        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $a = $object->toArray();
        $a['videos_count'] = TrainingLessonVideoHelper::countLessonVideos($this->modx, (int)$a['id']);
        $a['slides_count'] = TrainingLessonVideoHelper::countLessonSlides($this->modx, (int)$a['id']);
        $a['is_default'] = (int)!empty($a['is_default']);
        $a['is_active'] = (int)!empty($a['is_active']);
        return $a;
    }
}
return 'TrainingModuleLessonsGetListProcessor';

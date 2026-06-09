<?php

class TrainingModuleVideosGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingModuleVideo';
    public $objectType = 'training.module.video';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            $c->where(['1 = 0']);
            return $c;
        }

        $query = trim((string)$this->getProperty('query', ''));
        $c->where(['lesson_id' => $lessonId]);

        if ($query !== '') {
            $c->where([
                'quality:LIKE' => '%' . $query . '%',
                'OR:file_path:LIKE' => '%' . $query . '%',
                'OR:mime:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['is_default'] = (int)!empty($array['is_default']);
        $array['is_active'] = (int)!empty($array['is_active']);
        return $array;
    }
}

return 'TrainingModuleVideosGetListProcessor';

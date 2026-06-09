<?php

class TrainingModuleSlidesGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingModuleSlide';
    public $objectType = 'training.module.slide';
    public $defaultSortField = 'slide_no';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $moduleId = (int)$this->getProperty('module_id');
        $lessonId = (int)$this->getProperty('lesson_id');
        if (!$moduleId && !$lessonId) {
            $c->where(['1 = 0']);
            return $c;
        }

        $query = trim($this->getProperty('query', ''));

        if ($lessonId > 0) {
            $c->where(['lesson_id' => $lessonId]);
        } else {
            $c->where(['module_id' => $moduleId]);
        }

        if ($query !== '') {
            $c->where([
                'slide_no:=' => (int)$query,
                'OR:image:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['is_active'] = (int)!empty($array['is_active']);
        $array['timecode_human'] = $this->formatMilliseconds((int)$array['timecode_ms']);

        return $array;
    }

    protected function formatMilliseconds($milliseconds)
    {
        $seconds = (int)floor($milliseconds / 1000);
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $seconds = $seconds % 60;

        if ($hours > 0) {
            return sprintf('%d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%d:%02d', $minutes, $seconds);
    }
}

return 'TrainingModuleSlidesGetListProcessor';

<?php

class TrainingCourseGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingCourse';
    public $objectType = 'training.course';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'ASC';

    /** @var Training $training */
    protected $training;

    public function initialize()
    {
        $corePath = $this->modx->getOption(
            'training.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/training/'
        );

        require_once $corePath . 'model/training/training.class.php';
        $this->training = new Training($this->modx);

        return parent::initialize();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = trim($this->getProperty('query', ''));

        $c->leftJoin('modResource', 'Resource', 'Resource.id = TrainingCourse.resource_id');

        $c->select($this->modx->getSelectColumns('TrainingCourse', 'TrainingCourse'));
        $c->select([
            'Resource.pagetitle AS pagetitle',
            'Resource.uri AS uri',
            'Resource.published AS resource_published',
            'Resource.deleted AS resource_deleted',
        ]);

        if ($query !== '') {
            $c->where([
                'TrainingCourse.id:=' => (int)$query,
                'OR:TrainingCourse.resource_id:=' => (int)$query,
                'OR:Resource.pagetitle:LIKE' => '%' . $query . '%',
                'OR:Resource.uri:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
    
        $array['pagetitle'] = !empty($array['pagetitle']) ? $array['pagetitle'] : '—';
        $array['uri'] = !empty($array['uri']) ? $array['uri'] : '—';
    
        $array['is_active'] = (int)!empty($array['is_active']);
        $array['resource_published'] = (int)!empty($array['resource_published']);
    
        $array['modules_count'] = (int)$this->modx->getCount('TrainingModule', [
            'course_id' => $array['id'],
        ]);
    
        return $array;
    }
}

return 'TrainingCourseGetListProcessor';
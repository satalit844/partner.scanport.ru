<?php

require_once dirname(dirname(__DIR__)) . '/lesson/_video_helper.php';

class TrainingCourseModulesGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingModule';
    public $objectType = 'training.module';
    public $defaultSortField = 'Resource.menuindex';
    public $defaultSortDirection = 'ASC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $courseId = (int)$this->getProperty('course_id');
        if ($courseId <= 0) {
            $c->where(['1 = 0']);
            return $c;
        }

        $query = trim((string)$this->getProperty('query', ''));

        $c->leftJoin('modResource', 'Resource', 'Resource.id = TrainingModule.resource_id');
        $c->select($this->modx->getSelectColumns('TrainingModule', 'TrainingModule'));
        $c->select([
            'Resource.pagetitle AS title',
            'Resource.menuindex AS menuindex',
            'Resource.published AS published',
        ]);
        $c->where(['TrainingModule.course_id' => $courseId]);

        if ($query !== '') {
            $c->where([
                'TrainingModule.id:=' => (int)$query,
                'OR:TrainingModule.resource_id:=' => (int)$query,
                'OR:Resource.pagetitle:LIKE' => '%' . $query . '%',
            ]);
        }

        return $c;
    }

    protected function getModuleLessons($moduleId)
    {
        $criteria = $this->modx->newQuery('TrainingModuleLesson');
        $criteria->where(['module_id' => (int)$moduleId]);
        $criteria->sortby('sort_order', 'ASC');
        return $this->modx->getCollection('TrainingModuleLesson', $criteria);
    }

    protected function countModuleLessons($moduleId)
    {
        return (int)$this->modx->getCount('TrainingModuleLesson', ['module_id' => (int)$moduleId]);
    }

    protected function countModuleVideos($moduleId)
    {
        $count = 0;
        foreach ($this->getModuleLessons($moduleId) as $lesson) {
            $count += TrainingLessonVideoHelper::countLessonVideos($this->modx, (int)$lesson->get('id'));
        }
        return (int)$count;
    }

    protected function countModuleSlides($moduleId)
    {
        $count = 0;
        foreach ($this->getModuleLessons($moduleId) as $lesson) {
            $count += TrainingLessonVideoHelper::countLessonSlides($this->modx, (int)$lesson->get('id'));
        }
        return (int)$count;
    }

    public function prepareRow(xPDOObject $object)
    {
        $row = $object->toArray();
        $row['title'] = !empty($row['title']) ? $row['title'] : '—';
        $row['published'] = (int)!empty($row['published']);
        $row['is_active'] = (int)!empty($row['is_active']);
        $row['lessons_count'] = $this->countModuleLessons((int)$row['id']);
        $row['videos_count'] = $this->countModuleVideos((int)$row['id']);
        $row['slides_count'] = $this->countModuleSlides((int)$row['id']);
        return $row;
    }
}
return 'TrainingCourseModulesGetListProcessor';

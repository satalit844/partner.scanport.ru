<?php

require_once dirname(dirname(__DIR__)) . '/lesson/_video_helper.php';

class TrainingModuleLessonGetProcessor extends modObjectGetProcessor
{
    public $classKey = 'TrainingModuleLesson';
    public $objectType = 'training.module.lesson';

    public function checkPermissions(){return true;}

    public function cleanup()
    {
        $row = $this->object->toArray();
        $row['videos_count'] = TrainingLessonVideoHelper::countLessonVideos($this->modx, (int)$row['id']);
        $row['slides_count'] = TrainingLessonVideoHelper::countLessonSlides($this->modx, (int)$row['id']);
        $row['is_default'] = (int)!empty($row['is_default']);
        $row['is_active'] = (int)!empty($row['is_active']);
        $row['id_label'] = (int)$row['id'];
        $module = $this->modx->getObject('TrainingModule', ['id' => (int)$row['module_id']]);
        $row['module_title'] = '—';
        $row['course_id'] = 0;
        if ($module) {
            $row['course_id'] = (int)$module->get('course_id');
            $resource = $this->modx->getObject('modResource', ['id' => (int)$module->get('resource_id')]);
            if ($resource) {
                $row['module_title'] = (string)$resource->get('pagetitle');
            }
        }
        return $this->success('', $row);
    }
}
return 'TrainingModuleLessonGetProcessor';

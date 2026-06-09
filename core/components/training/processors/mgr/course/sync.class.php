<?php

class TrainingCourseSyncProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }
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

    public function process()
    {
        $coursesParentId = (int)$this->modx->getOption('training.courses_parent_id', null, 0);
        if ($coursesParentId <= 0) {
            return $this->failure($this->modx->lexicon('training.courses_parent_id_not_set'));
        }

        $now = date('Y-m-d H:i:s');
        $coursesCount = 0;
        $modulesCount = 0;

        // Все текущие курсы временно деактивируем, потом актуальные включим обратно
        $this->modx->updateCollection('TrainingCourse', [
            'is_active' => 0,
            'updatedon' => $now,
        ], []);

        $c = $this->modx->newQuery('modResource');
        $c->where([
            'parent' => $coursesParentId,
            'deleted' => 0,
        ]);
        $c->sortby('menuindex', 'ASC');

        /** @var modResource[] $courseResources */
        $courseResources = $this->modx->getCollection('modResource', $c);

        foreach ($courseResources as $courseResource) {
            /** @var TrainingCourse $course */
            $course = $this->modx->getObject('TrainingCourse', [
                'resource_id' => $courseResource->get('id'),
            ]);

            if (!$course) {
                $course = $this->modx->newObject('TrainingCourse');
                $course->set('resource_id', $courseResource->get('id'));
                $course->set('is_sequential', 1);
                $course->set('createdon', $now);
            }

            $course->set('is_active', 1);
            $course->set('updatedon', $now);
            $course->save();

            $courseId = (int)$course->get('id');
            $coursesCount++;

            // Модули этого курса тоже сначала деактивируем
            $this->modx->updateCollection('TrainingModule', [
                'is_active' => 0,
                'updatedon' => $now,
            ], [
                'course_id' => $courseId,
            ]);

            $mc = $this->modx->newQuery('modResource');
            $mc->where([
                'parent' => $courseResource->get('id'),
                'deleted' => 0,
            ]);
            $mc->sortby('menuindex', 'ASC');

            /** @var modResource[] $moduleResources */
            $moduleResources = $this->modx->getCollection('modResource', $mc);

            foreach ($moduleResources as $moduleResource) {
                /** @var TrainingModule $module */
                $module = $this->modx->getObject('TrainingModule', [
                    'resource_id' => $moduleResource->get('id'),
                ]);

                if (!$module) {
                    $module = $this->modx->newObject('TrainingModule');
                    $module->set('resource_id', $moduleResource->get('id'));
                    $module->set('course_id', $courseId);
                    $module->set('is_required', 1);
                    $module->set('duration_seconds', 0);
                    $module->set('video_status', 'none');
                    $module->set('presentation_status', 'none');
                    $module->set('source_video', '');
                    $module->set('source_presentation', '');
                    $module->set('presentation_pdf', '');
                    $module->set('slides_dir', '');
                    $module->set('createdon', $now);
                }

                $module->set('course_id', $courseId);
                $module->set('is_active', 1);
                $module->set('updatedon', $now);
                $module->save();

                $modulesCount++;
            }
        }

        return $this->success('', [
            'courses' => $coursesCount,
            'modules' => $modulesCount,
        ]);
    }
}

return 'TrainingCourseSyncProcessor';
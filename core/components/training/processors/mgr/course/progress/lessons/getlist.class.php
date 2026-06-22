<?php

require_once dirname(__DIR__) . '/_helpers.php';

class TrainingCourseProgressLessonsGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        $moduleId = (int)$this->getProperty('module_id', 0);

        if ($courseId <= 0 || $moduleId <= 0) {
            return trainingProgressCmpListResponse($this, array(), 0);
        }

        try {
            $training = new Training($this->modx);
            $progressService = new TrainingProgressService($this->modx, $training);

            $belongsToCourse = false;
            foreach ($progressService->getCourseModules($courseId, false, false) as $module) {
                if ((int)$module->get('id') === $moduleId) {
                    $belongsToCourse = true;
                    break;
                }
            }

            if (!$belongsToCourse) {
                return $this->failure('Выбранный модуль не относится к этому курсу.');
            }

            $items = $progressService->getModuleLessons($moduleId, true, false);
            $rows = array();

            /** @var TrainingModuleLesson $item */
            foreach ($items as $item) {
                $title = trim((string)$item->get('title'));

                $rows[] = array(
                    'id' => (int)$item->get('id'),
                    'module_id' => (int)$item->get('module_id'),
                    'sort_order' => (int)$item->get('sort_order'),
                    'is_active' => (int)$item->get('is_active'),
                    'duration_seconds' => (int)$item->get('duration_seconds'),
                    'display_name' => $title !== '' ? $title : ('Урок #' . (int)$item->get('id')),
                );
            }

            trainingProgressCmpLog($this->modx, 'lessons_getlist', array(
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'found' => count($rows),
                'source' => 'TrainingProgressService::getModuleLessons',
            ));

            return trainingProgressCmpListResponse($this, $rows, count($rows));
        } catch (Throwable $e) {
            trainingProgressCmpLog($this->modx, 'lessons_getlist_error', array(
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'message' => $e->getMessage(),
            ));

            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressLessonsGetListProcessor';

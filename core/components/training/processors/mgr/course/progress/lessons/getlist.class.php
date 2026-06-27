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
        $userId = (int)$this->getProperty('user_id', 0);
        $excludeCompleted = (int)$this->getProperty('exclude_completed', 0) === 1;

        if ($courseId <= 0 || $moduleId <= 0) {
            return trainingProgressCmpListResponse($this, array(), 0);
        }

        try {
            $source = 'TrainingProgressService::getModuleLessons';
            $rows = array();

            if ($userId > 0) {
                $details = trainingProgressCmpGetService($this->modx)->getUserProgressDetails($courseId, $userId);
                $targetModule = null;

                foreach ((array)$details['modules'] as $module) {
                    if ((int)$module['id'] === $moduleId) {
                        $targetModule = $module;
                        break;
                    }
                }

                if (!$targetModule) {
                    return $this->failure('Выбранный модуль не относится к этому курсу.');
                }

                $rows = isset($targetModule['lessons']) ? (array)$targetModule['lessons'] : array();
                $source = 'TrainingProgressAssignmentService::getUserProgressDetails';
            } else {
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
                        'status' => 'not_started',
                        'progress_percent' => 0,
                        'completed' => 0,
                        'is_current' => 0,
                    );
                }
            }

            $rows = array_values(array_filter($rows, function ($row) use ($excludeCompleted) {
                if ((int)$row['is_active'] !== 1) {
                    return false;
                }

                if ($excludeCompleted && !empty($row['completed'])) {
                    return false;
                }

                return true;
            }));

            trainingProgressCmpLog($this->modx, 'lessons_getlist', array(
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'exclude_completed' => $excludeCompleted ? 1 : 0,
                'found' => count($rows),
                'source' => $source,
            ));

            return trainingProgressCmpListResponse($this, $rows, count($rows));
        } catch (Throwable $e) {
            trainingProgressCmpLog($this->modx, 'lessons_getlist_error', array(
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ));

            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressLessonsGetListProcessor';

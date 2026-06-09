<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingCourseAccessRemoveProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $ids = $this->collectIds();
        if (empty($ids)) {
            return $this->failure('Не выбраны записи доступа для удаления');
        }

        $removed = 0;
        $courseIds = [];

        foreach ($ids as $id) {
            /** @var TrainingCourseAccess $item */
            $item = $this->modx->getObject('TrainingCourseAccess', ['id' => $id]);
            if ($item) {
                $courseId = (int)$item->get('course_id');
                if ($courseId > 0) {
                    $courseIds[$courseId] = $courseId;
                }

                if ($item->remove()) {
                    $removed++;
                }
            }
        }

        if (!empty($courseIds)) {
            $service = TrainingCourseAccessHelper::getProgressService($this->modx);
            foreach ($courseIds as $courseId) {
                $service->syncUserCourses($courseId);
            }
        }

        return $this->success('Доступы удалены', ['removed' => $removed]);
    }

    protected function collectIds()
    {
        $raw = $this->getProperty('ids', $this->getProperty('id', ''));
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }

        $raw = trim((string)$raw);
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('intval', array_map('trim', explode(',', $raw)))));
    }
}

return 'TrainingCourseAccessRemoveProcessor';
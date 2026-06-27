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

        /* training-license-core-v1 */
        $courseAccessTable = trim((string)$this->modx->getTableName('TrainingCourseAccess'), '`');
        $licenseAssignmentsTable = preg_replace('/_course_access$/', '_license_assignments', $courseAccessTable);

        if ($licenseAssignmentsTable && $licenseAssignmentsTable !== $courseAccessTable) {
            $safeAssignmentsTable = str_replace('`', '``', $licenseAssignmentsTable);

            foreach ($ids as $licenseCheckId) {
                /** @var TrainingCourseAccess|null $licenseCheckAccess */
                $licenseCheckAccess = $this->modx->getObject('TrainingCourseAccess', ['id' => (int)$licenseCheckId]);

                if (
                    !$licenseCheckAccess
                    || (string)$licenseCheckAccess->get('access_role') !== 'director'
                    || (string)$licenseCheckAccess->get('principal_type') !== 'user'
                ) {
                    continue;
                }

                $usedStmt = $this->modx->prepare(
                    'SELECT COUNT(*) FROM `' . $safeAssignmentsTable . '` '
                    . 'WHERE `director_access_id` = :director_access_id '
                    . 'AND `state` IN ("reserved","consumed")'
                );

                $used = 0;
                if ($usedStmt && $usedStmt->execute([
                    ':director_access_id' => (int)$licenseCheckAccess->get('id'),
                ])) {
                    $used = (int)$usedStmt->fetchColumn();
                }

                if ($used > 0) {
                    return $this->failure(
                        'Нельзя удалить директора: у него есть занятые или потраченные лицензии (' . $used . ')'
                    );
                }
            }
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
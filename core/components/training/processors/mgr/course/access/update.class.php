<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingCourseAccessUpdateProcessor extends modObjectUpdateProcessor
{
    public $classKey = 'TrainingCourseAccess';
    public $objectType = 'training.course.access';

    /** @var TrainingCourseAccess|null */
    protected $beforeObject = null;

    public function checkPermissions()
    {
        return true;
    }

    public function initialize()
    {
        $id = (int)$this->getProperty('id');
        if ($id > 0) {
            $this->beforeObject = $this->modx->getObject('TrainingCourseAccess', ['id' => $id]);
        }

        return parent::initialize();
    }

    public function beforeSet()
    {
        $id = (int)$this->getProperty('id');
        $courseId = (int)$this->getProperty('course_id');
        $principalType = trim((string)$this->getProperty('principal_type', 'user'));
        $principalId = (int)$this->getProperty('principal_id');
        $accessRole = trim((string)$this->getProperty('access_role', 'employee'));
        $isActive = (int)((string)$this->getProperty('is_active', '1') === '0' ? 0 : 1);
        /* training-license-access-ui-v1 */
        $licensesTotal = max(0, (int)$this->getProperty('licenses_total', 0));
        $licensesEnabled = ($accessRole === 'director' && $principalType === 'user' && $licensesTotal > 0) ? 1 : 0;

        $accessTable = trim((string)$this->modx->getTableName('TrainingCourseAccess'), '`');
        $licenseAssignmentsTable = preg_replace('/_course_access$/', '_license_assignments', $accessTable);
        $usedLicenses = 0;

        if ($id > 0 && $licenseAssignmentsTable && $licenseAssignmentsTable !== $accessTable) {
            $safeAssignmentsTable = str_replace('`', '``', $licenseAssignmentsTable);
            $usedStmt = $this->modx->prepare(
                'SELECT COUNT(*) FROM `' . $safeAssignmentsTable . '` '
                . 'WHERE `director_access_id` = :director_access_id '
                . 'AND `state` IN ("reserved", "consumed")'
            );

            if ($usedStmt && $usedStmt->execute(array(':director_access_id' => $id))) {
                $usedLicenses = (int)$usedStmt->fetchColumn();
            }
        }

        if ($accessRole === 'director' && $principalType === 'user') {
            if ($licensesTotal < $usedLicenses) {
                return 'Нельзя указать меньше лицензий, чем уже занято или потрачено: ' . $usedLicenses;
            }
        } elseif ($usedLicenses > 0) {
            return 'Нельзя изменить роль или тип директора: на нём есть занятые/потраченные лицензии (' . $usedLicenses . ')';
        } else {
            $licensesTotal = 0;
            $licensesEnabled = 0;
        }

        $this->setProperty('licenses_total', $licensesTotal);
        $this->setProperty('licenses_enabled', $licensesEnabled);

        if ($id <= 0) {
            return 'Не указан ID доступа';
        }
        if ($courseId <= 0) {
            return 'Не указан курс';
        }
        if (!in_array($principalType, ['user', 'group'], true)) {
            return 'Некорректный тип доступа';
        }
        if ($principalId <= 0) {
            return 'Не выбран пользователь или группа';
        }
        if (!in_array($accessRole, ['employee', 'director'], true)) {
            return 'Некорректное значение прав';
        }

        $c = $this->modx->newQuery('TrainingCourseAccess');
        $c->where([
            'course_id' => $courseId,
            'principal_type' => $principalType,
            'principal_id' => $principalId,
            'id:!=' => $id,
        ]);
        if ($this->modx->getCount('TrainingCourseAccess', $c) > 0) {
            return 'Такой доступ уже существует';
        }

        $this->setProperty('course_id', $courseId);
        $this->setProperty('principal_type', $principalType);
        $this->setProperty('principal_id', $principalId);
        $this->setProperty('access_role', $accessRole);
        $this->setProperty('is_active', $isActive);
        $this->setProperty('active_from', TrainingCourseAccessHelper::normalizeDateTime($this->getProperty('active_from')));
        $this->setProperty('active_to', TrainingCourseAccessHelper::normalizeDateTime($this->getProperty('active_to')));

        return parent::beforeSet();
    }

    public function afterSave()
    {
        $service = TrainingCourseAccessHelper::getProgressService($this->modx);
        $courseIds = [];

        if ($this->beforeObject) {
            $oldCourseId = (int)$this->beforeObject->get('course_id');
            if ($oldCourseId > 0) {
                $courseIds[$oldCourseId] = $oldCourseId;
            }
        }

        $newCourseId = (int)$this->object->get('course_id');
        if ($newCourseId > 0) {
            $courseIds[$newCourseId] = $newCourseId;
        }

        foreach ($courseIds as $courseId) {
            $service->syncUserCourses($courseId);
        }

        return parent::afterSave();
    }
}

return 'TrainingCourseAccessUpdateProcessor';
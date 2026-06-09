<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingCourseAccessCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'TrainingCourseAccess';
    public $objectType = 'training.course.access';

    public function checkPermissions()
    {
        return true;
    }

    public function beforeSet()
    {
        $courseId = (int)$this->getProperty('course_id');
        $principalType = trim((string)$this->getProperty('principal_type', 'user'));
        $principalId = (int)$this->getProperty('principal_id');
        $accessRole = trim((string)$this->getProperty('access_role', 'employee'));
        $isActive = (int)((string)$this->getProperty('is_active', '1') === '0' ? 0 : 1);

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

        $exists = $this->modx->getObject('TrainingCourseAccess', [
            'course_id' => $courseId,
            'principal_type' => $principalType,
            'principal_id' => $principalId,
        ]);
        if ($exists) {
            return 'Такой доступ уже существует';
        }

        $this->setProperty('course_id', $courseId);
        $this->setProperty('principal_type', $principalType);
        $this->setProperty('principal_id', $principalId);
        $this->setProperty('access_role', $accessRole);
        $this->setProperty('is_active', $isActive);
        $this->setProperty('active_from', TrainingCourseAccessHelper::normalizeDateTime($this->getProperty('active_from')));
        $this->setProperty('active_to', TrainingCourseAccessHelper::normalizeDateTime($this->getProperty('active_to')));
        $this->setProperty('assigned_by', $this->modx->user ? (int)$this->modx->user->get('id') : 0);
        $this->setProperty('createdon', date('Y-m-d H:i:s'));

        return parent::beforeSet();
    }

    public function afterSave()
    {
        $courseId = (int)$this->object->get('course_id');
        $principalType = (string)$this->object->get('principal_type');
        $principalId = (int)$this->object->get('principal_id');

        $service = TrainingCourseAccessHelper::getProgressService($this->modx);

        if ($courseId > 0) {
            if ($principalType === 'user' && $principalId > 0) {
                $service->syncUserCourseForUser($courseId, $principalId);
            } else {
                $service->syncUserCourses($courseId);
            }
        }

        return parent::afterSave();
    }
}

return 'TrainingCourseAccessCreateProcessor';
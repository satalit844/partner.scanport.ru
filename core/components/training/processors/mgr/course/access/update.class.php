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
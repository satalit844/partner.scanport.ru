<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingCourseAccessGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingCourseAccess';
    public $objectType = 'training.course.access';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';

    /** @var TrainingProgressService */
    protected $progressService;

    public function initialize()
    {
        $this->progressService = TrainingCourseAccessHelper::getProgressService($this->modx);
        return parent::initialize();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $courseId = (int)$this->getProperty('course_id');
        $query = trim((string)$this->getProperty('query', ''));

        if ($courseId > 0) {
            $c->where(['course_id' => $courseId]);
        }

        if ($query !== '') {
            $ids = [];
            if (ctype_digit($query)) {
                $ids[] = (int)$query;
            }

            $matchedUserIds = $this->findUserIds($query);
            $matchedGroupIds = $this->findGroupIds($query);

            $conditions = [];
            if (!empty($ids)) {
                $conditions[] = ['id:IN' => $ids];
            }
            if (!empty($matchedUserIds)) {
                $conditions[] = [
                    'principal_type' => 'user',
                    'principal_id:IN' => $matchedUserIds,
                ];
            }
            if (!empty($matchedGroupIds)) {
                $conditions[] = [
                    'principal_type' => 'group',
                    'principal_id:IN' => $matchedGroupIds,
                ];
            }

            if (!empty($conditions)) {
                $c->where($conditions, xPDOQuery::SQL_OR);
            } else {
                $c->where(['id' => 0]);
            }
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $principal = TrainingCourseAccessHelper::getPrincipalData(
            $this->modx,
            $array['principal_type'],
            $array['principal_id']
        );

        $array = array_merge($array, $principal);
        $array['is_active'] = (int)!empty($array['is_active']);
        $array['is_active_now'] = $this->progressService->isAccessCurrentlyActive($array) ? 1 : 0;
        $array['principal_type_label'] = $array['principal_type'] === 'group' ? 'Группа' : 'Пользователь';
        $array['access_role_label'] = $array['access_role'] === 'director' ? 'Директор' : 'Сотрудник';
        $array['assigned_by_label'] = TrainingCourseAccessHelper::getAssignedByLabel($this->modx, $array['assigned_by']);
        $array['active_from'] = !empty($array['active_from']) ? $array['active_from'] : '';
        $array['active_to'] = !empty($array['active_to']) ? $array['active_to'] : '';

        return $array;
    }

    protected function findUserIds($query)
    {
        $ids = [];
        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile', 'Profile.internalKey = modUser.id');
        $c->select(['modUser.id']);
        $c->where([
            'modUser.username:LIKE' => '%' . $query . '%',
            'OR:Profile.fullname:LIKE' => '%' . $query . '%',
            'OR:Profile.email:LIKE' => '%' . $query . '%',
        ]);
        $c->limit(50);
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = (int)$row['id'];
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }

    protected function findGroupIds($query)
    {
        $ids = [];
        $c = $this->modx->newQuery('modUserGroup');
        $c->select(['modUserGroup.id']);
        $c->where([
            'name:LIKE' => '%' . $query . '%',
        ]);
        $c->limit(50);
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = (int)$row['id'];
            }
        }
        return array_values(array_unique(array_filter($ids)));
    }
}

return 'TrainingCourseAccessGetListProcessor';

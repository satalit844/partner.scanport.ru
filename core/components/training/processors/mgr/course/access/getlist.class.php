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
        /* training-license-access-ui-v1 */
        $array['licenses_total'] = isset($array['licenses_total']) ? (int)$array['licenses_total'] : 0;
        $array['licenses_enabled'] = isset($array['licenses_enabled']) ? (int)$array['licenses_enabled'] : 0;
        $array['licenses_reserved'] = 0;
        $array['licenses_consumed'] = 0;
        $array['licenses_free'] = 0;
        $array['licenses_label'] = '—';

        if (
            (string)$array['access_role'] === 'director'
            && (string)$array['principal_type'] === 'user'
            && (int)$array['licenses_enabled'] === 1
        ) {
            $accessTable = trim((string)$this->modx->getTableName('TrainingCourseAccess'), '`');
            $licenseAssignmentsTable = preg_replace('/_course_access$/', '_license_assignments', $accessTable);

            if ($licenseAssignmentsTable && $licenseAssignmentsTable !== $accessTable) {
                $safeAssignmentsTable = str_replace('`', '``', $licenseAssignmentsTable);
                $licensesStmt = $this->modx->prepare(
                    'SELECT '
                    . 'SUM(CASE WHEN `state` = "reserved" THEN 1 ELSE 0 END) AS `reserved_count`, '
                    . 'SUM(CASE WHEN `state` = "consumed" THEN 1 ELSE 0 END) AS `consumed_count` '
                    . 'FROM `' . $safeAssignmentsTable . '` '
                    . 'WHERE `director_access_id` = :director_access_id'
                );

                if ($licensesStmt && $licensesStmt->execute(array(':director_access_id' => (int)$array['id']))) {
                    $licensesRow = (array)$licensesStmt->fetch(PDO::FETCH_ASSOC);
                    $array['licenses_reserved'] = (int)(isset($licensesRow['reserved_count']) ? $licensesRow['reserved_count'] : 0);
                    $array['licenses_consumed'] = (int)(isset($licensesRow['consumed_count']) ? $licensesRow['consumed_count'] : 0);
                }
            }

            $array['licenses_free'] = max(
                0,
                (int)$array['licenses_total']
                - (int)$array['licenses_reserved']
                - (int)$array['licenses_consumed']
            );

            $array['licenses_label'] = (int)$array['licenses_total']
                . ' всего · ' . (int)$array['licenses_free'] . ' свободно';
        }
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

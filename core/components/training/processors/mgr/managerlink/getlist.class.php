<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingManagerLinkGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingUserManagerLink';
    public $objectType = 'training.manager.link';
    public $defaultSortField = 'id';
    public $defaultSortDirection = 'DESC';

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $query = trim((string)$this->getProperty('query', ''));
        if ($query === '') {
            return $c;
        }

        $ids = [];
        $uc = $this->modx->newQuery('modUser');
        $uc->leftJoin('modUserProfile', 'Profile', 'Profile.internalKey = modUser.id');
        $uc->select(['modUser.id']);
        $uc->where([
            'modUser.username:LIKE' => '%' . $query . '%',
            'OR:Profile.fullname:LIKE' => '%' . $query . '%',
            'OR:Profile.email:LIKE' => '%' . $query . '%',
        ]);
        $uc->limit(100);
        if ($uc->prepare() && $uc->stmt->execute()) {
            while ($row = $uc->stmt->fetch(PDO::FETCH_ASSOC)) {
                $ids[] = (int)$row['id'];
            }
        }
        $ids = array_values(array_unique(array_filter($ids)));

        if ($ids) {
            $c->where([
                'manager_user_id:IN' => $ids,
                'OR:employee_user_id:IN' => $ids,
            ]);
        } else {
            $c->where(['id' => 0]);
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $array = $object->toArray();
        $array['manager_label'] = TrainingManagerLinkHelper::getUserLabel($this->modx, $array['manager_user_id']);
        $array['employee_label'] = TrainingManagerLinkHelper::getUserLabel($this->modx, $array['employee_user_id']);
        $array['createdby_label'] = TrainingManagerLinkHelper::getUserLabel($this->modx, $array['createdby']);
        $array['is_active'] = (int)!empty($array['is_active']);
        $array['createdon'] = !empty($array['createdon']) ? $array['createdon'] : '';
        return $array;
    }
}

return 'TrainingManagerLinkGetListProcessor';

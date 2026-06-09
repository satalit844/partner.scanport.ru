<?php

class TrainingCourseAccessGroupsProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $query = trim((string)$this->getProperty('query', ''));
        $limit = max(1, (int)$this->getProperty('limit', 20));
        $start = max(0, (int)$this->getProperty('start', 0));

        $c = $this->modx->newQuery('modUserGroup');
        $c->select([
            'modUserGroup.id AS id',
            'modUserGroup.name AS name',
        ]);
        if ($query !== '') {
            $c->where([
                'name:LIKE' => '%' . $query . '%',
            ]);
        }

        $count = $this->modx->getCount('modUserGroup', $c);
        $c->sortby('modUserGroup.name', 'ASC');
        $c->limit($limit, $start);

        $results = [];
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $display = '#' . $row['id'] . ' ' . $row['name'];
                $results[] = [
                    'id' => (int)$row['id'],
                    'name' => (string)$row['name'],
                    'display' => $display,
                ];
            }
        }

        return $this->outputArray($results, $count);
    }
}

return 'TrainingCourseAccessGroupsProcessor';

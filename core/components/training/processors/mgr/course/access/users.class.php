<?php

class TrainingCourseAccessUsersProcessor extends modProcessor
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

        $c = $this->modx->newQuery('modUser');
        $c->leftJoin('modUserProfile', 'Profile', 'Profile.internalKey = modUser.id');
        $c->select([
            'modUser.id AS id',
            'modUser.username AS username',
            'Profile.fullname AS fullname',
            'Profile.email AS email',
        ]);

        if ($query !== '') {
            $c->where([
                'modUser.username:LIKE' => '%' . $query . '%',
                'OR:Profile.fullname:LIKE' => '%' . $query . '%',
                'OR:Profile.email:LIKE' => '%' . $query . '%',
            ]);
        }

        $count = $this->modx->getCount('modUser', $c);
        $c->sortby('modUser.id', 'DESC');
        $c->limit($limit, $start);

        $results = [];
        if ($c->prepare() && $c->stmt->execute()) {
            while ($row = $c->stmt->fetch(PDO::FETCH_ASSOC)) {
                $fullname = trim((string)$row['fullname']);
                $email = trim((string)$row['email']);
                $username = trim((string)$row['username']);
                $display = '#' . $row['id'] . ' ' . $username;
                if ($fullname !== '') {
                    $display .= ' (' . $fullname . ')';
                }
                if ($email !== '') {
                    $display .= ' [' . $email . ']';
                }
                $results[] = [
                    'id' => (int)$row['id'],
                    'name' => $display,
                    'username' => $username,
                    'fullname' => $fullname,
                    'email' => $email,
                    'display' => $display,
                ];
            }
        }

        return $this->outputArray($results, $count);
    }
}

return 'TrainingCourseAccessUsersProcessor';

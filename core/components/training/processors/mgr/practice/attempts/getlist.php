<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrAttemptsGetListProcessor extends modProcessor
{
    public function process()
    {
        $attemptsTable = trainingPracticeMgrTable($this->modx, 'training_practice_attempts');
        $practicesTable = trainingPracticeMgrTable($this->modx, 'training_practices');
        $coursesTable = trainingPracticeMgrTable($this->modx, 'training_courses');
        $modulesTable = trainingPracticeMgrTable($this->modx, 'training_modules');
        $usersTable = trainingPracticeMgrOptionalTable($this->modx, 'users');
        $attributesTable = trainingPracticeMgrOptionalTable($this->modx, 'user_attributes');
        $contentTable = trainingPracticeMgrOptionalTable($this->modx, 'site_content');

        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $query = trim((string)$this->getProperty('query', ''));
        $practiceId = (int)$this->getProperty('practice_id', 0);
        $status = trim((string)$this->getProperty('status', ''));

        $joins = array(
            "LEFT JOIN {$practicesTable} p ON p.`id` = a.`practice_id`",
            "LEFT JOIN {$coursesTable} tc ON tc.`id` = a.`course_id`",
            "LEFT JOIN {$modulesTable} tm ON tm.`id` = a.`module_id`",
        );
        $selectExtra = array(
            "COALESCE(NULLIF(p.`title`, ''), NULLIF(a.`title`, ''), CONCAT('Практика #', a.`practice_id`)) AS `practice_title`",
            "'' AS `username`",
            "'' AS `email`",
            "'' AS `fullname`",
            "CONCAT('Курс #', a.`course_id`) AS `course_title`",
            "CONCAT('Модуль #', a.`module_id`) AS `module_title`",
        );
        $queryParts = array("p.`title` LIKE :query", "a.`title` LIKE :query");
        $sortMap = array(
            'id' => 'a.`id`',
            'practice_title' => 'p.`title`',
            'status' => 'a.`status`',
            'status_text' => 'a.`status`',
            'attempt_num' => 'a.`attempt_num`',
            'attempt_no' => 'a.`attempt_no`',
            'submittedon' => 'a.`submittedon`',
            'submittedon_formatted' => 'a.`submittedon`',
            'createdon' => 'a.`createdon`',
            'createdon_formatted' => 'a.`createdon`',
            'reviewedon' => 'a.`reviewedon`',
            'reviewedon_formatted' => 'a.`reviewedon`',
            'course_display' => 'a.`course_id`',
            'module_display' => 'a.`module_id`',
            'user_display' => 'a.`user_id`',
        );

        if ($contentTable !== '') {
            $joins[] = "LEFT JOIN {$contentTable} cr ON cr.`id` = IFNULL(NULLIF(tc.`resource_id`, 0), a.`course_id`)";
            $joins[] = "LEFT JOIN {$contentTable} mr ON mr.`id` = IFNULL(NULLIF(tm.`resource_id`, 0), a.`module_id`)";
            $selectExtra[4] = "COALESCE(NULLIF(cr.`pagetitle`, ''), CONCAT('Курс #', a.`course_id`)) AS `course_title`";
            $selectExtra[5] = "COALESCE(NULLIF(mr.`pagetitle`, ''), CONCAT('Модуль #', a.`module_id`)) AS `module_title`";
            $queryParts[] = "cr.`pagetitle` LIKE :query";
            $queryParts[] = "mr.`pagetitle` LIKE :query";
            $sortMap['course_display'] = 'cr.`pagetitle`';
            $sortMap['module_display'] = 'mr.`pagetitle`';
        }

        if ($usersTable !== '') {
            $joins[] = "LEFT JOIN {$usersTable} u ON u.`id` = a.`user_id`";
            $selectExtra[1] = "u.`username` AS `username`";
            // В MODX Revolution email хранится в modUserProfile (user_attributes), не в modUser.
            $selectExtra[2] = "'' AS `email`";
            $queryParts[] = "u.`username` LIKE :query";
            $sortMap['user_display'] = 'u.`username`';
        }

        if ($attributesTable !== '' && $usersTable !== '') {
            $joins[] = "LEFT JOIN {$attributesTable} ua ON ua.`internalKey` = u.`id`";
            $selectExtra[2] = "ua.`email` AS `email`";
            $selectExtra[3] = "ua.`fullname` AS `fullname`";
            $queryParts[] = "ua.`email` LIKE :query";
            $queryParts[] = "ua.`fullname` LIKE :query";
            $sortMap['user_display'] = 'ua.`fullname`';
        }

        $where = array('1=1');
        $params = array();

        if ($practiceId > 0) {
            $where[] = 'a.`practice_id` = :practice_id';
            $params[':practice_id'] = $practiceId;
        }

        if ($status !== '') {
            $where[] = 'a.`status` = :status';
            $params[':status'] = $status;
        }

        if ($query !== '') {
            $where[] = '(' . implode(' OR ', $queryParts) . ')';
            $params[':query'] = '%' . $query . '%';
        }

        $whereSql = implode(' AND ', $where);
        $joinSql = implode("\n", $joins);

        $countSql = "
            SELECT COUNT(*)
            FROM {$attemptsTable} a
            {$joinSql}
            WHERE {$whereSql}
        ";

        $countStmt = $this->modx->prepare($countSql);
        if (!$countStmt || !$countStmt->execute($params)) {
            $err = $countStmt ? $countStmt->errorInfo() : array('', '', 'prepare failed');
            return $this->failure('Не удалось загрузить попытки практических заданий: ' . (isset($err[2]) ? $err[2] : 'SQL error'));
        }
        $total = (int)$countStmt->fetchColumn();

        $sort = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$this->getProperty('sort', 'id'));
        $sortSql = isset($sortMap[$sort]) ? $sortMap[$sort] : 'a.`id`';
        $dir = strtoupper((string)$this->getProperty('dir', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "
            SELECT
                a.*,
                " . implode(",\n                ", $selectExtra) . "
            FROM {$attemptsTable} a
            {$joinSql}
            WHERE {$whereSql}
            ORDER BY {$sortSql} {$dir}, a.`id` DESC
        ";

        if ($limit > 0) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            $err = $stmt ? $stmt->errorInfo() : array('', '', 'prepare failed');
            return $this->failure('Не удалось загрузить список попыток: ' . (isset($err[2]) ? $err[2] : 'SQL error'));
        }

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = array();

        foreach ($rows as $row) {
            $fullname = trim((string)$row['fullname']);
            if ($fullname === '') {
                $fullname = trim((string)$row['username']);
            }
            if ($fullname === '') {
                $fullname = 'Пользователь #' . (int)$row['user_id'];
            }

            $row['user_display'] = $fullname;
            $row['status_text'] = trainingPracticeMgrStatusText($row['status']);
            $row['createdon_formatted'] = trainingPracticeMgrDate($row['createdon']);
            $row['submittedon_formatted'] = trainingPracticeMgrDate($row['submittedon']);
            $row['reviewedon_formatted'] = trainingPracticeMgrDate($row['reviewedon']);
            $row['deadline_at_formatted'] = trainingPracticeMgrDate($row['deadline_at']);
            $row['course_display'] = trim((string)$row['course_title']) !== '' ? $row['course_title'] : ('Курс #' . (int)$row['course_id']);
            $row['module_display'] = trim((string)$row['module_title']) !== '' ? $row['module_title'] : ('Модуль #' . (int)$row['module_id']);
            $row['attempt_num'] = (int)(!empty($row['attempt_num']) ? $row['attempt_num'] : $row['attempt_no']);
            $row['attempt_no'] = $row['attempt_num'];
            $results[] = $row;
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingPracticeMgrAttemptsGetListProcessor';

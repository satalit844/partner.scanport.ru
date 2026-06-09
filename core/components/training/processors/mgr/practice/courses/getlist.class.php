<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrCoursesGetListProcessor extends modProcessor
{
    public function process()
    {
        $coursesTable = trainingPracticeMgrTable($this->modx, 'training_courses');
        $contentTable = trainingPracticeMgrTable($this->modx, 'site_content');

        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $query = trim((string)$this->getProperty('query', ''));
        if ($query === '') {
            $query = trim((string)$this->getProperty('search', ''));
        }

        $where = array('1=1');
        $params = array();

        if ($query !== '') {
            $where[] = '(c.`id` = :query_id OR c.`resource_id` = :query_id OR r.`pagetitle` LIKE :query)';
            $params[':query_id'] = (int)$query;
            $params[':query'] = '%' . $query . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->modx->prepare("\n            SELECT COUNT(*)\n            FROM {$coursesTable} c\n            LEFT JOIN {$contentTable} r ON r.`id` = c.`resource_id`\n            WHERE {$whereSql}\n        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "\n            SELECT\n                c.`id`,\n                c.`resource_id`,\n                c.`is_active`,\n                r.`pagetitle`\n            FROM {$coursesTable} c\n            LEFT JOIN {$contentTable} r ON r.`id` = c.`resource_id`\n            WHERE {$whereSql}\n            ORDER BY r.`menuindex` ASC, c.`id` ASC\n        ";

        if ($limit > 0) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->modx->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $results = array();
        foreach ($rows as $row) {
            $title = trim((string)$row['pagetitle']);
            if ($title === '') {
                $title = 'Курс #' . (int)$row['id'];
            }

            $row['display'] = '#' . (int)$row['id'] . ' / ресурс ' . (int)$row['resource_id'] . ' — ' . $title;
            $results[] = $row;
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingPracticeMgrCoursesGetListProcessor';

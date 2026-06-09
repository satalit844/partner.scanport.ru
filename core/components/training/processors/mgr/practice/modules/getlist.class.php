<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrModulesGetListProcessor extends modProcessor
{
    public function process()
    {
        $modulesTable = trainingPracticeMgrTable($this->modx, 'training_modules');
        $contentTable = trainingPracticeMgrTable($this->modx, 'site_content');

        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $courseId = (int)$this->getProperty('course_id', 0);
        $query = trim((string)$this->getProperty('query', ''));
        if ($query === '') {
            $query = trim((string)$this->getProperty('search', ''));
        }

        $where = array('1=1');
        $params = array();

        if ($courseId > 0) {
            $where[] = 'm.`course_id` = :course_id';
            $params[':course_id'] = $courseId;
        }

        if ($query !== '') {
            $where[] = '(m.`id` = :query_id OR m.`resource_id` = :query_id OR r.`pagetitle` LIKE :query)';
            $params[':query_id'] = (int)$query;
            $params[':query'] = '%' . $query . '%';
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->modx->prepare("\n            SELECT COUNT(*)\n            FROM {$modulesTable} m\n            LEFT JOIN {$contentTable} r ON r.`id` = m.`resource_id`\n            WHERE {$whereSql}\n        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "\n            SELECT\n                m.`id`,\n                m.`course_id`,\n                m.`resource_id`,\n                m.`is_active`,\n                r.`pagetitle`\n            FROM {$modulesTable} m\n            LEFT JOIN {$contentTable} r ON r.`id` = m.`resource_id`\n            WHERE {$whereSql}\n            ORDER BY r.`menuindex` ASC, m.`id` ASC\n        ";

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
                $title = 'Модуль #' . (int)$row['id'];
            }

            $row['display'] = '#' . (int)$row['id'] . ' / ресурс ' . (int)$row['resource_id'] . ' — ' . $title;
            $results[] = $row;
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingPracticeMgrModulesGetListProcessor';

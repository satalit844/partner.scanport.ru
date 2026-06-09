<?php

require_once __DIR__ . '/_helpers.php';

class TrainingModuleTestLinkPracticesProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $query = trim((string)$this->getProperty('query', ''));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $start = max(0, (int)$this->getProperty('start', 0));
        $courseId = (int)$this->getProperty('course_id', 0);
        $moduleId = (int)$this->getProperty('module_id', 0);
        $activeOnly = TrainingModuleTestLinkHelper::boolValue($this->getProperty('active_only', 1), 1) === 1;

        $table = TrainingModuleTestLinkHelper::plainTable($this->modx, 'training_practices');
        $where = array('1=1');
        $params = array();

        if ($courseId > 0) {
            $where[] = '`course_id` = :course_id';
            $params[':course_id'] = $courseId;
        }
        if ($moduleId > 0) {
            $where[] = '`module_id` = :module_id';
            $params[':module_id'] = $moduleId;
        }
        if ($activeOnly) {
            $where[] = '`active` = 1';
        }
        if ($query !== '') {
            $where[] = '(`title` LIKE :query OR `description` LIKE :query OR `id` = :query_id)';
            $params[':query'] = '%' . $query . '%';
            $params[':query_id'] = (int)$query;
        }

        $whereSql = implode(' AND ', $where);

        $stmtCount = $this->modx->prepare("SELECT COUNT(*) FROM `{$table}` WHERE {$whereSql}");
        $stmtCount->execute($params);
        $total = (int)$stmtCount->fetchColumn();

        $sql = "SELECT * FROM `{$table}` WHERE {$whereSql} ORDER BY `rank` ASC, `id` ASC";
        if ($limit > 0) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->modx->prepare($sql);
        $stmt->execute($params);

        $results = array();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id = (int)$row['id'];
            $title = trim((string)$row['title']);
            if ($title === '') {
                $title = 'Практическое задание #' . $id;
            }

            $description = trim((string)$row['description']);
            $display = '#' . $id . ' ' . $title;
            if ($description !== '') {
                $display .= ' — ' . mb_substr(strip_tags($description), 0, 120, 'UTF-8');
            }

            $results[] = array(
                'id' => $id,
                'title' => $title,
                'name' => $title,
                'description' => $description,
                'course_id' => (int)$row['course_id'],
                'module_id' => (int)$row['module_id'],
                'active' => (int)$row['active'] === 1 ? 1 : 0,
                'display' => $display,
            );
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingModuleTestLinkPracticesProcessor';

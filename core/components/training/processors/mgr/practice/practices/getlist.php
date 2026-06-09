<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrPracticeGetListProcessor extends modProcessor
{
    public function process()
    {
        $table = trainingPracticeMgrTable($this->modx, 'training_practices');
        $coursesTable = trainingPracticeMgrTable($this->modx, 'training_courses');
        $modulesTable = trainingPracticeMgrTable($this->modx, 'training_modules');
        $contentTable = trainingPracticeMgrTable($this->modx, 'site_content');

        $start = max(0, (int)$this->getProperty('start', 0));
        $limit = max(0, (int)$this->getProperty('limit', 20));
        $query = trim((string)$this->getProperty('query', ''));
        $courseId = (int)$this->getProperty('course_id', 0);
        $moduleId = (int)$this->getProperty('module_id', 0);
        $active = trim((string)$this->getProperty('active', ''));

        $where = array('1=1');
        $params = array();

        if ($query !== '') {
            $where[] = '(p.`title` LIKE :query OR p.`description` LIKE :query OR cr.`pagetitle` LIKE :query OR mr.`pagetitle` LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }
        if ($courseId > 0) {
            $where[] = 'p.`course_id` = :course_id';
            $params[':course_id'] = $courseId;
        }
        if ($moduleId > 0) {
            $where[] = 'p.`module_id` = :module_id';
            $params[':module_id'] = $moduleId;
        }
        if ($active !== '') {
            $where[] = 'p.`active` = :active';
            $params[':active'] = (int)$active;
        }

        $whereSql = implode(' AND ', $where);

        $countStmt = $this->modx->prepare("\n            SELECT COUNT(*)\n            FROM {$table} p\n            LEFT JOIN {$coursesTable} tc ON tc.`id` = p.`course_id`\n            LEFT JOIN {$modulesTable} tm ON tm.`id` = p.`module_id`\n            LEFT JOIN {$contentTable} cr ON cr.`id` = tc.`resource_id`\n            LEFT JOIN {$contentTable} mr ON mr.`id` = tm.`resource_id`\n            WHERE {$whereSql}\n        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $allowedSort = array(
            'id' => 'p.`id`',
            'course_id' => 'p.`course_id`',
            'module_id' => 'p.`module_id`',
            'title' => 'p.`title`',
            'active' => 'p.`active`',
            'deadline_at' => 'p.`deadline_at`',
            'rank' => 'p.`rank`',
            'createdon' => 'p.`createdon`',
            'editedon' => 'p.`editedon`',
            'course_display' => 'cr.`pagetitle`',
            'module_display' => 'mr.`pagetitle`',
        );

        $sort = preg_replace('/[^a-zA-Z0-9_]/', '', (string)$this->getProperty('sort', 'rank'));
        if (!isset($allowedSort[$sort])) {
            $sort = 'rank';
        }
        $dir = strtoupper((string)$this->getProperty('dir', 'ASC')) === 'DESC' ? 'DESC' : 'ASC';

        $sql = "\n            SELECT\n                p.*,\n                tc.`resource_id` AS `course_resource_id`,\n                tm.`resource_id` AS `module_resource_id`,\n                cr.`pagetitle` AS `course_title`,\n                mr.`pagetitle` AS `module_title`\n            FROM {$table} p\n            LEFT JOIN {$coursesTable} tc ON tc.`id` = p.`course_id`\n            LEFT JOIN {$modulesTable} tm ON tm.`id` = p.`module_id`\n            LEFT JOIN {$contentTable} cr ON cr.`id` = tc.`resource_id`\n            LEFT JOIN {$contentTable} mr ON mr.`id` = tm.`resource_id`\n            WHERE {$whereSql}\n            ORDER BY {$allowedSort[$sort]} {$dir}, p.`id` ASC\n        ";

        if ($limit > 0) {
            $sql .= " LIMIT {$start}, {$limit}";
        }

        $stmt = $this->modx->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $results = array();

        foreach ($rows as $row) {
            $courseTitle = trim((string)$row['course_title']);
            $moduleTitle = trim((string)$row['module_title']);

            $row['active_text'] = !empty($row['active']) ? 'Да' : 'Нет';
            $row['createdon_formatted'] = trainingPracticeMgrDate($row['createdon']);
            $row['editedon_formatted'] = trainingPracticeMgrDate($row['editedon']);
            $row['deadline_at_formatted'] = trainingPracticeMgrDate($row['deadline_at']);
            $row['course_display'] = $courseTitle !== ''
                ? ('#' . (int)$row['course_id'] . ' / ресурс ' . (int)$row['course_resource_id'] . ' — ' . $courseTitle)
                : ('Курс #' . (int)$row['course_id']);
            $row['module_display'] = $moduleTitle !== ''
                ? ('#' . (int)$row['module_id'] . ' / ресурс ' . (int)$row['module_resource_id'] . ' — ' . $moduleTitle)
                : ('Модуль #' . (int)$row['module_id']);
            $results[] = $row;
        }

        return $this->outputArray($results, $total);
    }
}

return 'TrainingPracticeMgrPracticeGetListProcessor';

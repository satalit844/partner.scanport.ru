<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideosGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->successJson([]);
        }

        $query = trim((string)$this->getProperty('query', ''));
        $sort = trim((string)$this->getProperty('sort', 'sort_order'));
        $dir = strtoupper(trim((string)$this->getProperty('dir', 'ASC')));

        if (!in_array($sort, ['id', 'title', 'sort_order', 'duration_seconds', 'video_status'], true)) {
            $sort = 'sort_order';
        }
        if (!in_array($dir, ['ASC', 'DESC'], true)) {
            $dir = 'ASC';
        }

        $table = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $sql = 'SELECT * FROM `' . $table . '` WHERE `lesson_id` = :lesson_id';
        $params = [':lesson_id' => $lessonId];

        if ($query !== '') {
            $sql .= ' AND (`title` LIKE :query OR `source_video` LIKE :query)';
            $params[':query'] = '%' . $query . '%';
        }

        $sql .= ' ORDER BY `' . $sort . '` ' . $dir . ', `id` ASC';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute($params)) {
            return $this->failure('Не удалось получить список видео урока');
        }

        $rows = [];
        foreach ((array)$stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $row['qualities_count'] = TrainingLessonVideoHelper::countLessonQualities($this->modx, (int)$row['id']);
            $row['slides_count'] = TrainingLessonVideoHelper::countVideoSlides($this->modx, (int)$row['id']);
            $row['is_default'] = (int)!empty($row['is_default']);
            $row['is_active'] = (int)!empty($row['is_active']);
            $rows[] = $row;
        }

        return $this->successJson($rows);
    }

    protected function successJson(array $rows)
    {
        return $this->modx->toJSON([
            'success' => true,
            'total' => count($rows),
            'results' => array_values($rows),
        ]);
    }
}
return 'TrainingLessonVideosGetListProcessor';

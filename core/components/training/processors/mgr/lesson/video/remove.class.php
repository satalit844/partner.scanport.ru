<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoRemoveProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $ids = $this->collectIds();
        if (!$ids) { return $this->failure('Не выбраны видео урока'); }

        $videoTable = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $qualitiesTable = TrainingLessonVideoHelper::qualitiesTable($this->modx);
        $slidesTable = TrainingLessonVideoHelper::slidesTable($this->modx);
        $affectedLessons = [];

        foreach ($ids as $id) {
            $row = TrainingLessonVideoHelper::fetchVideo($this->modx, $id);
            if (!$row) { continue; }

            $qStmt = $this->modx->prepare('SELECT `file_path` FROM `' . $qualitiesTable . '` WHERE `lesson_video_id` = :lesson_video_id');
            if ($qStmt && $qStmt->execute([':lesson_video_id' => (int)$id])) {
                foreach ((array)$qStmt->fetchAll(PDO::FETCH_ASSOC) as $qRow) {
                    TrainingLessonVideoHelper::unlinkWebPath($this->modx, $qRow['file_path']);
                }
            }

            TrainingLessonVideoHelper::unlinkWebPath($this->modx, $row['source_video']);

            $stmt = $this->modx->prepare('DELETE FROM `' . $qualitiesTable . '` WHERE `lesson_video_id` = :lesson_video_id');
            if ($stmt) { $stmt->execute([':lesson_video_id' => (int)$id]); }
            $stmt = $this->modx->prepare('DELETE FROM `' . $slidesTable . '` WHERE `lesson_video_id` = :lesson_video_id');
            if ($stmt) { $stmt->execute([':lesson_video_id' => (int)$id]); }
            $stmt = $this->modx->prepare('DELETE FROM `' . $videoTable . '` WHERE `id` = :id');
            if ($stmt) { $stmt->execute([':id' => (int)$id]); }

            $affectedLessons[(int)$row['lesson_id']] = (int)$row['lesson_id'];
        }

        foreach ($affectedLessons as $lessonId) {
            $lesson = TrainingLessonVideoHelper::getLesson($this->modx, $lessonId);
            if ($lesson) {
                TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);
            }
        }

        return $this->success('Видео урока удалены');
    }

    protected function collectIds()
    {
        $raw = $this->getProperty('ids', $this->getProperty('id', ''));
        if (is_array($raw)) {
            return array_values(array_filter(array_map('intval', $raw)));
        }
        $raw = trim((string)$raw);
        return $raw === '' ? [] : array_values(array_filter(array_map('intval', explode(',', $raw))));
    }
}
return 'TrainingLessonVideoRemoveProcessor';

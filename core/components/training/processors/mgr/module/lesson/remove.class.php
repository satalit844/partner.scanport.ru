<?php

require_once dirname(dirname(__DIR__)) . '/lesson/_video_helper.php';
require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleLessonRemoveProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    public function process()
    {
        $ids = $this->collectIds();
        if (!$ids) {
            return $this->failure('Не выбраны уроки');
        }

        $removed = 0;
        foreach ($ids as $id) {
            $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => (int)$id]);
            if (!$lesson) { continue; }

            $videoTable = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
            $qualitiesTable = TrainingLessonVideoHelper::qualitiesTable($this->modx);
            $slidesTable = TrainingLessonVideoHelper::slidesTable($this->modx);

            $videoStmt = $this->modx->prepare('SELECT `id`,`source_video` FROM `' . $videoTable . '` WHERE `lesson_id` = :lesson_id');
            $videos = [];
            if ($videoStmt && $videoStmt->execute([':lesson_id' => (int)$id])) {
                $videos = $videoStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            foreach ($videos as $video) {
                $qStmt = $this->modx->prepare('SELECT `file_path` FROM `' . $qualitiesTable . '` WHERE `lesson_video_id` = :lesson_video_id');
                if ($qStmt && $qStmt->execute([':lesson_video_id' => (int)$video['id']])) {
                    foreach ((array)$qStmt->fetchAll(PDO::FETCH_ASSOC) as $qRow) {
                        TrainingLessonVideoHelper::unlinkWebPath($this->modx, $qRow['file_path']);
                    }
                }
                TrainingLessonVideoHelper::unlinkWebPath($this->modx, $video['source_video']);
            }

            $stmt = $this->modx->prepare('DELETE FROM `' . $qualitiesTable . '` WHERE `lesson_id` = :lesson_id');
            if ($stmt) { $stmt->execute([':lesson_id' => (int)$id]); }
            $stmt = $this->modx->prepare('DELETE FROM `' . $slidesTable . '` WHERE `lesson_id` = :lesson_id');
            if ($stmt) { $stmt->execute([':lesson_id' => (int)$id]); }
            $stmt = $this->modx->prepare('DELETE FROM `' . $videoTable . '` WHERE `lesson_id` = :lesson_id');
            if ($stmt) { $stmt->execute([':lesson_id' => (int)$id]); }

            TrainingLessonVideoHelper::unlinkWebPath($this->modx, $lesson->get('source_presentation'));
            TrainingLessonVideoHelper::unlinkWebPath($this->modx, $lesson->get('presentation_pdf'));

            $lesson->remove();
            $removed++;
        }

        return $this->success('Уроки удалены', ['removed' => $removed]);
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
return 'TrainingModuleLessonRemoveProcessor';

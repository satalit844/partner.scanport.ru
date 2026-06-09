<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoUpdateProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    protected function boolValue($value)
    {
        return in_array((string)$value, ['1','true','yes','on'], true) || $value === true || $value === 1 ? 1 : 0;
    }

    protected function trimValue($value, $limit = 0)
    {
        $value = trim((string)$value);
        if ($limit > 0 && function_exists('mb_substr')) {
            $value = mb_substr($value, 0, $limit, 'UTF-8');
        } elseif ($limit > 0) {
            $value = substr($value, 0, $limit);
        }
        return $value;
    }

    protected function normalizeSourceVideo($path)
    {
        $path = $this->trimValue($path, 255);
        if ($path === '') {
            return '';
        }

        $path = str_replace('\\', '/', $path);
        $basename = basename($path);
        if ($basename === '' || strpos($basename, '.') === false) {
            return '';
        }

        return $path;
    }

    public function process()
    {
        $id = (int)$this->getProperty('id');
        $row = TrainingLessonVideoHelper::fetchVideo($this->modx, $id);
        if (!$row) {
            return $this->failure('Видео урока не найдено');
        }

        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, (int)$row['lesson_id']);
        if (!$lesson) {
            return $this->failure('Урок не найден');
        }

        $title = $this->trimValue($this->getProperty('title'), 255);
        if ($title === '') {
            return $this->failure('Укажи название');
        }

        $sort = (int)$this->getProperty('sort_order');
        if ($sort <= 0) {
            $sort = 1;
        }

        $sourceVideo = $this->normalizeSourceVideo($this->getProperty('source_video', $row['source_video']));
        $table = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $sql = 'UPDATE `' . $table . '` '
             . 'SET `title`=:title, `description`=:description, `sort_order`=:sort_order, `source_video`=:source_video, `video_status`=:video_status, `is_default`=:is_default, `is_active`=:is_active, `updatedon`=:updatedon '
             . 'WHERE `id`=:id';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return $this->failure('Не удалось подготовить запрос на сохранение видео урока');
        }

        $videoStatus = $sourceVideo !== '' ? 'available' : ((int)TrainingLessonVideoHelper::countLessonQualities($this->modx, $id) > 0 ? 'ready' : 'none');
        $ok = false;
        try {
            $ok = $stmt->execute([
                ':title'       => $title,
                ':description' => trim((string)$this->getProperty('description', $row['description'])),
                ':sort_order'  => $sort,
                ':source_video'=> $sourceVideo,
                ':video_status'=> $videoStatus,
                ':is_default'  => $this->boolValue($this->getProperty('is_default', $row['is_default'])),
                ':is_active'   => $this->boolValue($this->getProperty('is_active', $row['is_active'])),
                ':updatedon'   => date('Y-m-d H:i:s'),
                ':id'          => $id,
            ]);
        } catch (Exception $e) {
            return $this->failure('Не удалось сохранить видео урока: ' . $e->getMessage());
        }

        if (!$ok) {
            $error = $stmt->errorInfo();
            $message = !empty($error[2]) ? $error[2] : 'неизвестная ошибка БД';
            return $this->failure('Не удалось сохранить видео урока: ' . $message);
        }

        if ($this->boolValue($this->getProperty('is_default', $row['is_default']))) {
            TrainingLessonVideoHelper::clearDefaultLessonVideo($this->modx, (int)$row['lesson_id'], $id);
        }

        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);
        return $this->success('Видео урока сохранено');
    }
}
return 'TrainingLessonVideoUpdateProcessor';

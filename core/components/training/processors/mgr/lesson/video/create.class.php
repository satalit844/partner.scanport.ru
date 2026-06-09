<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoCreateProcessor extends modProcessor
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

        // Если из браузера прилетела папка, а не файл, не валим создание записи.
        $basename = basename($path);
        if ($basename === '' || strpos($basename, '.') === false) {
            return '';
        }

        return $path;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->failure('Не указан урок');
        }

        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, $lessonId);
        if (!$lesson) {
            return $this->failure('Урок не найден');
        }

        $title = $this->trimValue($this->getProperty('title'), 255);
        if ($title === '') {
            $title = 'Новое видео';
        }

        $sort = (int)$this->getProperty('sort_order');
        if ($sort <= 0) {
            $sort = TrainingLessonVideoHelper::nextSortOrder($this->modx, $lessonId);
        }

        $sourceVideo = $this->normalizeSourceVideo($this->getProperty('source_video', ''));
        $videoStatus = $sourceVideo !== '' ? 'available' : 'none';
        $description = trim((string)$this->getProperty('description', ''));
        $now = date('Y-m-d H:i:s');

        $table = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $sql = 'INSERT INTO `' . $table . '` '
             . '(`lesson_id`,`title`,`description`,`sort_order`,`source_video`,`duration_seconds`,`video_status`,`source_presentation`,`presentation_pdf`,`slides_dir`,`presentation_status`,`preview_image`,`is_default`,`is_active`,`createdon`,`updatedon`) '
             . 'VALUES '
             . '(:lesson_id,:title,:description,:sort_order,:source_video,0,:video_status,\'\',\'\',\'\',\'none\',\'\',:is_default,:is_active,:createdon,:updatedon)';

        $stmt = $this->modx->prepare($sql);
        if (!$stmt) {
            return $this->failure('Не удалось подготовить запрос на создание видео урока');
        }

        $ok = false;
        try {
            $ok = $stmt->execute([
                ':lesson_id'   => $lessonId,
                ':title'       => $title,
                ':description' => $description,
                ':sort_order'  => $sort,
                ':source_video'=> $sourceVideo,
                ':video_status'=> $videoStatus,
                ':is_default'  => $this->boolValue($this->getProperty('is_default', 0)),
                ':is_active'   => $this->boolValue($this->getProperty('is_active', 1)),
                ':createdon'   => $now,
                ':updatedon'   => $now,
            ]);
        } catch (Exception $e) {
            return $this->failure('Не удалось создать видео урока: ' . $e->getMessage());
        }

        if (!$ok) {
            $error = $stmt->errorInfo();
            $message = !empty($error[2]) ? $error[2] : 'неизвестная ошибка БД';
            return $this->failure('Не удалось создать видео урока: ' . $message);
        }

        $id = 0;
        $idStmt = $this->modx->query('SELECT LAST_INSERT_ID()');
        if ($idStmt) {
            $id = (int)$idStmt->fetchColumn();
        }

        if ($this->boolValue($this->getProperty('is_default', 0)) && $id > 0) {
            TrainingLessonVideoHelper::clearDefaultLessonVideo($this->modx, $lessonId, $id);
        }

        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);

        return $this->success('Видео урока создано', ['id' => $id]);
    }
}
return 'TrainingLessonVideoCreateProcessor';

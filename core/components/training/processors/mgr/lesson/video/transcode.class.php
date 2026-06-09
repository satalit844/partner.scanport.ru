<?php

require_once dirname(__DIR__) . '/_video_helper.php';

class TrainingLessonVideoTranscodeProcessor extends modProcessor
{
    protected $bitrates = [1080 => 3500, 720 => 2000, 480 => 900, 320 => 450];

    public function checkPermissions(){return true;}

    protected function upsertQuality($moduleId, $lessonId, $lessonVideoId, array $data)
    {
        $table = TrainingLessonVideoHelper::qualitiesTable($this->modx);
        $check = $this->modx->prepare('SELECT `id` FROM `' . $table . '` WHERE `lesson_video_id` = :lesson_video_id AND `quality` = :quality LIMIT 1');
        $id = 0;
        if ($check && $check->execute([':lesson_video_id' => $lessonVideoId, ':quality' => $data['quality']])) {
            $id = (int)$check->fetchColumn();
        }

        if ($id > 0) {
            $sql = 'UPDATE `' . $table . '` SET `module_id`=:module_id, `lesson_id`=:lesson_id, `lesson_video_id`=:lesson_video_id, `mime`=:mime, `file_path`=:file_path, `width`=:width, `height`=:height, `bitrate`=:bitrate, `filesize`=:filesize, `is_default`=:is_default, `is_active`=:is_active WHERE `id`=:id';
            $params = [
                ':module_id' => $moduleId, ':lesson_id' => $lessonId, ':lesson_video_id' => $lessonVideoId,
                ':mime' => $data['mime'], ':file_path' => $data['file_path'], ':width' => $data['width'], ':height' => $data['height'],
                ':bitrate' => $data['bitrate'], ':filesize' => $data['filesize'], ':is_default' => $data['is_default'], ':is_active' => $data['is_active'], ':id' => $id
            ];
            $stmt = $this->modx->prepare($sql);
            return ($stmt && $stmt->execute($params)) ? $id : 0;
        }

        $sql = 'INSERT INTO `' . $table . '` (`module_id`,`lesson_id`,`lesson_video_id`,`quality`,`mime`,`file_path`,`width`,`height`,`bitrate`,`filesize`,`is_default`,`is_active`) VALUES (:module_id,:lesson_id,:lesson_video_id,:quality,:mime,:file_path,:width,:height,:bitrate,:filesize,:is_default,:is_active)';
        $stmt = $this->modx->prepare($sql);
        if (!$stmt || !$stmt->execute([
            ':module_id' => $moduleId, ':lesson_id' => $lessonId, ':lesson_video_id' => $lessonVideoId,
            ':quality' => $data['quality'], ':mime' => $data['mime'], ':file_path' => $data['file_path'],
            ':width' => $data['width'], ':height' => $data['height'], ':bitrate' => $data['bitrate'],
            ':filesize' => $data['filesize'], ':is_default' => $data['is_default'], ':is_active' => $data['is_active']
        ])) {
            return 0;
        }
        return (int)$this->modx->lastInsertId();
    }

    public function process()
    {
        $lessonVideoId = (int)$this->getProperty('lesson_video_id');
        $video = TrainingLessonVideoHelper::fetchVideo($this->modx, $lessonVideoId);
        if (!$video) {
            return $this->failure('Не выбрано видео урока');
        }

        $lesson = TrainingLessonVideoHelper::getLesson($this->modx, (int)$video['lesson_id']);
        if (!$lesson) {
            return $this->failure('Урок не найден');
        }

        $sourceAbsolute = TrainingMediaHelper::resolveLocalPath($this->modx, $video['source_video']);
        if ($sourceAbsolute === '' || !is_file($sourceAbsolute)) {
            return $this->failure('Исходный видеофайл не найден');
        }

        if (!TrainingLessonVideoHelper::ensureVideoDirs($this->modx, $lesson, $lessonVideoId)) {
            return $this->failure('Не удалось создать папки для видео урока');
        }

        $dirs = TrainingLessonVideoHelper::resolveVideoDirs($this->modx, $lesson, $lessonVideoId);
        $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION));
        $storedSourceAbsolute = $dirs['source_absolute'] . TrainingLessonVideoHelper::buildSourceFilename($this->modx, $lesson, $lessonVideoId, $extension);
        if (!TrainingMediaHelper::copyInto($sourceAbsolute, $storedSourceAbsolute)) {
            return $this->failure('Не удалось сохранить исходный файл видео');
        }

        $sourceMeta = TrainingMediaHelper::detectVideoMeta($this->modx, $storedSourceAbsolute);
        if ((int)$sourceMeta['height'] <= 0) {
            return $this->failure('ffprobe не смог определить параметры исходного видео');
        }

        $qualityString = trim((string)$this->getProperty('qualities', $this->modx->getOption('training_video_qualities', null, '1080,720,480,320', true)));
        $qualityParts = array_filter(array_map('trim', explode(',', $qualityString)));
        if (!$qualityParts) {
            $qualityParts = ['1080','720','480','320'];
        }

        $allowUpscale = (int)$this->modx->getOption('training_upscale_variants', null, 0, true) === 1;
        $ffmpeg = TrainingMediaHelper::getCommand($this->modx, 'training_ffmpeg_command', 'ffmpeg');

        $createdIds = [];
        $defaultId = 0;
        foreach ($qualityParts as $qualityPart) {
            $targetHeight = (int)preg_replace('/\D+/', '', $qualityPart);
            if ($targetHeight <= 0) { continue; }
            if (!$allowUpscale && $targetHeight > (int)$sourceMeta['height']) { continue; }

            $qualityName = $targetHeight . 'p';
            $qualityDir = $dirs['qualities_absolute'] . $qualityName . '/';
            if (!TrainingMediaHelper::ensureDir($qualityDir)) { continue; }

            $outputAbsolute = $qualityDir . TrainingLessonVideoHelper::buildQualityFilename($this->modx, $lesson, $lessonVideoId, $qualityName, 'mp4');
            $videoBitrate = isset($this->bitrates[$targetHeight]) ? (int)$this->bitrates[$targetHeight] : 1200;
            $audioBitrate = $targetHeight >= 720 ? '128k' : '96k';

            $command = escapeshellcmd($ffmpeg)
                . ' -y -i ' . escapeshellarg($storedSourceAbsolute)
                . ' -vf ' . escapeshellarg('scale=-2:' . $targetHeight)
                . ' -c:v libx264 -preset veryfast -crf 23 -pix_fmt yuv420p'
                . ' -b:v ' . escapeshellarg($videoBitrate . 'k')
                . ' -maxrate ' . escapeshellarg(($videoBitrate + 300) . 'k')
                . ' -bufsize ' . escapeshellarg(($videoBitrate * 2) . 'k')
                . ' -movflags +faststart -c:a aac -b:a ' . escapeshellarg($audioBitrate)
                . ' ' . escapeshellarg($outputAbsolute);

            $output = [];
            $code = 0;
            if (!TrainingMediaHelper::runCommand($this->modx, $command, $output, $code)) {
                return $this->failure("Ошибка FFmpeg на качестве {$qualityName}\n" . implode("\n", $output));
            }

            $meta = TrainingMediaHelper::detectVideoMeta($this->modx, $outputAbsolute);
            $qualityId = $this->upsertQuality((int)$lesson->get('module_id'), (int)$lesson->get('id'), $lessonVideoId, [
                'quality' => $qualityName,
                'mime' => 'video/mp4',
                'file_path' => TrainingMediaHelper::fsPathToWeb($this->modx, $outputAbsolute),
                'width' => (int)$meta['width'],
                'height' => (int)$meta['height'],
                'bitrate' => (int)$meta['bitrate'],
                'filesize' => (int)$meta['filesize'],
                'is_default' => $defaultId > 0 ? 0 : 1,
                'is_active' => 1,
            ]);
            if ($qualityId > 0) {
                $createdIds[] = $qualityId;
                if ($defaultId <= 0) {
                    $defaultId = $qualityId;
                }
            }
        }

        if (!$createdIds) {
            return $this->failure('Не удалось сгенерировать ни одного качества');
        }

        if ($defaultId > 0) {
            TrainingLessonVideoHelper::clearDefaultQuality($this->modx, $lessonVideoId, $defaultId);
        }

        $table = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $stmt = $this->modx->prepare('UPDATE `' . $table . '` SET `source_video`=:source_video, `duration_seconds`=:duration_seconds, `video_status`=:video_status, `updatedon`=:updatedon WHERE `id`=:id');
        if ($stmt) {
            $stmt->execute([
                ':source_video' => TrainingMediaHelper::fsPathToWeb($this->modx, $storedSourceAbsolute),
                ':duration_seconds' => (int)round((float)$sourceMeta['duration']),
                ':video_status' => 'ready',
                ':updatedon' => date('Y-m-d H:i:s'),
                ':id' => $lessonVideoId,
            ]);
        }

        TrainingLessonVideoHelper::applyEvenSlideTimecodes($this->modx, $lessonVideoId, (int)round((float)$sourceMeta['duration'] * 1000));
        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);

        return $this->success('Видео обработано', ['lesson_video_id' => $lessonVideoId, 'qualities' => count($createdIds)]);
    }
}
return 'TrainingLessonVideoTranscodeProcessor';

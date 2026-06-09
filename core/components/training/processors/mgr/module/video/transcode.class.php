<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModuleVideoTranscodeProcessor extends modProcessor
{
    protected $bitrates = [
        1080 => 5000,
        720 => 2800,
        480 => 1400,
        320 => 800,
    ];

    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $moduleId = (int)$this->getProperty('module_id');
        $lessonId = (int)$this->getProperty('lesson_id');

        if ($lessonId <= 0) {
            return $this->failure('Сначала выбери видео в верхней таблице');
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return $this->failure('Видео не найдено');
        }

        $moduleId = (int)$lesson->get('module_id');
        /** @var TrainingModule $module */
        $module = $this->modx->getObject('TrainingModule', ['id' => $moduleId]);
        if (!$module) {
            return $this->failure('Модуль не найден');
        }

        $source = trim((string)$lesson->get('source_video'));
        if ($source === '') {
            $source = trim((string)$this->getProperty('source_video'));
        }
        if ($source === '') {
            return $this->failure('У выбранного видео не указан исходный файл');
        }

        $sourceAbsolute = TrainingMediaHelper::resolveLocalPath($this->modx, $source);
        if ($sourceAbsolute === '' || !is_file($sourceAbsolute)) {
            return $this->failure('Исходный видеофайл не найден на сервере');
        }

        $dirs = TrainingMediaHelper::resolveLessonVideoDirs($this->modx, $lesson);
        if (empty($dirs)) {
            return $this->failure('Не удалось определить папки для выбранного видео');
        }

        foreach (['base_absolute', 'source_absolute', 'video_absolute', 'preview_absolute'] as $dirKey) {
            if (empty($dirs[$dirKey]) || !TrainingMediaHelper::ensureDir($dirs[$dirKey])) {
                return $this->failure('Не удалось создать папки для выбранного видео');
            }
        }

        $sourceExtension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION));
        if ($sourceExtension === '') {
            $sourceExtension = 'mp4';
        }

        $storedSourceAbsolute = $dirs['source_absolute'] . TrainingMediaHelper::buildLessonVideoSourceFilename($lesson, $sourceExtension);
        if (!TrainingMediaHelper::copyInto($sourceAbsolute, $storedSourceAbsolute)) {
            return $this->failure('Не удалось сохранить исходный видеофайл в папку выбранного видео');
        }

        $lesson->set('source_video', TrainingMediaHelper::fsPathToWeb($this->modx, $storedSourceAbsolute));
        $lesson->set('video_status', 'processing');
        $lesson->save();

        $sourceMeta = TrainingMediaHelper::detectVideoMeta($this->modx, $storedSourceAbsolute);
        if ((int)$sourceMeta['height'] <= 0) {
            $lesson->set('video_status', 'error');
            $lesson->save();
            return $this->failure('ffprobe не смог определить параметры исходного видео');
        }

        $qualityString = trim((string)$this->getProperty('qualities', $this->modx->getOption('training_video_qualities', null, '1080,720,480,320', true)));
        $qualityParts = array_filter(array_map('trim', explode(',', $qualityString)));
        if (empty($qualityParts)) {
            $qualityParts = ['1080', '720', '480', '320'];
        }

        $allowUpscale = (int)$this->modx->getOption('training_upscale_variants', null, 0, true) === 1;
        $ffmpeg = TrainingMediaHelper::getCommand($this->modx, 'training_ffmpeg_command', 'ffmpeg');
        $createdQualities = [];
        $defaultQuality = '';

        foreach ($qualityParts as $qualityPart) {
            $targetHeight = (int)preg_replace('/\D+/', '', $qualityPart);
            if ($targetHeight <= 0) {
                continue;
            }
            if (!$allowUpscale && $targetHeight > (int)$sourceMeta['height']) {
                continue;
            }

            $qualityName = $targetHeight . 'p';
            $qualityDirAbsolute = $dirs['video_absolute'] . $qualityName . '/';
            if (!TrainingMediaHelper::ensureDir($qualityDirAbsolute)) {
                continue;
            }

            $outputAbsolute = $qualityDirAbsolute . TrainingMediaHelper::buildLessonQualityVideoFilename($lesson, $qualityName, 'mp4');
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
                $lesson->set('video_status', 'error');
                $lesson->save();
                return $this->failure("Ошибка FFmpeg на качестве {$qualityName}\n" . implode("\n", $output));
            }

            $meta = TrainingMediaHelper::detectVideoMeta($this->modx, $outputAbsolute);
            $isDefault = empty($defaultQuality) ? 1 : 0;
            if ($isDefault) {
                $defaultQuality = $qualityName;
            }

            TrainingMediaHelper::upsertLessonVideo($this->modx, $lessonId, [
                'quality' => $qualityName,
                'mime' => 'video/mp4',
                'file_path' => TrainingMediaHelper::fsPathToWeb($this->modx, $outputAbsolute),
                'width' => (int)$meta['width'],
                'height' => (int)$meta['height'],
                'bitrate' => (int)$meta['bitrate'],
                'filesize' => (int)$meta['filesize'],
                'is_default' => $isDefault,
                'is_active' => 1,
            ]);

            $createdQualities[] = $qualityName;
        }

        if (empty($createdQualities)) {
            $lesson->set('video_status', 'error');
            $lesson->save();
            return $this->failure('Не удалось сгенерировать ни одного варианта качества');
        }

        if ($defaultQuality !== '') {
            TrainingMediaHelper::clearOtherDefaultLessonVideos($this->modx, $lessonId, $defaultQuality);
        }

        $durationSeconds = (int)round((float)$sourceMeta['duration']);
        $timedSlides = 0;

        if ((int)$this->modx->getCount('TrainingModuleSlide', ['lesson_id' => $lessonId]) > 0 && $durationSeconds > 0) {
            $timedSlides = TrainingMediaHelper::applyEvenLessonSlideTimecodes($this->modx, $lessonId, $durationSeconds * 1000);
            if ($timedSlides > 0) {
                $lesson->set('presentation_status', 'ready');
            }
        }

        $lesson->set('duration_seconds', $durationSeconds);
        $lesson->set('video_status', 'ready');
        $lesson->save();

        return $this->success('Видео обработано', [
            'lesson_id' => $lessonId,
            'module_id' => $moduleId,
            'qualities' => $createdQualities,
            'duration_seconds' => $durationSeconds,
            'duration_human' => TrainingMediaHelper::formatSeconds($durationSeconds),
            'source_video' => $lesson->get('source_video'),
            'timed_slides' => $timedSlides,
            'video_output_dir' => $dirs['video_web'],
            'base_dir' => $dirs['base_web'],
        ]);
    }
}

return 'TrainingModuleVideoTranscodeProcessor';

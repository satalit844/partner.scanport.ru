<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';
require_once dirname(dirname(__DIR__)) . '/lesson/_video_helper.php';

class TrainingLessonPresentationProcessProcessor extends modProcessor
{
    public function checkPermissions(){return true;}

    protected function cleanupSlideFiles($slidesAbsolute)
    {
        foreach ((array)@scandir($slidesAbsolute) as $oldSlide) {
            if ($oldSlide === '.' || $oldSlide === '..') { continue; }
            $oldSlidePath = $slidesAbsolute . $oldSlide;
            if (is_file($oldSlidePath)) { @unlink($oldSlidePath); }
        }
    }

    protected function collectGeneratedSlides($slidesAbsolute, $prefixBase)
    {
        $slides = glob($slidesAbsolute . $prefixBase . '-*.jpg');
        if (!$slides) {
            $slides = glob($slidesAbsolute . '*.jpg');
        }
        if (!$slides) {
            $slides = glob($slidesAbsolute . '*.jpeg');
        }
        natcasesort($slides);
        return array_values($slides ?: []);
    }

    protected function renameSlides(modX $modx, array $slides, $slidesAbsolute, $lessonId)
    {
        $renamedSlides = [];
        $index = 1;
        foreach ($slides as $slidePath) {
            $ext = strtolower(pathinfo($slidePath, PATHINFO_EXTENSION));
            if ($ext === '') { $ext = 'jpg'; }
            $target = $slidesAbsolute . 'lesson_' . (int)$lessonId . '_slide_' . sprintf('%03d', $index) . '.' . $ext;
            if (realpath($slidePath) !== realpath($target)) {
                @rename($slidePath, $target);
            }
            $renamedSlides[] = $target;
            $index++;
        }
        return $renamedSlides;
    }

    protected function fetchVideoIds($videoTable, $lessonId)
    {
        $videoStmt = $this->modx->prepare('SELECT `id` FROM `' . $videoTable . '` WHERE `lesson_id` = :lesson_id AND `is_active` = 1 ORDER BY `sort_order` ASC, `id` ASC');
        $videoIds = [];
        if ($videoStmt && $videoStmt->execute([':lesson_id' => (int)$lessonId])) {
            foreach ((array)$videoStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $videoIds[] = (int)$row['id'];
            }
        }
        return $videoIds;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->failure('Не указан урок');
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return $this->failure('Урок не найден');
        }

        $source = trim((string)$this->getProperty('source_presentation', $lesson->get('source_presentation')));
        if ($source === '') {
            return $this->failure('Укажи путь к PPT/PPTX/PDF файлу презентации');
        }

        $sourceAbsolute = TrainingMediaHelper::resolveLocalPath($this->modx, $source);
        if ($sourceAbsolute === '' || !is_file($sourceAbsolute)) {
            return $this->failure('Файл презентации не найден на сервере');
        }

        $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION));
        if (!in_array($extension, ['ppt', 'pptx', 'pdf'], true)) {
            return $this->failure('Поддерживаются только ppt, pptx и pdf');
        }

        $dirs = TrainingMediaHelper::resolveLessonPresentationDirs($this->modx, $lesson);
        if (!TrainingMediaHelper::ensureDir($dirs['base_absolute']) || !TrainingMediaHelper::ensureDir($dirs['slides_absolute'])) {
            return $this->failure('Не удалось создать папки урока для презентации');
        }

        $sourceFilename = 'course_' . TrainingLessonVideoHelper::getCourseIdByLesson($this->modx, $lesson)
            . '_module_' . (int)$lesson->get('module_id')
            . '_lesson_' . (int)$lesson->get('id') . '_presentation.' . $extension;
        $storedSourceAbsolute = $dirs['base_absolute'] . $sourceFilename;
        if (!TrainingMediaHelper::copyInto($sourceAbsolute, $storedSourceAbsolute)) {
            return $this->failure('Не удалось сохранить исходный файл презентации');
        }

        $lesson->set('source_presentation', TrainingMediaHelper::fsPathToWeb($this->modx, $storedSourceAbsolute));
        $lesson->set('presentation_status', 'processing');
        $lesson->save();

        $pdfAbsolute = $dirs['pdf_absolute'];
        if ($extension === 'pdf') {
            if (!TrainingMediaHelper::copyInto($storedSourceAbsolute, $pdfAbsolute)) {
                $lesson->set('presentation_status', 'error');
                $lesson->save();
                return $this->failure('Не удалось сохранить PDF презентации');
            }
        } else {
            $soffice = TrainingMediaHelper::getCommand($this->modx, 'training_soffice_command', 'soffice');
            $output = [];
            $code = 0;
            $command = escapeshellcmd($soffice)
                . ' --headless --convert-to pdf --outdir '
                . escapeshellarg(rtrim($dirs['base_absolute'], '/'))
                . ' '
                . escapeshellarg($storedSourceAbsolute);

            if (!TrainingMediaHelper::runCommand($this->modx, $command, $output, $code)) {
                $lesson->set('presentation_status', 'error');
                $lesson->save();
                return $this->failure("LibreOffice не смог конвертировать презентацию в PDF\n" . implode("\n", $output));
            }

            $convertedPdf = $dirs['base_absolute'] . pathinfo($storedSourceAbsolute, PATHINFO_FILENAME) . '.pdf';
            if (!is_file($convertedPdf)) {
                $lesson->set('presentation_status', 'error');
                $lesson->save();
                return $this->failure('После конвертации не найден PDF файл');
            }

            if (realpath($convertedPdf) !== realpath($pdfAbsolute)) {
                @copy($convertedPdf, $pdfAbsolute);
            }
        }

        $slidesTable = TrainingLessonVideoHelper::slidesTable($this->modx);
        $oldStmt = $this->modx->prepare('SELECT `image` FROM `' . $slidesTable . '` WHERE `lesson_id` = :lesson_id');
        if ($oldStmt && $oldStmt->execute([':lesson_id' => $lessonId])) {
            foreach ((array)$oldStmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                TrainingLessonVideoHelper::unlinkWebPath($this->modx, $row['image']);
            }
        }
        $deleteStmt = $this->modx->prepare('DELETE FROM `' . $slidesTable . '` WHERE `lesson_id` = :lesson_id');
        if ($deleteStmt) {
            $deleteStmt->execute([':lesson_id' => $lessonId]);
        }

        $this->cleanupSlideFiles($dirs['slides_absolute']);

        $pdftoppm = TrainingMediaHelper::getCommand($this->modx, 'training_pdftoppm_command', 'pdftoppm');
        $output = [];
        $code = 0;
        $prefixBase = 'course_' . TrainingLessonVideoHelper::getCourseIdByLesson($this->modx, $lesson)
            . '_module_' . (int)$lesson->get('module_id')
            . '_lesson_' . (int)$lesson->get('id') . '_slide';
        $prefix = $dirs['slides_absolute'] . $prefixBase;
        $command = escapeshellcmd($pdftoppm)
            . ' -jpeg -r 150 '
            . escapeshellarg($pdfAbsolute)
            . ' '
            . escapeshellarg($prefix);

        if (!TrainingMediaHelper::runCommand($this->modx, $command, $output, $code)) {
            $lesson->set('presentation_status', 'error');
            $lesson->save();
            return $this->failure("Не удалось разобрать PDF на слайды\n" . implode("\n", $output));
        }

        $generatedSlides = $this->collectGeneratedSlides($dirs['slides_absolute'], $prefixBase);
        if (empty($generatedSlides)) {
            $lesson->set('presentation_status', 'error');
            $lesson->save();
            return $this->failure('После разбора презентации не найдено ни одного слайда');
        }

        $renamedSlides = $this->renameSlides($this->modx, $generatedSlides, $dirs['slides_absolute'], $lessonId);
        $videoTable = TrainingLessonVideoHelper::lessonVideosTable($this->modx);
        $videoIds = $this->fetchVideoIds($videoTable, $lessonId);

        $insert = $this->modx->prepare('INSERT INTO `' . $slidesTable . '` (`module_id`,`lesson_id`,`lesson_video_id`,`slide_no`,`image`,`timecode_ms`,`is_active`) VALUES (:module_id,:lesson_id,:lesson_video_id,:slide_no,:image,0,1)');
        $slidesCount = 0;
        $attachedCount = 0;
        foreach ($renamedSlides as $i => $slidePath) {
            $lessonVideoId = 0;
            if (isset($videoIds[$i])) {
                $lessonVideoId = (int)$videoIds[$i];
                $attachedCount++;
            }
            if ($insert && $insert->execute([
                ':module_id' => (int)$lesson->get('module_id'),
                ':lesson_id' => $lessonId,
                ':lesson_video_id' => $lessonVideoId,
                ':slide_no' => $i + 1,
                ':image' => TrainingMediaHelper::fsPathToWeb($this->modx, $slidePath),
            ])) {
                $slidesCount++;
            }
        }

        $lesson->set('presentation_pdf', $dirs['pdf_web']);
        $lesson->set('slides_dir', $dirs['slides_web']);
        $lesson->set('presentation_status', $slidesCount > 0 ? 'ready' : 'none');
        $lesson->save();
        TrainingLessonVideoHelper::recalcLesson($this->modx, $lesson);

        return $this->success('Презентация урока переразобрана', [
            'source_presentation' => $lesson->get('source_presentation'),
            'presentation_pdf' => $lesson->get('presentation_pdf'),
            'slides_dir' => $lesson->get('slides_dir'),
            'slides_count' => $slidesCount,
            'attached_count' => $attachedCount,
        ]);
    }
}
return 'TrainingLessonPresentationProcessProcessor';

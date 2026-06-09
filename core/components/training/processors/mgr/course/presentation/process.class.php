<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingCoursePresentationProcessProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }
    public function process()
    {
        $courseId = (int)$this->getProperty('course_id');
        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }

        /** @var TrainingCourse $course */
        $course = $this->modx->getObject('TrainingCourse', ['id' => $courseId]);
        if (!$course) {
            return $this->failure('Курс не найден');
        }

        $source = trim((string)$this->getProperty('source_presentation', $course->get('source_presentation')));
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

        $dirs = TrainingMediaHelper::resolveCoursePresentationDirs($this->modx, $course);
        if (!TrainingMediaHelper::ensureDir($dirs['base_absolute']) || !TrainingMediaHelper::ensureDir($dirs['slides_absolute'])) {
            return $this->failure('Не удалось создать папки курса для презентации');
        }

        $storedSourceAbsolute = $dirs['base_absolute'] . TrainingMediaHelper::buildCoursePresentationSourceFilename($course, $extension);
        if (!TrainingMediaHelper::copyInto($sourceAbsolute, $storedSourceAbsolute)) {
            return $this->failure('Не удалось сохранить исходный файл презентации в папку курса');
        }

        $course->set('source_presentation', TrainingMediaHelper::fsPathToWeb($this->modx, $storedSourceAbsolute));
        $course->set('presentation_status', 'processing');
        $course->save();

        $pdfAbsolute = $dirs['pdf_absolute'];
        if ($extension === 'pdf') {
            if (!TrainingMediaHelper::copyInto($storedSourceAbsolute, $pdfAbsolute)) {
                $course->set('presentation_status', 'error');
                $course->save();
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
                $course->set('presentation_status', 'error');
                $course->save();
                return $this->failure("LibreOffice не смог конвертировать презентацию в PDF\n" . implode("\n", $output));
            }

            $convertedPdf = $dirs['base_absolute'] . pathinfo($storedSourceAbsolute, PATHINFO_FILENAME) . '.pdf';
            if (!is_file($convertedPdf)) {
                $course->set('presentation_status', 'error');
                $course->save();
                return $this->failure('После конвертации не найден PDF файл');
            }

            if (realpath($convertedPdf) !== realpath($pdfAbsolute)) {
                @copy($convertedPdf, $pdfAbsolute);
            }
        }

        $oldSlides = scandir($dirs['slides_absolute']);
        foreach ($oldSlides as $oldSlide) {
            if ($oldSlide === '.' || $oldSlide === '..') {
                continue;
            }
            $oldSlidePath = $dirs['slides_absolute'] . $oldSlide;
            if (is_file($oldSlidePath)) {
                @unlink($oldSlidePath);
            }
        }

        $pdftoppm = TrainingMediaHelper::getCommand($this->modx, 'training_pdftoppm_command', 'pdftoppm');
        $output = [];
        $code = 0;
        $prefixBase = 'course_' . (int)$course->get('id') . '_slide';
        $prefix = $dirs['slides_absolute'] . $prefixBase;
        $command = escapeshellcmd($pdftoppm)
            . ' -jpeg -r 150 '
            . escapeshellarg($pdfAbsolute)
            . ' '
            . escapeshellarg($prefix);

        if (!TrainingMediaHelper::runCommand($this->modx, $command, $output, $code)) {
            $course->set('presentation_status', 'error');
            $course->save();
            return $this->failure("Не удалось разобрать PDF на слайды\n" . implode("\n", $output));
        }

        $slides = glob($dirs['slides_absolute'] . $prefixBase . '-*.jpg');
        natcasesort($slides);
        $index = 1;
        foreach ($slides as $slidePath) {
            $target = $dirs['slides_absolute'] . TrainingMediaHelper::buildCourseSlideFilename($course, $index);
            if (realpath($slidePath) !== realpath($target)) {
                @rename($slidePath, $target);
            }
            $index++;
        }

        $slidesCount = count(glob($dirs['slides_absolute'] . '*.jpg'));

        $course->set('presentation_pdf', $dirs['pdf_web']);
        $course->set('slides_dir', $dirs['slides_web']);
        $course->set('presentation_status', 'ready');
        $course->save();

        $moduleQuery = $this->modx->newQuery('TrainingModule');
        $moduleQuery->leftJoin('modResource', 'Resource', 'Resource.id = TrainingModule.resource_id');
        $moduleQuery->where(['TrainingModule.course_id' => $courseId]);
        $moduleQuery->sortby('Resource.menuindex', 'ASC');
        $moduleQuery->sortby('TrainingModule.id', 'ASC');
        $modules = $this->modx->getCollection('TrainingModule', $moduleQuery);

        $slideFiles = glob($dirs['slides_absolute'] . '*.jpg');
        natcasesort($slideFiles);
        $slideFiles = array_values($slideFiles);
        $assigned = 0;

        foreach ($modules as $index => $module) {
            $moduleId = (int)$module->get('id');
            $existingCount = (int)$this->modx->getCount('TrainingModuleSlide', ['module_id' => $moduleId]);

            if ($existingCount <= 0 && isset($slideFiles[$index])) {
                $slidePath = $slideFiles[$index];
                $slideWeb = TrainingMediaHelper::fsPathToWeb($this->modx, $slidePath);
                $slideNo = TrainingMediaHelper::extractLastNumber(pathinfo($slidePath, PATHINFO_FILENAME));
                if ($slideNo <= 0) {
                    $slideNo = $index + 1;
                }

                /** @var TrainingModuleSlide $moduleSlide */
                $moduleSlide = $this->modx->newObject('TrainingModuleSlide');
                $moduleSlide->fromArray([
                    'module_id' => $moduleId,
                    'slide_no' => $slideNo,
                    'image' => $slideWeb,
                    'timecode_ms' => 0,
                    'is_active' => 1,
                ], '', true, true);
                if ($moduleSlide->save()) {
                    $existingCount++;
                    $assigned++;
                }
            }

            if ($existingCount > 0) {
                $module->set('presentation_status', 'ready');
            } else {
                $module->set('presentation_status', 'available');
            }
            $module->save();
        }

        return $this->success('Презентация обработана', [
            'source_presentation' => $course->get('source_presentation'),
            'presentation_pdf' => $course->get('presentation_pdf'),
            'slides_dir' => $course->get('slides_dir'),
            'slides_count' => $slidesCount,
            'modules_assigned' => $assigned,
        ]);
    }
}

return 'TrainingCoursePresentationProcessProcessor';

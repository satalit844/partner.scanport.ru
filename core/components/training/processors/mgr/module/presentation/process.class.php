<?php

require_once dirname(dirname(__DIR__)) . '/_media_helper.php';

class TrainingModulePresentationProcessProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $moduleId = (int)$this->getProperty('module_id');
        if ($moduleId <= 0) {
            return $this->failure('Не указан модуль');
        }

        /** @var TrainingModule $module */
        $module = $this->modx->getObject('TrainingModule', ['id' => $moduleId]);
        if (!$module) {
            return $this->failure('Модуль не найден');
        }

        $source = trim((string)$this->getProperty('source_presentation', $module->get('source_presentation')));
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

        $dirs = TrainingMediaHelper::resolveModulePresentationDirs($this->modx, $module);
        if (!TrainingMediaHelper::ensureDir($dirs['base_absolute']) || !TrainingMediaHelper::ensureDir($dirs['slides_absolute'])) {
            return $this->failure('Не удалось создать папки модуля для презентации');
        }

        $storedSourceAbsolute = $dirs['base_absolute'] . TrainingMediaHelper::buildModulePresentationSourceFilename($module, $extension);
        if (!TrainingMediaHelper::copyInto($sourceAbsolute, $storedSourceAbsolute)) {
            return $this->failure('Не удалось сохранить исходный файл презентации в папку модуля');
        }

        $module->set('source_presentation', TrainingMediaHelper::fsPathToWeb($this->modx, $storedSourceAbsolute));
        $module->set('presentation_status', 'processing');
        $module->save();

        $pdfAbsolute = $dirs['pdf_absolute'];
        if ($extension === 'pdf') {
            if (!TrainingMediaHelper::copyInto($storedSourceAbsolute, $pdfAbsolute)) {
                $module->set('presentation_status', 'error');
                $module->save();
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
                $module->set('presentation_status', 'error');
                $module->save();
                return $this->failure("LibreOffice не смог конвертировать презентацию в PDF\n" . implode("\n", $output));
            }

            $convertedPdf = $dirs['base_absolute'] . pathinfo($storedSourceAbsolute, PATHINFO_FILENAME) . '.pdf';
            if (!is_file($convertedPdf)) {
                $module->set('presentation_status', 'error');
                $module->save();
                return $this->failure('После конвертации не найден PDF файл');
            }

            if (realpath($convertedPdf) !== realpath($pdfAbsolute)) {
                @copy($convertedPdf, $pdfAbsolute);
            }
        }

        $oldSlides = @scandir($dirs['slides_absolute']);
        if (is_array($oldSlides)) {
            foreach ($oldSlides as $oldSlide) {
                if ($oldSlide === '.' || $oldSlide === '..') {
                    continue;
                }
                $oldSlidePath = $dirs['slides_absolute'] . $oldSlide;
                if (is_file($oldSlidePath)) {
                    @unlink($oldSlidePath);
                }
            }
        }

        $pdftoppm = TrainingMediaHelper::getCommand($this->modx, 'training_pdftoppm_command', 'pdftoppm');
        $output = [];
        $code = 0;
        $prefixBase = 'course_' . (int)$module->get('course_id') . '_module_' . (int)$module->get('id') . '_slide';
        $prefix = $dirs['slides_absolute'] . $prefixBase;
        $command = escapeshellcmd($pdftoppm)
            . ' -jpeg -r 150 '
            . escapeshellarg($pdfAbsolute)
            . ' '
            . escapeshellarg($prefix);

        if (!TrainingMediaHelper::runCommand($this->modx, $command, $output, $code)) {
            $module->set('presentation_status', 'error');
            $module->save();
            return $this->failure("Не удалось разобрать PDF на слайды\n" . implode("\n", $output));
        }

        $slides = glob($dirs['slides_absolute'] . $prefixBase . '-*.jpg');
        natcasesort($slides);
        $index = 1;
        foreach ($slides as $slidePath) {
            $target = $dirs['slides_absolute'] . TrainingMediaHelper::buildModuleSlideFilename($module, $index);
            if (realpath($slidePath) !== realpath($target)) {
                @rename($slidePath, $target);
            }
            $index++;
        }

        $slidesCount = count(glob($dirs['slides_absolute'] . '*.jpg'));

        $module->set('presentation_pdf', $dirs['pdf_web']);
        $module->set('slides_dir', $dirs['slides_web']);
        $module->set('presentation_status', $slidesCount > 0 ? 'ready' : 'none');
        $module->save();

        return $this->success('Презентация модуля обработана', [
            'source_presentation' => $module->get('source_presentation'),
            'presentation_pdf' => $module->get('presentation_pdf'),
            'slides_dir' => $module->get('slides_dir'),
            'slides_count' => $slidesCount,
        ]);
    }
}

return 'TrainingModulePresentationProcessProcessor';

<?php

require_once dirname(__FILE__) . '/_helpers.php';

class TrainingModuleSlideImportProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        if ($lessonId <= 0) {
            return $this->failure('Сначала выбери видео');
        }

        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) {
            return $this->failure('Видео не найдено');
        }

        $moduleId = (int)$lesson->get('module_id');
        $scan = TrainingModuleSlideHelper::scanLessonSlides($this->modx, $lessonId);
        if (empty($scan['dir']['exists'])) {
            $checked = !empty($scan['dir']['checked']) ? implode("\n", $scan['dir']['checked']) : '—';
            return $this->failure("Папка со слайдами модуля/урока не найдена.\nПроверено:\n" . $checked);
        }

        $c = $this->modx->newQuery('TrainingModuleSlide');
        $c->where(['lesson_id' => $lessonId]);

        /** @var TrainingModuleSlide[] $existingItems */
        $existingItems = $this->modx->getCollection('TrainingModuleSlide', $c);
        $existingByImage = [];
        $maxSlideNo = 0;

        foreach ($existingItems as $item) {
            $existingByImage[(string)$item->get('image')] = true;
            $maxSlideNo = max($maxSlideNo, (int)$item->get('slide_no'));
        }

        $created = 0;
        foreach ($scan['slides'] as $slide) {
            if (!empty($existingByImage[$slide['path']])) {
                continue;
            }

            /** @var TrainingModuleSlide $item */
            $item = $this->modx->newObject('TrainingModuleSlide');
            $item->set('module_id', $moduleId);
            $item->set('lesson_id', $lessonId);
            $item->set('image', $slide['path']);

            $slideNo = (int)$slide['slide_no'];
            if ($slideNo <= 0 || $slideNo <= $maxSlideNo) {
                $slideNo = $maxSlideNo + 1;
            }

            $item->set('slide_no', $slideNo);
            $item->set('timecode_ms', 0);
            $item->set('is_active', 1);
            $item->save();

            $existingByImage[$slide['path']] = true;
            $maxSlideNo = max($maxSlideNo, $slideNo);
            $created++;
        }

        $totalSlides = (int)$this->modx->getCount('TrainingModuleSlide', ['lesson_id' => $lessonId]);
        if ($totalSlides > 0) {
            $lesson->set('presentation_status', 'available');
            $lesson->save();
        }

        return $this->success('Импорт завершён', [
            'lesson_id' => $lessonId,
            'created' => $created,
            'total_slides' => $totalSlides,
            'available_dir' => $scan['dir']['web'],
        ]);
    }
}

return 'TrainingModuleSlideImportProcessor';

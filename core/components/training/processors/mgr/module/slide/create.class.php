<?php
require_once dirname(__FILE__) . '/_helpers.php';
class TrainingModuleSlideCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'TrainingModuleSlide';
    public $objectType = 'training.module.slide';
    public function checkPermissions(){return true;}
    protected function boolValue($value){return in_array((string)$value,['1','true','yes','on'],true)||$value===true||$value===1?1:0;}
    public function beforeSet()
    {
        $lessonId = (int)$this->getProperty('lesson_id');
        $image = trim((string)$this->getProperty('image'));
        $slideNo = (int)$this->getProperty('slide_no', 0);
        if ($lessonId <= 0) return 'Сначала выбери видео';
        if ($image === '') return 'Выберите изображение слайда';
        /** @var TrainingModuleLesson $lesson */
        $lesson = $this->modx->getObject('TrainingModuleLesson', ['id' => $lessonId]);
        if (!$lesson) return 'Видео не найдено';
        $moduleId = (int)$lesson->get('module_id');
        $image = TrainingModuleSlideHelper::normalizeWebPath($this->modx, $image);
        if ($slideNo <= 0) {
            $slideNo = $this->getNextSlideNo($lessonId, basename($image));
        }
        $this->setProperty('module_id', $moduleId);
        $this->setProperty('lesson_id', $lessonId);
        $this->setProperty('image', $image);
        $this->setProperty('slide_no', $slideNo);
        $this->setProperty('timecode_ms', max(0, (int)$this->getProperty('timecode_ms', 0)));
        $this->setProperty('is_active', $this->boolValue($this->getProperty('is_active', 1)));
        return parent::beforeSet();
    }
    protected function getNextSlideNo($lessonId, $filename)
    {
        $fromFilename = TrainingModuleSlideHelper::extractSlideNumber($filename);
        if ($fromFilename > 0) return $fromFilename;
        $c = $this->modx->newQuery('TrainingModuleSlide');
        $c->where(['lesson_id' => (int)$lessonId]);
        $c->sortby('slide_no', 'DESC');
        $c->limit(1);
        $last = $this->modx->getObject('TrainingModuleSlide', $c);
        return $last ? ((int)$last->get('slide_no') + 1) : 1;
    }
}
return 'TrainingModuleSlideCreateProcessor';

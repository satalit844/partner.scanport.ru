<?php

require_once __DIR__ . '/_helpers.php';

class TrainingModuleTestLinkCreateProcessor extends modObjectCreateProcessor
{
    public $classKey = 'TrainingTestLink';
    public $objectType = 'training.module.testlink';

    public function checkPermissions()
    {
        return true;
    }

    public function beforeSet()
    {
        $moduleId = (int)$this->getProperty('module_id');
        $courseId = (int)$this->getProperty('course_id');
        $linkType = TrainingModuleTestLinkHelper::normalizeLinkType($this->getProperty('link_type', 'test'));
        $activityId = $linkType === 'practice'
            ? (int)$this->getProperty('practice_id', $this->getProperty('usertest_test_id'))
            : (int)$this->getProperty('usertest_test_id');
        $sortOrder = (int)$this->getProperty('sort_order');

        if ($moduleId <= 0) {
            return 'Не указан модуль';
        }

        /** @var TrainingModule $module */
        $module = TrainingModuleTestLinkHelper::getModule($this->modx, $moduleId);
        if (!$module) {
            return 'Модуль не найден';
        }

        $resolvedCourseId = (int)$module->get('course_id');
        if ($resolvedCourseId <= 0) {
            return 'У модуля не найден курс';
        }
        if ($courseId > 0 && $courseId !== $resolvedCourseId) {
            return 'Модуль не принадлежит указанному курсу';
        }

        if ($activityId <= 0) {
            return $linkType === 'practice' ? 'Выберите практическое задание' : 'Выберите тест';
        }

        if ($linkType === 'practice') {
            if (!TrainingModuleTestLinkHelper::practiceBelongsToModule($this->modx, $activityId, $resolvedCourseId, $moduleId)) {
                return 'Практическое задание не найдено или относится к другому модулю';
            }
        } else {
            if (!TrainingModuleTestLinkHelper::ensureUserTestPackage($this->modx)) {
                return 'Не удалось подключить компонент usertest';
            }

            $test = TrainingModuleTestLinkHelper::getUserTestObject($this->modx, $activityId);
            if (!$test) {
                return 'Выбранный тест не найден';
            }
        }

        if (TrainingModuleTestLinkHelper::findDuplicate($this->modx, $resolvedCourseId, $moduleId, $activityId, $linkType)) {
            return 'Такая привязка уже существует';
        }

        if ($sortOrder <= 0) {
            $sortOrder = TrainingModuleTestLinkHelper::getNextSortOrder($this->modx, $moduleId);
        }

        $this->setProperty('course_id', $resolvedCourseId);
        $this->setProperty('module_id', $moduleId);
        $this->setProperty('usertest_test_id', $activityId);
        $this->setProperty('link_type', $linkType);
        $this->setProperty('sort_order', $sortOrder);
        $this->setProperty('is_required', TrainingModuleTestLinkHelper::boolValue($this->getProperty('is_required', 1), 1));
        $this->setProperty('max_attempts', max(0, (int)$this->getProperty('max_attempts', $linkType === 'practice' ? 5 : 0)));
        $this->setProperty('min_pass_percent', max(0, min(100, (float)$this->getProperty('min_pass_percent', 0))));
        $this->setProperty(
            'block_next_module_until_passed',
            TrainingModuleTestLinkHelper::boolValue($this->getProperty('block_next_module_until_passed', 0), 0)
        );
        $this->setProperty('createdon', date('Y-m-d H:i:s'));

        return parent::beforeSet();
    }
}

return 'TrainingModuleTestLinkCreateProcessor';

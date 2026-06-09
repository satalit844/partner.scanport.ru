<?php

class TrainingModuleTestLinkHelper
{
    public static function ensureUserTestPackage(modX $modx)
    {
        static $loaded = false;
        if ($loaded) {
            return true;
        }

        $corePath = $modx->getOption(
            'usertest_core_path',
            null,
            $modx->getOption('core_path') . 'components/usertest/'
        );
        $modelPath = rtrim($corePath, '/\\') . '/model/';

        if (!is_dir($modelPath)) {
            return false;
        }

        $modx->addPackage('usertest', $modelPath);
        $loaded = true;

        return true;
    }

    public static function normalizeLinkType($value)
    {
        $value = trim((string)$value);
        return $value === 'practice' ? 'practice' : 'test';
    }

    public static function getLinkTypeLabel($value)
    {
        return self::normalizeLinkType($value) === 'practice' ? 'Практическая работа' : 'Тест';
    }

    public static function boolValue($value, $default = 0)
    {
        if ($value === null || $value === '') {
            return (int)$default;
        }

        return in_array((string)$value, ['1', 'true', 'yes', 'on'], true) || $value === true || $value === 1
            ? 1
            : 0;
    }

    public static function plainTable(modX $modx, $name)
    {
        $prefix = (string)$modx->getOption('table_prefix');
        $candidates = array(
            $prefix . $name,
            $prefix . '_' . $name,
            'modx_' . $name,
        );

        foreach ($candidates as $table) {
            $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
            if ($table === '') {
                continue;
            }

            $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
            if ($stmt && $stmt->fetchColumn()) {
                return $table;
            }
        }

        return preg_replace('/[^a-zA-Z0-9_]/', '', $prefix . $name);
    }

    public static function getModule(modX $modx, $moduleId)
    {
        $moduleId = (int)$moduleId;
        if ($moduleId <= 0) {
            return null;
        }

        return $modx->getObject('TrainingModule', ['id' => $moduleId]);
    }

    public static function resolveCourseId(modX $modx, $moduleId, $courseId = 0)
    {
        $courseId = (int)$courseId;
        if ($courseId > 0) {
            return $courseId;
        }

        $module = self::getModule($modx, $moduleId);
        return $module ? (int)$module->get('course_id') : 0;
    }

    public static function getUserTestObject(modX $modx, $testId)
    {
        $testId = (int)$testId;
        if ($testId <= 0) {
            return null;
        }

        if (!self::ensureUserTestPackage($modx)) {
            return null;
        }

        return $modx->getObject('UserTestTests', ['id' => $testId]);
    }

    public static function getPracticeObject(modX $modx, $practiceId)
    {
        $practiceId = (int)$practiceId;
        if ($practiceId <= 0) {
            return array();
        }

        $table = self::plainTable($modx, 'training_practices');
        $stmt = $modx->prepare("SELECT * FROM `{$table}` WHERE `id` = :id LIMIT 1");
        if (!$stmt || !$stmt->execute(array(':id' => $practiceId))) {
            return array();
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : array();
    }

    public static function getPracticeDisplay(modX $modx, $practiceId)
    {
        $practice = self::getPracticeObject($modx, $practiceId);
        if (!$practice) {
            return '#' . (int)$practiceId . ' Практическое задание не найдено';
        }

        $title = trim((string)$practice['title']);
        if ($title === '') {
            $title = 'Практическое задание #' . (int)$practice['id'];
        }

        return '#' . (int)$practice['id'] . ' ' . $title;
    }

    public static function practiceBelongsToModule(modX $modx, $practiceId, $courseId, $moduleId)
    {
        $practice = self::getPracticeObject($modx, $practiceId);
        if (!$practice) {
            return false;
        }

        return (int)$practice['course_id'] === (int)$courseId && (int)$practice['module_id'] === (int)$moduleId;
    }

    public static function getNextSortOrder(modX $modx, $moduleId)
    {
        $moduleId = (int)$moduleId;
        $q = $modx->newQuery('TrainingTestLink');
        $q->where(['module_id' => $moduleId]);
        $q->sortby('sort_order', 'DESC');
        $q->sortby('id', 'DESC');
        $q->limit(1);

        /** @var TrainingTestLink $last */
        $last = $modx->getObject('TrainingTestLink', $q);
        return $last ? ((int)$last->get('sort_order') + 1) : 1;
    }

    public static function findDuplicate(modX $modx, $courseId, $moduleId, $activityId, $linkType, $excludeId = 0)
    {
        $criteria = [
            'course_id' => (int)$courseId,
            'module_id' => (int)$moduleId,
            'usertest_test_id' => (int)$activityId,
            'link_type' => self::normalizeLinkType($linkType),
        ];
        if ((int)$excludeId > 0) {
            $criteria['id:!='] = (int)$excludeId;
        }

        return $modx->getObject('TrainingTestLink', $criteria);
    }
}

<?php

require_once dirname(__DIR__) . '/testlink/_helpers.php';

class TrainingModuleTestLinksGetListProcessor extends modObjectGetListProcessor
{
    public $classKey = 'TrainingTestLink';
    public $objectType = 'training.module.testlink';
    public $defaultSortField = 'sort_order';
    public $defaultSortDirection = 'ASC';

    public function initialize()
    {
        TrainingModuleTestLinkHelper::ensureUserTestPackage($this->modx);
        return parent::initialize();
    }

    public function prepareQueryBeforeCount(xPDOQuery $c)
    {
        $moduleId = (int)$this->getProperty('module_id');
        $courseId = (int)$this->getProperty('course_id');
        $query = trim((string)$this->getProperty('query', ''));

        if ($moduleId <= 0) {
            $c->where(['1 = 0']);
            return $c;
        }

        $c->where(['module_id' => $moduleId]);
        if ($courseId > 0) {
            $c->where(['course_id' => $courseId]);
        }

        if ($query !== '') {
            $or = [];
            if (is_numeric($query)) {
                $or['id'] = (int)$query;
                $or['OR:usertest_test_id'] = (int)$query;
            }
            $normalizedType = TrainingModuleTestLinkHelper::normalizeLinkType($query);
            if ($normalizedType === 'test' || $normalizedType === 'practice') {
                $or['OR:link_type'] = $normalizedType;
            }
            if (!empty($or)) {
                $c->where($or);
            }
        }

        return $c;
    }

    public function prepareQueryAfterCount(xPDOQuery $c)
    {
        $sort = trim((string)$this->getProperty('sort', ''));
        $dir = strtoupper(trim((string)$this->getProperty('dir', 'ASC')));
        if ($dir !== 'DESC') {
            $dir = 'ASC';
        }

        if ($sort === '' || $sort === 'sort_order') {
            $c->sortby('sort_order', 'ASC');
            $c->sortby('id', 'ASC');
        } else {
            $c->sortby($sort, $dir);
            if ($sort !== 'sort_order') {
                $c->sortby('sort_order', 'ASC');
                $c->sortby('id', 'ASC');
            }
        }

        return $c;
    }

    public function prepareRow(xPDOObject $object)
    {
        $row = $object->toArray();
        $linkType = TrainingModuleTestLinkHelper::normalizeLinkType($row['link_type']);
        $activityId = (int)$row['usertest_test_id'];

        $row['course_id'] = (int)$row['course_id'];
        $row['module_id'] = (int)$row['module_id'];
        $row['usertest_test_id'] = $activityId;
        $row['practice_id'] = $linkType === 'practice' ? $activityId : 0;
        $row['sort_order'] = (int)$row['sort_order'];
        $row['is_required'] = (int)!empty($row['is_required']);
        $row['max_attempts'] = (int)$row['max_attempts'];
        $row['min_pass_percent'] = (float)$row['min_pass_percent'];
        $row['block_next_module_until_passed'] = (int)!empty($row['block_next_module_until_passed']);
        $row['link_type'] = $linkType;
        $row['link_type_label'] = TrainingModuleTestLinkHelper::getLinkTypeLabel($linkType);

        if ($linkType === 'practice') {
            $practice = TrainingModuleTestLinkHelper::getPracticeObject($this->modx, $activityId);
            if ($practice) {
                $title = trim((string)$practice['title']);
                if ($title === '') {
                    $title = 'Практическое задание #' . $activityId;
                }
                $row['test_name'] = $title;
                $row['test_description'] = trim((string)$practice['description']);
                $row['test_active'] = (int)$practice['active'] === 1 ? 1 : 0;
                $row['test_display'] = '#' . $activityId . ' ' . $title;
                $row['practice_title'] = $title;
            } else {
                $row['test_name'] = 'Практическое задание не найдено';
                $row['test_description'] = '';
                $row['test_active'] = 0;
                $row['test_display'] = '#' . $activityId . ' Практическое задание не найдено';
                $row['practice_title'] = '';
            }

            return $row;
        }

        $test = TrainingModuleTestLinkHelper::getUserTestObject($this->modx, $activityId);
        if ($test) {
            $row['test_name'] = trim((string)$test->get('name'));
            $row['test_description'] = trim((string)$test->get('description'));
            $row['test_active'] = (int)$test->get('active') === 1 ? 1 : 0;
            $row['test_display'] = '#' . $activityId . ' ' . $row['test_name'];
        } else {
            $row['test_name'] = 'Тест не найден';
            $row['test_description'] = '';
            $row['test_active'] = 0;
            $row['test_display'] = '#' . $activityId . ' Тест не найден';
        }

        return $row;
    }
}

return 'TrainingModuleTestLinksGetListProcessor';

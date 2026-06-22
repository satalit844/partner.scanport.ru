<?php

require_once dirname(__DIR__) . '/_helpers.php';

class TrainingCourseProgressUsersGetListProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        if ($courseId <= 0) {
            return trainingProgressCmpListResponse($this, array(), 0);
        }

        try {
            $training = new Training($this->modx);
            $progressService = new TrainingProgressService($this->modx, $training);

            /*
             * Важно: только чтение.
             * Не вызываем syncUserCourses() при открытии вкладки, так как она
             * меняет TrainingUserCourse и отзывает записи, которых нет в текущем
             * доступе. Для selector достаточно текущей карты назначений.
             */
            $accessMap = $progressService->collectAccessibleUserMap($courseId);
            $userIds = array_keys($accessMap);

            if (empty($userIds)) {
                trainingProgressCmpLog($this->modx, 'users_getlist', array(
                    'course_id' => $courseId,
                    'found' => 0,
                    'source' => 'collectAccessibleUserMap',
                ));

                return trainingProgressCmpListResponse($this, array(), 0);
            }

            $userTable = str_replace('`', '', $this->modx->getTableName('modUser'));
            $profileTable = str_replace('`', '', $this->modx->getTableName('modUserProfile'));
            $userCourseTable = str_replace('`', '', $this->modx->getTableName('TrainingUserCourse'));

            $params = array(':course_id' => $courseId);
            $holders = array();

            foreach (array_values($userIds) as $index => $userId) {
                $key = ':user_' . $index;
                $holders[] = $key;
                $params[$key] = (int)$userId;
            }

            $sql = 'SELECT '
                . 'u.`id` AS `user_id`, '
                . 'u.`username` AS `username`, '
                . 'p.`fullname` AS `fullname`, '
                . 'p.`email` AS `email`, '
                . 'uc.`status` AS `status`, '
                . 'uc.`progress_percent` AS `progress_percent`, '
                . 'uc.`completed_modules` AS `completed_modules`, '
                . 'uc.`total_modules` AS `total_modules`, '
                . 'uc.`current_module_id` AS `current_module_id` '
                . 'FROM `' . $userTable . '` u '
                . 'LEFT JOIN `' . $profileTable . '` p ON p.`internalKey` = u.`id` '
                . 'LEFT JOIN `' . $userCourseTable . '` uc '
                . 'ON uc.`course_id` = :course_id AND uc.`user_id` = u.`id` '
                . 'WHERE u.`id` IN (' . implode(',', $holders) . ') '
                . 'ORDER BY COALESCE(NULLIF(p.`fullname`, ""), u.`username`) ASC, u.`id` ASC';

            $stmt = $this->modx->prepare($sql);
            if (!$stmt || !$stmt->execute($params)) {
                $error = $stmt && method_exists($stmt, 'errorInfo')
                    ? implode(' | ', $stmt->errorInfo())
                    : 'prepare/execute failed';
                throw new RuntimeException($error);
            }

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $query = mb_strtolower(trim((string)$this->getProperty('query', '')), 'UTF-8');

            foreach ($rows as &$row) {
                $userId = (int)$row['user_id'];
                $row['user_id'] = $userId;
                $row['display_name'] = trim((string)$row['fullname']);

                if ($row['display_name'] === '') {
                    $row['display_name'] = trim((string)$row['username']);
                }
                if ($row['display_name'] === '') {
                    $row['display_name'] = 'Пользователь #' . $userId;
                }

                $row['email'] = (string)$row['email'];
                $row['username'] = (string)$row['username'];
                $row['status'] = trim((string)$row['status']) !== ''
                    ? (string)$row['status']
                    : 'assigned';
                $row['progress_percent'] = (float)$row['progress_percent'];
                $row['completed_modules'] = (int)$row['completed_modules'];
                $row['total_modules'] = (int)$row['total_modules'];
                $row['current_module_id'] = (int)$row['current_module_id'];
                $row['current_module_label'] = '—';
                $row['current_lesson_label'] = '—';
                $row['access_role'] = isset($accessMap[$userId]['access_role'])
                    ? (string)$accessMap[$userId]['access_role']
                    : 'employee';
            }
            unset($row);

            if ($query !== '') {
                $rows = array_values(array_filter($rows, function ($row) use ($query) {
                    $haystack = mb_strtolower(
                        (string)$row['display_name'] . ' ' . (string)$row['email'] . ' ' . (string)$row['username'],
                        'UTF-8'
                    );

                    return mb_strpos($haystack, $query, 0, 'UTF-8') !== false;
                }));
            }

            trainingProgressCmpLog($this->modx, 'users_getlist', array(
                'course_id' => $courseId,
                'found' => count($rows),
                'source' => 'collectAccessibleUserMap',
            ));

            $total = count($rows);
            $start = max(0, (int)$this->getProperty('start', 0));
            $limit = max(0, (int)$this->getProperty('limit', 0));

            if ($limit > 0) {
                $rows = array_slice($rows, $start, $limit);
            }

            return trainingProgressCmpListResponse($this, $rows, $total);
        } catch (Throwable $e) {
            trainingProgressCmpLog($this->modx, 'users_getlist_error', array(
                'course_id' => $courseId,
                'message' => $e->getMessage(),
            ));

            return $this->failure($e->getMessage());
        }
    }
}

return 'TrainingCourseProgressUsersGetListProcessor';

<?php
/**
 * trainingHasTrainingMenuAccess
 * Возвращает 1, если пользователю нужно показывать расширенное меню обучения.
 * Возвращает 0, если нужно показывать обычный пункт "Обучение" без подпунктов.
 *
 * Логика без жёстких user_id:
 * - sudo/admin видит расширенное меню;
 * - пользователь с активными назначенными курсами видит расширенное меню;
 * - director/manager/admin по training_course_access или training_user_courses видит расширенное меню.
 */
/** @var modX $modx */

if (!$modx->user || !(int)$modx->user->get('id')) {
    return '0';
}

$userId = (int)$modx->user->get('id');

if ((int)$modx->user->get('sudo') === 1) {
    return '1';
}

if (method_exists($modx->user, 'isMember')) {
    if ($modx->user->isMember('Administrator') || $modx->user->isMember('Администратор')) {
        return '1';
    }
}

$prefix = (string)$modx->getOption('table_prefix', null, '');

$tables = array(
    'user_courses' => array(
        $prefix . 'partnerstraining_user_courses',
        $prefix . 'training_user_courses',
    ),
    'course_access' => array(
        $prefix . 'partnerstraining_course_access',
        $prefix . 'training_course_access',
    ),
);

$exists = function ($table) use ($modx) {
    $table = str_replace('`', '', (string)$table);
    if ($table === '') {
        return false;
    }
    $stmt = $modx->query('SHOW TABLES LIKE ' . $modx->quote($table));
    return $stmt && $stmt->fetch(PDO::FETCH_NUM);
};

$firstTable = function (array $candidates) use ($exists) {
    foreach ($candidates as $table) {
        if ($exists($table)) {
            return str_replace('`', '', $table);
        }
    }
    return '';
};

$userCourses = $firstTable($tables['user_courses']);
if ($userCourses !== '') {
    $sql = 'SELECT COUNT(*) FROM `' . $userCourses . '` '
        . 'WHERE `user_id` = :user_id '
        . 'AND (`status` IS NULL OR `status` <> "revoked")';
    $stmt = $modx->prepare($sql);
    if ($stmt && $stmt->execute(array(':user_id' => $userId)) && (int)$stmt->fetchColumn() > 0) {
        return '1';
    }
}

$courseAccess = $firstTable($tables['course_access']);
if ($courseAccess !== '') {
    $sql = 'SELECT COUNT(*) FROM `' . $courseAccess . '` '
        . 'WHERE `is_active` = 1 '
        . 'AND `access_role` IN ("director", "manager", "admin") '
        . 'AND ((`principal_type` = "user" AND `principal_id` = :uid1) OR `user_id` = :uid2 OR `assigned_by` = :uid3)';
    $stmt = $modx->prepare($sql);
    if ($stmt && $stmt->execute(array(':uid1' => $userId, ':uid2' => $userId, ':uid3' => $userId)) && (int)$stmt->fetchColumn() > 0) {
        return '1';
    }
}

return '0';

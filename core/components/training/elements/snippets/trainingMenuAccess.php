<?php
/**
 * Returns JSON with training menu mode for current web user.
 *
 * Menu modes:
 * - none     — обычный пункт "Обучение" без подпунктов
 * - employee — Все курсы / Мои курсы / Сертификаты / История
 * - manager  — Все курсы / Управление курсами / Управление сертификатами / Управление историей
 * - director — все пункты обучения
 *
 * Важно:
 * - активный director-доступ к курсу = директор может и проходить курс, и управлять;
 * - выключенный / недействующий director-доступ к курсу = курс проходить нельзя,
 *   но управленческие разделы остаются доступны;
 * - связь директор -> сотрудник тоже даёт управленческие разделы.
 */
if (!isset($modx) || !$modx instanceof modX) {
    return json_encode(['mode' => 'none'], JSON_UNESCAPED_UNICODE);
}

$userId = $modx->user ? (int)$modx->user->get('id') : 0;
if ($userId <= 0) {
    return json_encode(['mode' => 'none'], JSON_UNESCAPED_UNICODE);
}

$prefix = $modx->getOption('table_prefix');
$courseAccessTable = $prefix . 'training_course_access';
$userCoursesTable = $prefix . 'training_user_courses';
$managerLinkTable = $prefix . 'training_user_manager_link';

$hasActiveDirectorCourse = false;
$hasAnyDirectorCourse = false;
$hasActiveEmployeeCourse = false;
$hasUserCourse = false;
$hasManagerLink = false;
$hasEmployeeLink = false;

$now = date('Y-m-d H:i:s');

try {
    $sql = "
        SELECT
            access_role,
            is_active,
            active_from,
            active_to
        FROM {$courseAccessTable}
        WHERE principal_type = 'user'
          AND principal_id = :user_id
    ";
    $stmt = $modx->prepare($sql);
    if ($stmt && $stmt->execute([':user_id' => $userId])) {
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $role = strtolower(trim((string)($row['access_role'] ?? '')));
            $isActiveFlag = (int)($row['is_active'] ?? 0) === 1;
            $from = trim((string)($row['active_from'] ?? ''));
            $to = trim((string)($row['active_to'] ?? ''));

            $fromOk = $from === '' || $from === '0000-00-00 00:00:00' || $from <= $now;
            $toOk = $to === '' || $to === '0000-00-00 00:00:00' || $to >= $now;
            $isCurrentlyActive = $isActiveFlag && $fromOk && $toOk;

            if ($role === 'director') {
                $hasAnyDirectorCourse = true;
                if ($isCurrentlyActive) {
                    $hasActiveDirectorCourse = true;
                }
            } elseif ($role === 'employee') {
                if ($isCurrentlyActive) {
                    $hasActiveEmployeeCourse = true;
                }
            }
        }
    }
} catch (Throwable $e) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[trainingMenuAccess] course_access: ' . $e->getMessage());
}

try {
    $sql = "
        SELECT COUNT(*)
        FROM {$userCoursesTable}
        WHERE user_id = :user_id
          AND status <> 'revoked'
    ";
    $stmt = $modx->prepare($sql);
    if ($stmt && $stmt->execute([':user_id' => $userId])) {
        $hasUserCourse = ((int)$stmt->fetchColumn()) > 0;
    }
} catch (Throwable $e) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[trainingMenuAccess] user_courses: ' . $e->getMessage());
}

try {
    $sql = "
        SELECT
            SUM(CASE WHEN manager_user_id = :manager_id THEN 1 ELSE 0 END) AS manager_count,
            SUM(CASE WHEN employee_user_id = :employee_id THEN 1 ELSE 0 END) AS employee_count
        FROM {$managerLinkTable}
        WHERE is_active = 1
          AND (manager_user_id = :manager_id2 OR employee_user_id = :employee_id2)
    ";
    $stmt = $modx->prepare($sql);
    if ($stmt && $stmt->execute([
        ':manager_id' => $userId,
        ':employee_id' => $userId,
        ':manager_id2' => $userId,
        ':employee_id2' => $userId,
    ])) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hasManagerLink = !empty($row['manager_count']);
        $hasEmployeeLink = !empty($row['employee_count']);
    }
} catch (Throwable $e) {
    $modx->log(modX::LOG_LEVEL_ERROR, '[trainingMenuAccess] manager_link: ' . $e->getMessage());
}

$isAdmin = false;
try {
    $isAdmin = $modx->user && ($modx->user->sudo || $modx->user->isMember('Administrator'));
} catch (Throwable $e) {
    $isAdmin = false;
}

if ($isAdmin || $hasActiveDirectorCourse) {
    $mode = 'director';
} elseif ($hasAnyDirectorCourse || $hasManagerLink) {
    // Director row exists, but access is disabled / expired / not started:
    // no personal course passing, but management pages remain available.
    $mode = 'manager';
} elseif ($hasActiveEmployeeCourse || $hasEmployeeLink || $hasUserCourse) {
    $mode = 'employee';
} else {
    $mode = 'none';
}

return json_encode([
    'mode' => $mode,
    'is_admin' => $isAdmin ? 1 : 0,
    'has_active_director_course' => $hasActiveDirectorCourse ? 1 : 0,
    'has_any_director_course' => $hasAnyDirectorCourse ? 1 : 0,
    'has_active_employee_course' => $hasActiveEmployeeCourse ? 1 : 0,
    'has_user_course' => $hasUserCourse ? 1 : 0,
    'has_manager_link' => $hasManagerLink ? 1 : 0,
    'has_employee_link' => $hasEmployeeLink ? 1 : 0,
], JSON_UNESCAPED_UNICODE);

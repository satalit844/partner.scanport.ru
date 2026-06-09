<?php

require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/model/training/training.class.php';
require_once dirname(dirname(dirname(dirname(__DIR__)))) . '/model/training/services/trainingprogress.class.php';

class TrainingCourseAccessHelper
{
    public static function getTraining(modX $modx)
    {
        return new Training($modx);
    }

    public static function getProgressService(modX $modx)
    {
        return new TrainingProgressService($modx, self::getTraining($modx));
    }

    public static function normalizeDateTime($value)
    {
        $value = trim((string)$value);
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $value = str_replace('T', ' ', $value);
        if (preg_match('#^\d{4}-\d{2}-\d{2}$#', $value)) {
            return $value . ' 00:00:00';
        }

        if (preg_match('#^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$#', $value)) {
            return $value . ':00';
        }

        return $value;
    }

    public static function getPrincipalData(modX $modx, $principalType, $principalId)
    {
        $principalType = (string)$principalType;
        $principalId = (int)$principalId;

        $data = [
            'principal_name' => '—',
            'principal_label' => '—',
        ];

        if ($principalType === 'user') {
            /** @var modUser $user */
            $user = $modx->getObject('modUser', ['id' => $principalId]);
            if ($user) {
                $profile = $user->getOne('Profile');
                $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
                $email = $profile ? trim((string)$profile->get('email')) : '';
                $username = (string)$user->get('username');
                $data['principal_name'] = $fullname !== '' ? $fullname : $username;
                $label = '#' . $principalId . ' ' . $username;
                if ($fullname !== '') {
                    $label .= ' (' . $fullname . ')';
                }
                if ($email !== '') {
                    $label .= ' [' . $email . ']';
                }
                $data['principal_label'] = $label;
            }
            return $data;
        }

        if ($principalType === 'group') {
            /** @var modUserGroup $group */
            $group = $modx->getObject('modUserGroup', ['id' => $principalId]);
            if ($group) {
                $name = (string)$group->get('name');
                $data['principal_name'] = $name;
                $data['principal_label'] = '#' . $principalId . ' ' . $name;
            }
        }

        return $data;
    }

    public static function getAssignedByLabel(modX $modx, $userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return '—';
        }

        /** @var modUser $user */
        $user = $modx->getObject('modUser', ['id' => $userId]);
        if (!$user) {
            return '—';
        }

        $profile = $user->getOne('Profile');
        $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
        $username = (string)$user->get('username');

        return $fullname !== '' ? ($fullname . ' [' . $username . ']') : $username;
    }
}

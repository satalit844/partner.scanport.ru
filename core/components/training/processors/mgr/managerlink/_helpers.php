<?php

require_once dirname(dirname(dirname(__DIR__))) . '/model/training/training.class.php';
require_once dirname(dirname(dirname(__DIR__))) . '/model/training/services/trainingprogress.class.php';

class TrainingManagerLinkHelper
{
    public static function getTraining(modX $modx)
    {
        return new Training($modx);
    }

    public static function getProgressService(modX $modx)
    {
        return new TrainingProgressService($modx, self::getTraining($modx));
    }

    public static function getUserLabel(modX $modx, $userId)
    {
        $userId = (int)$userId;
        if ($userId <= 0) {
            return '—';
        }

        $user = $modx->getObject('modUser', ['id' => $userId]);
        if (!$user) {
            return '—';
        }

        $profile = $user->getOne('Profile');
        $fullname = $profile ? trim((string)$profile->get('fullname')) : '';
        $email = $profile ? trim((string)$profile->get('email')) : '';
        $username = trim((string)$user->get('username'));

        $label = '#' . $userId . ' ' . $username;
        if ($fullname !== '') {
            $label .= ' (' . $fullname . ')';
        }
        if ($email !== '') {
            $label .= ' [' . $email . ']';
        }

        return $label;
    }
}

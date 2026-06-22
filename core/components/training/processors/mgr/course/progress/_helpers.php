<?php

/**
 * Общие функции processors/mgr/course/progress/.
 */

$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';

require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingprogress.class.php';
require_once $corePath . 'model/training/services/trainingprogressassignment.class.php';

function trainingProgressCmpGetService(modX $modx)
{
    static $services = array();
    $key = spl_object_hash($modx);

    if (!isset($services[$key])) {
        $services[$key] = new TrainingProgressAssignmentService($modx);
    }

    return $services[$key];
}

function trainingProgressCmpCourseId(modProcessor $processor)
{
    $courseId = (int)$processor->getProperty('course_id', $processor->getProperty('id', 0));
    if ($courseId <= 0) {
        return 0;
    }

    /** @var TrainingCourse $course */
    $course = $processor->modx->getObject('TrainingCourse', array('id' => $courseId));
    return $course ? $courseId : 0;
}

function trainingProgressCmpListResponse(modProcessor $processor, array $rows, $total = null)
{
    if ($total === null) {
        $total = count($rows);
    }

    return $processor->modx->toJSON(array(
        'success' => true,
        'total' => (int)$total,
        'results' => array_values($rows),
        'object' => array(
            'total' => (int)$total,
            'results' => array_values($rows),
        ),
    ));
}

function trainingProgressCmpActorId(modX $modx)
{
    return ($modx->user && (int)$modx->user->get('id'))
        ? (int)$modx->user->get('id')
        : 0;
}

function trainingProgressCmpLog(modX $modx, $event, array $data = array())
{
    $modx->log(
        modX::LOG_LEVEL_ERROR,
        '[trainingCourseProgress] ' . $event . "\n" . print_r($data, true)
    );
}

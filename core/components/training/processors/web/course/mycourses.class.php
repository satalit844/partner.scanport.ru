<?php

require_once dirname(dirname(__FILE__)) . '/_helpers.php';

class TrainingWebCourseMyCoursesProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    protected function formatDateValue($value)
    {
        if ($value === null || $value === '' || $value === '0000-00-00 00:00:00') {
            return '—';
        }

        $ts = strtotime((string)$value);
        if (!$ts) {
            return '—';
        }

        return date('d.m.Y', $ts);
    }

    protected function buildState($status, $progressPercent)
    {
        $status = (string)$status;
        $progressPercent = (float)$progressPercent;

        if ($status === 'completed' || $progressPercent >= 100) {
            return array(
                'state' => 'done',
                'item_class' => 'is-done',
                'status_text' => 'Курс завершен',
                'status_class' => 'label-chip--green',
            );
        }

        if ($status === 'in_progress' || $progressPercent > 0) {
            return array(
                'state' => 'progress',
                'item_class' => 'is-progress',
                'status_text' => 'В процессе',
                'status_class' => 'label-chip--purple',
            );
        }

        return array(
            'state' => 'new',
            'item_class' => 'is-new',
            'status_text' => 'Не начат',
            'status_class' => 'label-chip--blue',
        );
    }

    protected function getCourseStats(TrainingProgressService $service, $courseId, $userId)
    {
        $videoStats = TrainingWebHelper::getCourseVideoStats($this->modx, $service, (int)$courseId, (int)$userId);
        $activityStats = $service->getCourseActivityStats((int)$courseId, (int)$userId);

        return array(
            'total_videos' => (int)$videoStats['total_videos'],
            'completed_videos' => (int)$videoStats['completed_videos'],
            'started_videos' => (int)$videoStats['started_videos'],
            'total_tests' => (int)$activityStats['tests_total'],
            'passed_tests' => (int)$activityStats['tests_passed'],
            'started_tests' => (int)$activityStats['tests_started'],
            'practices_total' => (int)$activityStats['practices_total'],
            'practices_completed' => (int)$activityStats['practices_completed'],
            'practices_started' => (int)$activityStats['practices_started'],
        );
    }

    public function process()
    {
        if ($failure = TrainingWebHelper::requireAuth($this)) {
            return $failure;
        }

        $userId = (int)$this->modx->user->get('id');
        $limit = max(0, (int)$this->getProperty('limit', 0));
        $start = max(0, (int)$this->getProperty('start', 0));
        $includeRevoked = (int)$this->getProperty('include_revoked', 0) === 1;
        $recalculate = (int)$this->getProperty('recalculate', 1) === 1;

        $service = TrainingWebHelper::getProgressService($this->modx);
        $rows = $service->getMyCourses($userId, array(
            'limit' => $limit,
            'start' => $start,
            'include_revoked' => $includeRevoked,
            'recalculate' => $recalculate,
        ));

        $results = array();

        foreach ($rows as $row) {
            $courseId = (int)$row['course_id'];
            $resourceId = (int)$row['resource_id'];

            if ($courseId <= 0 || $resourceId <= 0) {
                continue;
            }

            if (empty($row['course_is_active']) || empty($row['published'])) {
                continue;
            }

            /** @var modResource|null $resource */
            $resource = $this->modx->getObject('modResource', array('id' => $resourceId));
            if (!$resource) {
                continue;
            }

            $image = trim((string)$resource->getTVValue('image_curse'));
            $descText = trim((string)$resource->getTVValue('desc'));
            $durationText = trim((string)$resource->getTVValue('time_curse'));

            $url = $this->modx->makeUrl(
                $resourceId,
                $resource->get('context_key'),
                '',
                'full'
            );

            $stats = $this->getCourseStats($service, $courseId, $userId);
            $totalTrackItems = (int)$stats['total_videos'] + (int)$stats['practices_total'] + (int)$stats['total_tests'];
            $completedTrackItems = (int)$stats['completed_videos'] + (int)$stats['practices_completed'] + (int)$stats['passed_tests'];
            $progressPercent = $totalTrackItems > 0
                ? (int)round(($completedTrackItems / $totalTrackItems) * 100)
                : (int)round((float)$row['progress_percent']);

            $status = (string)$row['status'];
            if ($totalTrackItems > 0 && $completedTrackItems >= $totalTrackItems) {
                $status = 'completed';
            } elseif ($progressPercent > 0 && $status === 'assigned') {
                $status = 'in_progress';
            }

            $state = $this->buildState($status, $progressPercent);
            $completedModules = (int)$row['completed_modules'];
            $totalModules = max((int)$row['total_modules'], (int)$this->modx->getCount('TrainingModule', array(
                'course_id' => $courseId,
                'is_active' => 1,
            )));

            $title = trim((string)($row['longtitle'] ?: $row['pagetitle']));
            if ($title === '') {
                $title = trim((string)$resource->get('pagetitle'));
            }

            $results[] = array(
                'course_id' => $courseId,
                'resource_id' => $resourceId,
                'title' => $title,
                'pagetitle' => (string)$row['pagetitle'],
                'description' => $descText !== '' ? $descText : (string)$row['description'],
                'url' => $url,
                'image' => $image,
                'duration_text' => $durationText,
                'access_role' => (string)$row['access_role'],
                'status' => $status,
                'progress_percent' => $progressPercent,
                'progress_percent_text' => $progressPercent . '%',
                'completed_modules' => $completedModules,
                'total_modules' => $totalModules,
                'startedon' => $row['startedon'],
                'startedon_formatted' => $this->formatDateValue($row['startedon']),
                'completedon' => $row['completedon'],
                'completedon_formatted' => $this->formatDateValue($row['completedon']),
                'last_activity' => $row['last_activity'],
                'last_activity_formatted' => $this->formatDateValue($row['last_activity']),
                'videos_completed' => (int)$stats['completed_videos'],
                'videos_total' => (int)$stats['total_videos'],
                'videos_text' => ((int)$stats['completed_videos']) . '/' . ((int)$stats['total_videos']),
                'practices_completed' => (int)$stats['practices_completed'],
                'practices_total' => (int)$stats['practices_total'],
                'practices_text' => ((int)$stats['practices_completed']) . '/' . ((int)$stats['practices_total']),
                'modules_completed' => $completedModules,
                'modules_total' => $totalModules,
                'tests_passed' => (int)$stats['passed_tests'],
                'tests_total' => (int)$stats['total_tests'],
                'tests_text' => ((int)$stats['passed_tests']) . '/' . ((int)$stats['total_tests']),
                'show_stats' => $state['state'] === 'progress' ? 1 : 0,
                'state' => $state['state'],
                'item_class' => $state['item_class'],
                'status_text' => $state['status_text'],
                'status_class' => $state['status_class'],
                'can_manage' => $service->canManageCourse($courseId, $userId) ? 1 : 0,
            );
        }

        return $this->success('', array(
            'total' => count($results),
            'results' => $results,
        ));
    }
}

return 'TrainingWebCourseMyCoursesProcessor';

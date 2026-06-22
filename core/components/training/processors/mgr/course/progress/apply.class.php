<?php

require_once __DIR__ . '/_helpers.php';

class TrainingCourseProgressApplyProcessor extends modProcessor
{
    public function checkPermissions()
    {
        return true;
    }

    public function process()
    {
        $courseId = trainingProgressCmpCourseId($this);
        $userId = (int)$this->getProperty('user_id', 0);
        $moduleId = (int)$this->getProperty('module_id', 0);
        $lessonId = (int)$this->getProperty('lesson_id', 0);
        $mode = $this->getProperty('mode', 'lesson') === 'module' ? 'module' : 'lesson';

        if ($courseId <= 0 || $userId <= 0 || $moduleId <= 0) {
            return $this->failure('Выберите пользователя и модуль');
        }

        try {
            $service = trainingProgressCmpGetService($this->modx);
            $before = $service->getUserSummary($courseId, $userId);
            $plan = $service->buildPlan($courseId, $userId, $mode, $moduleId, $lessonId);

            trainingProgressCmpLog($this->modx, 'apply_start', array(
                'actor_user_id' => trainingProgressCmpActorId($this->modx),
                'course_id' => $courseId,
                'user_id' => $userId,
                'mode' => $mode,
                'module_id' => $moduleId,
                'lesson_id' => $lessonId,
                'before' => $before,
                'plan' => array(
                    'complete_module_ids' => $plan['complete_module_ids'],
                    'complete_lesson_ids' => $plan['complete_lesson_ids'],
                    'activities' => $plan['required_activities'],
                ),
            ));

            $result = $service->applyPlan($plan, trainingProgressCmpActorId($this->modx));
            $after = $service->getUserSummary($courseId, $userId);

            trainingProgressCmpLog($this->modx, 'apply_success', array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'result' => $result,
                'after' => $after,
            ));

            return $this->success('Прогресс пользователя обновлён', array(
                'summary' => $after,
                'result' => $result,
                'result_html' => $this->buildResultHtml($result, $after),
            ));
        } catch (Throwable $e) {
            trainingProgressCmpLog($this->modx, 'apply_error', array(
                'course_id' => $courseId,
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ));

            return $this->failure($e->getMessage());
        }
    }

    protected function buildResultHtml(array $result, array $summary)
    {
        $html = '<div class="training-progress-result">';
        $html .= '<h3 style="margin:0 0 10px;">Прогресс обновлён</h3>';
        $html .= '<ul style="margin:0 0 12px 18px;">';
        $html .= '<li>Завершено уроков: <b>' . (int)$result['lessons_completed'] . '</b></li>';
        $html .= '<li>Отмечено видео: <b>' . (int)$result['videos_completed'] . '</b></li>';
        $html .= '<li>Создано пройденных тестов: <b>' . (int)$result['tests_created'] . '</b></li>';
        $html .= '<li>Создано/обновлено проверенных практик: <b>'
            . ((int)$result['practices_created'] + (int)$result['practices_updated'])
            . '</b></li>';
        $html .= '</ul>';
        $html .= '<p style="margin:0;"><b>Текущий модуль:</b> '
            . htmlspecialchars((string)$summary['current_module_label'], ENT_QUOTES, 'UTF-8')
            . '</p>';
        $html .= '<p style="margin:6px 0 0;"><b>Текущий урок:</b> '
            . htmlspecialchars((string)$summary['current_lesson_label'], ENT_QUOTES, 'UTF-8')
            . '</p>';
        $html .= '</div>';

        return $html;
    }
}

return 'TrainingCourseProgressApplyProcessor';

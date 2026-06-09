<?php
require_once dirname(dirname(__FILE__)) . '/_helper.php';

class TrainingPracticeMgrPracticeCreateProcessor extends modProcessor
{
    public function process()
    {
        $title = trainingPracticeMgrGetString($this, 'title');
        if ($title === '') {
            return $this->failure('Укажите название практического задания');
        }

        $courseId = trainingPracticeMgrGetInt($this, 'course_id');
        $moduleId = trainingPracticeMgrGetInt($this, 'module_id');

        if ($courseId <= 0) {
            return $this->failure('Укажите курс');
        }
        if ($moduleId <= 0) {
            return $this->failure('Укажите модуль');
        }

        $table = trainingPracticeMgrTable($this->modx, 'training_practices');
        $now = date('Y-m-d H:i:s');
        $deadlineAt = trainingPracticeMgrNormalizeDate($this->getProperty('deadline_at', ''));
        $rank = trainingPracticeMgrGetInt($this, 'rank');
        $active = trainingPracticeMgrGetInt($this, 'active', 1);

        $stmt = $this->modx->prepare("\n            INSERT INTO {$table}\n            (`course_id`, `module_id`, `title`, `description`, `template_file`, `template_file_name`, `image`, `deadline_at`, `deadline_days`, `allowed_extensions`, `max_file_size`, `active`, `rank`, `createdon`, `editedon`)\n            VALUES\n            (:course_id, :module_id, :title, :description, :template_file, :template_file_name, :image, :deadline_at, :deadline_days, :allowed_extensions, :max_file_size, :active, :rank, :createdon, :editedon)\n        ");

        $stmt->execute(array(
            ':course_id' => $courseId,
            ':module_id' => $moduleId,
            ':title' => $title,
            ':description' => (string)$this->getProperty('description', ''),
            ':template_file' => trainingPracticeMgrGetString($this, 'template_file'),
            ':template_file_name' => trainingPracticeMgrGetString($this, 'template_file_name'),
            ':image' => trainingPracticeMgrGetString($this, 'image'),
            ':deadline_at' => $deadlineAt,
            ':deadline_days' => trainingPracticeMgrGetInt($this, 'deadline_days'),
            ':allowed_extensions' => trainingPracticeMgrGetString($this, 'allowed_extensions', 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip'),
            ':max_file_size' => trainingPracticeMgrGetInt($this, 'max_file_size', 52428800),
            ':active' => $active,
            ':rank' => $rank,
            ':createdon' => $now,
            ':editedon' => $now,
        ));

        $practiceId = (int)$this->modx->lastInsertId();
        if ($active === 1 && $practiceId > 0) {
            trainingPracticeMgrEnsureModuleLink($this->modx, $courseId, $moduleId, $practiceId, $rank, 1);
        }

        return $this->success('Практическое задание создано', array('id' => $practiceId));
    }
}

return 'TrainingPracticeMgrPracticeCreateProcessor';

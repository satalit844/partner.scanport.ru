<?php

$corePath = dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/';
require_once $corePath . 'model/training/training.class.php';
require_once $corePath . 'model/training/services/trainingcertificate.class.php';

class TrainingCourseCertificateUpdateProcessor extends modProcessor
{
    public function process()
    {
        $courseId = (int)$this->getProperty('course_id', $this->getProperty('id', 0));
        if ($courseId <= 0) {
            return $this->failure('Не указан курс');
        }

        $service = new TrainingCertificateService($this->modx, new Training($this->modx));
        $row = $service->saveTemplate($courseId, array(
            'template_pdf' => $this->getProperty('template_pdf', ''),
            'template_preview' => $this->getProperty('template_preview', ''),
            'output_dir' => $this->getProperty('output_dir', ''),
            'page_no' => $this->getProperty('page_no', 1),
            'fullname_x' => $this->getProperty('fullname_x', 0),
            'fullname_y' => $this->getProperty('fullname_y', 0),
            'fullname_max_width' => $this->getProperty('fullname_max_width', 0),
            'fullname_font_size' => $this->getProperty('fullname_font_size', 28),
            'fullname_color' => $this->getProperty('fullname_color', '#7B4F92'),
            'fullname_align' => $this->getProperty('fullname_align', 'left'),
            'course_title_x' => $this->getProperty('course_title_x', 0),
            'course_title_y' => $this->getProperty('course_title_y', 0),
            'course_title_max_width' => $this->getProperty('course_title_max_width', 0),
            'course_title_font_size' => $this->getProperty('course_title_font_size', 24),
            'course_title_color' => $this->getProperty('course_title_color', '#FFFFFF'),
            'course_title_align' => $this->getProperty('course_title_align', 'left'),
            'completed_date_x' => $this->getProperty('completed_date_x', 0),
            'completed_date_y' => $this->getProperty('completed_date_y', 0),
            'completed_date_max_width' => $this->getProperty('completed_date_max_width', 0),
            'completed_date_font_size' => $this->getProperty('completed_date_font_size', 20),
            'completed_date_color' => $this->getProperty('completed_date_color', '#7B4F92'),
            'completed_date_align' => $this->getProperty('completed_date_align', 'left'),
            'date_format' => $this->getProperty('date_format', 'd.m.Y'),
            'is_active' => (int)$this->getProperty('is_active', 0),
        ));
        if (!$row) {
            return $this->failure('Не удалось сохранить шаблон сертификата');
        }
        return $this->success('Шаблон сертификата сохранён', $row);
    }
}
return 'TrainingCourseCertificateUpdateProcessor';

<?php

class TrainingHomeManagerController extends modExtraManagerController
{
    /** @var Training $training */
    public $training;

    public function initialize()
    {
        $corePath = $this->modx->getOption(
            'training.core_path',
            null,
            $this->modx->getOption('core_path') . 'components/training/'
        );

        $this->training = $this->modx->getService(
            'training',
            'Training',
            $corePath . 'model/training/'
        );

        parent::initialize();
    }

    public function getLanguageTopics()
    {
        return ['training:default'];
    }

    public function checkPermissions()
    {
        return true;
    }

    public function getPageTitle()
    {
        $lessonId = (int)$this->modx->getOption('lesson_id', $_GET, 0);
        $moduleId = (int)$this->modx->getOption('module_id', $_GET, 0);
        $courseId = (int)$this->modx->getOption('course_id', $_GET, 0);

        if ($lessonId > 0) {
            return 'Урок';
        }
        if ($moduleId > 0) {
            return 'Модуль';
        }
        if ($courseId > 0) {
            return 'Курс';
        }

        return 'Курсы';
    }

    public function loadCustomCssJs()
    {
        $assetsJsUrl = $this->training->config['assetsUrl'] . 'js/mgr/';
        $assetsCssUrl = $this->training->config['assetsUrl'] . 'css/mgr/';
        $courseId = (int)$this->modx->getOption('course_id', $_GET, 0);
        $moduleId = (int)$this->modx->getOption('module_id', $_GET, 0);
        $lessonId = (int)$this->modx->getOption('lesson_id', $_GET, 0);
        $xtype = 'training-page-home';

        if ($lessonId > 0) {
            $xtype = 'training-page-lesson';
        } elseif ($moduleId > 0) {
            $xtype = 'training-page-module';
        } elseif ($courseId > 0) {
            $xtype = 'training-page-course';
        }

        $this->addCss($assetsCssUrl . 'certificates.css');

        $this->addJavascript($assetsJsUrl . 'training.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/home.panel.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.panel.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.tabs.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.certificate.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.progress.panel.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/course.access.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/manager.link.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.panel.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.tabs.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.lesson.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.testlink.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/lesson.panel.js');
        $this->addJavascript($assetsJsUrl . 'widgets/lesson.tabs.js');
        $this->addJavascript($assetsJsUrl . 'widgets/lesson.video.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.video.grid.js');
        $this->addJavascript($assetsJsUrl . 'widgets/module.slide.grid.js');

        $this->addJavascript($assetsJsUrl . 'sections/home.js');
        $this->addJavascript($assetsJsUrl . 'sections/course.js');
        $this->addJavascript($assetsJsUrl . 'sections/module.js');
        $this->addJavascript($assetsJsUrl . 'sections/lesson.js');

        $this->addHtml(
            '<script type="text/javascript">'
            . 'Ext.onReady(function() {'
            . 'Training.config = ' . $this->modx->toJSON([
                'connector_url' => $this->training->config['connectorUrl'],
                'course_id' => $courseId,
                'module_id' => $moduleId,
                'lesson_id' => $lessonId,
                'courses_parent_id' => (int)$this->modx->getOption('training_courses_parent_id', null, 150),
                'media_source' => (int)$this->modx->getOption('training.training_media_source', null, 3),
            ]) . ';'
            . 'MODx.load({xtype:"' . $xtype . '"});'
            . '});'
            . '</script>'
        );
    }

    public function getTemplateFile()
    {
        return $this->training->config['templatesPath'] . 'home.tpl';
    }
}

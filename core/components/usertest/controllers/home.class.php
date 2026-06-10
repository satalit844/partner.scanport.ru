<?php

/**
 * The home manager controller for UserTest.
 *
 */
class UserTestHomeManagerController extends modExtraManagerController
{
    /** @var UserTest $UserTest */
    public $UserTest;


    /**
     *
     */
    public function initialize()
    {
        $path = $this->modx->getOption('usertest_core_path', null,
                $this->modx->getOption('core_path') . 'components/usertest/') . 'model/usertest/';
        $this->UserTest = $this->modx->getService('usertest', 'UserTest', $path);
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return array('usertest:default');
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('usertest');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->loadRichTextEditor();
		
		$this->addCss($this->UserTest->config['cssUrl'] . 'mgr/main.css');
        $this->addCss($this->UserTest->config['cssUrl'] . 'mgr/bootstrap.buttons.css');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/usertest.js');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/misc/combo.js');
        //$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/items.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/tests.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/groups.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/grouplinks.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/categorys.grid.js');
		
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/tests.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/questions.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/answers.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/variants.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/results.grid.js');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/userresults.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/useranswers.windows.js'); 
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/usercategorys.windows.js');
		//$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/sections/home.js');
		
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/questions_with_parent.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/invites.grid.js');
		
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/variant_sets.grid.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/test_question_link.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/test_variant_link.windows.js');
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/show_tests_in_variant_set.windows.js');
		
		$this->addJavascript($this->UserTest->config['jsUrl'] . 'mgr/widgets/export.panel.js');
		
        $this->addHtml('<script type="text/javascript">
        UserTest.config = ' . json_encode($this->UserTest->config) . ';
        UserTest.config.connector_url = "' . $this->UserTest->config['connectorUrl'] . '";
        Ext.onReady(function() {
            MODx.load({ xtype: "usertest-page-home"});
        });
        </script>
        ');
    }

	public function loadRichTextEditor()
    {
        $useEditor = $this->modx->getOption('use_editor');
        $whichEditor = $this->modx->getOption('which_editor');
        if ($useEditor && !empty($whichEditor))
        {
            // invoke the OnRichTextEditorInit event
            $onRichTextEditorInit = $this->modx->invokeEvent('OnRichTextEditorInit',array(
                'editor' => $whichEditor, // Not necessary for Redactor
                'elements' => array('foo'), // Not necessary for Redactor
            ));
            if (is_array($onRichTextEditorInit))
            {
                $onRichTextEditorInit = implode('', $onRichTextEditorInit);
            }
            $this->setPlaceholder('onRichTextEditorInit', $onRichTextEditorInit);
        }
    }
    /**
     * @return string
     */
    public function getTemplateFile()
    {
        return $this->UserTest->config['templatesPath'] . 'home.tpl';
    }
}
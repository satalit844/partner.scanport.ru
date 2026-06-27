
class TrainingPracticesManagerController extends modExtraManagerController
{
    public function getPageTitle()
    {
        return 'Практические задания';
    }

    public function loadCustomCssJs()
    {
        $assetsUrl = $this->modx->getOption('training.assets_url', null, $this->modx->getOption('assets_url') . 'components/training/');
        $assetsUrl = rtrim($assetsUrl, '/') . '/';

        $this->addJavascript($assetsUrl . 'js/mgr/practices/practices.panel.js');

        $connectorUrl = $assetsUrl . 'connector.php';
        $this->addHtml('<script type="text/javascript">Ext.onReady(function(){window.Training = window.Training || {}; Training.config = Training.config || {}; Training.config.connector_url = "' . $connectorUrl . '"; MODx.add({xtype: "training-panel-practices"});});</script>');
    }

    public function getTemplateFile()
    {
        return $this->modx->getOption('training.core_path', null, $this->modx->getOption('core_path') . 'components/training/') . 'templates/mgr/practices.tpl';
    }
}
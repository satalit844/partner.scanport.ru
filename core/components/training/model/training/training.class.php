<?php

class Training
{
    /** @var modX $modx */
    public $modx;

    /** @var array $config */
    public $config = [];

    public function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $corePath = $modx->getOption(
            'training.core_path',
            $config,
            $modx->getOption('core_path') . 'components/training/'
        );

        $assetsUrl = $modx->getOption(
            'training.assets_url',
            $config,
            $modx->getOption('assets_url') . 'components/training/'
        );

        $assetsPath = $modx->getOption(
            'training.assets_path',
            $config,
            $modx->getOption('assets_path') . 'components/training/'
        );

        $this->config = array_merge([
            'namespace'       => 'training',
            'corePath'        => $corePath,
            'modelPath'       => $corePath . 'model/',
            'processorsPath'  => $corePath . 'processors/',
            'controllersPath' => $corePath . 'controllers/',
            'templatesPath'   => $corePath . 'templates/',
            'assetsUrl'       => $assetsUrl,
            'assetsPath'      => $assetsPath,
            'jsUrl'           => $assetsUrl . 'js/mgr/',
            'cssUrl'          => $assetsUrl . 'css/mgr/',
            'connectorUrl'    => $modx->getOption(
                'training.connector_url',
                null,
                $assetsUrl . 'connector.php'
            ),
        ], $config);

        $this->modx->addPackage('training', $this->config['modelPath']);
        $this->modx->lexicon->load('training:default');
    }
}
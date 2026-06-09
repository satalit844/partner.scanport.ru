Training.page.Module = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        components: [{
            xtype: 'training-panel-module',
            renderTo: 'training-panel-home-div'
        }]
    });

    Training.page.Module.superclass.constructor.call(this, config);
};

Ext.extend(Training.page.Module, MODx.Component);
Ext.reg('training-page-module', Training.page.Module);

Training.page.Home = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        components: [{
            xtype: 'training-panel-home',
            renderTo: 'training-panel-home-div'
        }]
    });

    Training.page.Home.superclass.constructor.call(this, config);
};

Ext.extend(Training.page.Home, MODx.Component);
Ext.reg('training-page-home', Training.page.Home);
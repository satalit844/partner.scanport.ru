Training.page.Course = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        components: [{
            xtype: 'training-panel-course',
            renderTo: 'training-panel-home-div'
        }]
    });

    Training.page.Course.superclass.constructor.call(this, config);
};

Ext.extend(Training.page.Course, MODx.Component);
Ext.reg('training-page-course', Training.page.Course);
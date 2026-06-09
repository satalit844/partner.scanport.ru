Training.panel.Module = function(config) {
    config = config || {};
    Ext.apply(config, {
        id: 'training-panel-module',
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            html: '<h2>Модуль</h2>',
            style: {margin: '15px 0'}
        }, {
            xtype: 'training-module-tabs',
            moduleId: Training.config.module_id,
            courseId: Training.config.course_id,
            cls: 'main-wrapper',
            preventRender: true,
            anchor: '100%'
        }]
    });
    Training.panel.Module.superclass.constructor.call(this, config);
};
Ext.extend(Training.panel.Module, MODx.Panel);
Ext.reg('training-panel-module', Training.panel.Module);

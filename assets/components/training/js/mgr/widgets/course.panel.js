Training.panel.Course = function(config) {
    config = config || {};

    Ext.apply(config, {
        id: 'training-panel-course',
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            html: '<h2>Курс</h2>',
            style: {margin: '15px 0'}
        }, {
            xtype: 'training-course-tabs',
            courseId: Training.config.course_id,
            cls: 'main-wrapper',
            preventRender: true,
            anchor: '100%'
        }]
    });

    Training.panel.Course.superclass.constructor.call(this, config);
};

Ext.extend(Training.panel.Course, MODx.Panel);
Ext.reg('training-panel-course', Training.panel.Course);

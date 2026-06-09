Training.panel.Lesson = function(config) {
    config = config || {};
    Ext.apply(config, {
        id: 'training-panel-lesson',
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            html: '<h2>Урок</h2>',
            style: {margin: '15px 0'}
        },{
            xtype: 'training-lesson-tabs',
            lessonId: Training.config.lesson_id,
            moduleId: Training.config.module_id,
            courseId: Training.config.course_id,
            cls: 'main-wrapper',
            preventRender: true,
            anchor: '100%'
        }]
    });
    Training.panel.Lesson.superclass.constructor.call(this, config);
};
Ext.extend(Training.panel.Lesson, MODx.Panel);
Ext.reg('training-panel-lesson', Training.panel.Lesson);

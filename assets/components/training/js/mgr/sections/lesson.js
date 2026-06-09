Training.page.Lesson = function(config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'training-panel-lesson',
            renderTo: 'training-panel-home-div'
        }]
    });
    Training.page.Lesson.superclass.constructor.call(this, config);
};
Ext.extend(Training.page.Lesson, MODx.Component);
Ext.reg('training-page-lesson', Training.page.Lesson);

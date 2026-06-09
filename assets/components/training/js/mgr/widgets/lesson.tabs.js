Training.panel.LessonTabs = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-lesson-tabs',
        border: true,
        deferredRender: false,
        defaults: {border: false, autoHeight: true},
        items: [{
            title: 'Общие',
            xtype: 'training-lesson-general-form',
            lessonId: this.lessonId,
            moduleId: this.moduleId,
            courseId: this.courseId
        },{
            title: 'Видео урока',
            xtype: 'panel',
            layout: 'anchor',
            autoHeight: true,
            items: [{
                xtype: 'training-grid-lesson-videos',
                lessonId: this.lessonId,
                moduleId: this.moduleId,
                anchor: '100%'
            },{
                xtype: 'training-grid-lesson-qualities',
                lessonId: this.lessonId,
                anchor: '100%',
                style: 'margin-top:12px;'
            },{
                xtype: 'training-grid-lesson-slides',
                lessonId: this.lessonId,
                anchor: '100%',
                style: 'margin-top:12px;'
            }]
        }]
    });

    Training.panel.LessonTabs.superclass.constructor.call(this, config);
};
Ext.extend(Training.panel.LessonTabs, MODx.Tabs);
Ext.reg('training-lesson-tabs', Training.panel.LessonTabs);

Training.form.LessonGeneral = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-lesson-general-form',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/module/lesson/update'},
        bodyCssClass: 'main-wrapper',
        cls: 'container',
        labelWidth: 220,
        autoHeight: true,
        border: false,
        bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
        defaults: {anchor: '100%'},
        items: [{
            xtype: 'hidden', name: 'id', value: this.lessonId
        },{
            xtype: 'hidden', name: 'module_id', value: this.moduleId
        },{
            xtype: 'fieldset',
            title: 'Основное',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'displayfield', fieldLabel: 'ID урока', name: 'id_label'
            },{
                xtype: 'displayfield', fieldLabel: 'Модуль', name: 'module_title'
            },{
                xtype: 'textfield', fieldLabel: 'Название урока', name: 'title', anchor: '100%', allowBlank: false
            },{
                xtype: 'numberfield', fieldLabel: 'Порядок', name: 'sort_order', anchor: '100%'
            },{
                xtype: 'textarea', fieldLabel: 'Описание', name: 'description', anchor: '100%'
            },{
                xtype: 'displayfield', fieldLabel: 'Видео урока', name: 'videos_count'
            },{
                xtype: 'displayfield', fieldLabel: 'Слайдов', name: 'slides_count'
            },{
                xtype: 'xcheckbox', boxLabel: 'Урок по умолчанию', hideLabel: true, name: 'is_default', inputValue: 1
            },{
                xtype: 'xcheckbox', boxLabel: 'Урок активен', hideLabel: true, name: 'is_active', inputValue: 1, checked: true
            }]
        },{
            xtype: 'fieldset',
            title: 'Презентация урока',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'button',
                text: 'Выбрать файл с сервера',
                style: 'margin-bottom:10px;',
                handler: function() {
                    var field = this.getForm().findField('source_presentation');
                    if (field) {
                        Training.utils.openPathBrowser(field, {source: Training.config.media_source || 3, allowedFileTypes: 'ppt,pptx,pdf'});
                    }
                },
                scope: this
            },{
                xtype: 'textfield', fieldLabel: 'Исходный PPT/PPTX/PDF', name: 'source_presentation', anchor: '100%'
            },{
                xtype: 'displayfield', fieldLabel: 'Статус презентации', name: 'presentation_status'
            },{
                xtype: 'displayfield', fieldLabel: 'PDF', name: 'presentation_pdf', renderer: Training.utils.renderFileLink
            },{
                xtype: 'displayfield', fieldLabel: 'Папка слайдов', name: 'slides_dir'
            }]
        }],
        buttons: [{
            text: 'Сохранить',
            handler: this.saveForm,
            scope: this
        },{
            text: 'Разобрать презентацию',
            handler: this.processPresentation,
            scope: this
        },{
            text: 'Назад к модулю',
            handler: function() { window.location.href = Training.utils.buildUrl({course_id: this.courseId, module_id: this.moduleId}, ['lesson_id']); },
            scope: this
        },{
            text: 'Назад к курсу',
            handler: function() { window.location.href = Training.utils.buildUrl({course_id: this.courseId}, ['module_id', 'lesson_id']); },
            scope: this
        }]
    });

    Training.form.LessonGeneral.superclass.constructor.call(this, config);
    this.on('afterrender', this.loadLesson, this);
};
Ext.extend(Training.form.LessonGeneral, MODx.FormPanel, {
    loadLesson: function() {
        var form = this.getForm();
        this.getEl().mask('Загружаем урок...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/module/lesson/get', id: this.lessonId},
            listeners: {
                success: {fn: function(r) {
                    this.getEl().unmask();
                    var obj = Training.utils.getResultData(r);
                    form.setValues(obj);
                    Training.utils.setCheckboxValue(form, 'is_default', obj.is_default);
                    Training.utils.setCheckboxValue(form, 'is_active', obj.is_active);
                }, scope: this},
                failure: {fn: function(r) {
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось загрузить урок');
                }, scope: this}
            }
        });
    },

    saveForm: function() {
        var form = this.getForm();
        if (!form.isValid()) { return false; }
        var values = form.getValues();
        values.action = 'mgr/module/lesson/update';
        values.id = this.lessonId;
        values.module_id = this.moduleId;
        values.is_default = Training.utils.toBool(form.findField('is_default').getValue()) ? 1 : 0;
        values.is_active = Training.utils.toBool(form.findField('is_active').getValue()) ? 1 : 0;
        MODx.Ajax.request({url: Training.config.connector_url, params: values, listeners: {success: {fn: function(){ this.loadLesson(); MODx.msg.alert('Готово', 'Урок сохранён'); }, scope: this}}});
    },

    processPresentation: function() {
        var values = this.getForm().getValues();
        this.getEl().mask('Разбираем презентацию...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/presentation/process', lesson_id: this.lessonId, source_presentation: values.source_presentation || ''},
            listeners: {
                success: {fn: function(){
                    this.getEl().unmask();
                    this.loadLesson();
                    var videos = Ext.getCmp('training-grid-lesson-videos');
                    var qualities = Ext.getCmp('training-grid-lesson-qualities');
                    var slides = Ext.getCmp('training-grid-lesson-slides');
                    if (videos) { videos.currentVideoId = 0; videos.refresh(); }
                    if (qualities) { qualities.setVideoContext(0); }
                    if (slides) { slides.setVideoContext(0); }
                    MODx.msg.alert('Готово', 'Презентация переразобрана, старые слайды заменены новыми.');
                }, scope: this},
                failure: {fn: function(r){
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось разобрать презентацию');
                }, scope: this}
            }
        });
    }
});
Ext.reg('training-lesson-general-form', Training.form.LessonGeneral);

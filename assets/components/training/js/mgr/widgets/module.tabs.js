Training.panel.ModuleTabs = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-module-tabs',
        border: true,
        deferredRender: false,
        defaults: {border: false, autoHeight: true},
        items: [{
            title: 'Уроки',
            xtype: 'training-grid-module-lessons',
            moduleId: this.moduleId,
            courseId: this.courseId
        }, {
            title: 'Тесты и практики',
            xtype: 'training-grid-module-test-links',
            moduleId: this.moduleId,
            courseId: this.courseId
        }, {
            title: 'Общие',
            xtype: 'training-module-general-form',
            moduleId: this.moduleId,
            courseId: this.courseId
        }]
    });

    Training.panel.ModuleTabs.superclass.constructor.call(this, config);
};
Ext.extend(Training.panel.ModuleTabs, MODx.Tabs);
Ext.reg('training-module-tabs', Training.panel.ModuleTabs);

Training.form.ModuleGeneral = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-module-general-form',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/module/update'},
        bodyCssClass: 'main-wrapper',
        cls: 'container',
        labelWidth: 220,
        autoHeight: true,
        border: false,
        bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
        defaults: {anchor: '100%'},
        items: [{
            xtype: 'hidden',
            name: 'id',
            value: this.moduleId
        },{
            xtype: 'hidden',
            name: 'course_id',
            value: this.courseId
        },{
            xtype: 'fieldset',
            title: 'Основное',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'displayfield', fieldLabel: 'ID модуля', name: 'id_label'
            },{
                xtype: 'displayfield', fieldLabel: 'ID курса', name: 'course_id_label'
            },{
                xtype: 'displayfield', fieldLabel: 'Курс', name: 'course_title'
            },{
                xtype: 'displayfield', fieldLabel: 'Resource ID модуля', name: 'resource_id'
            },{
                xtype: 'displayfield', fieldLabel: 'Название модуля', name: 'pagetitle'
            },{
                xtype: 'displayfield', fieldLabel: 'URI', name: 'uri'
            },{
                xtype: 'displayfield', fieldLabel: 'Опубликован ресурс', name: 'published_label'
            },{
                xtype: 'xcheckbox', boxLabel: 'Модуль активен', hideLabel: true, name: 'is_active', inputValue: 1, checked: true
            },{
                xtype: 'xcheckbox', boxLabel: 'Модуль обязателен для прохождения', hideLabel: true, name: 'is_required', inputValue: 1, checked: true
            }]
        }],
        buttons: [{
            text: 'Сохранить',
            handler: this.saveForm,
            scope: this
        },{
            text: 'Открыть ресурс',
            handler: function() {
                var values = this.getForm().getValues();
                if (values.resource_id) { MODx.loadPage('resource/update', 'id=' + values.resource_id); }
            },
            scope: this
        },{
            text: 'Назад к курсу',
            handler: function() {
                window.location.href = Training.utils.buildUrl({course_id: this.courseId}, ['module_id', 'lesson_id']);
            },
            scope: this
        },{
            text: 'Назад к курсам',
            handler: function() {
                window.location.href = Training.utils.buildUrl({}, ['course_id', 'module_id', 'lesson_id']);
            }
        }]
    });

    Training.form.ModuleGeneral.superclass.constructor.call(this, config);
    this.on('afterrender', this.loadModule, this);
};
Ext.extend(Training.form.ModuleGeneral, MODx.FormPanel, {
    loadModule: function() {
        var form = this.getForm();
        this.getEl().mask('Загружаем модуль...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/module/get', id: this.moduleId},
            listeners: {
                success: {fn: function(r) {
                    this.getEl().unmask();
                    var obj = Training.utils.getResultData(r);
                    form.setValues(obj);
                    Training.utils.setCheckboxValue(form, 'is_active', obj.is_active);
                    Training.utils.setCheckboxValue(form, 'is_required', obj.is_required);
                }, scope: this},
                failure: {fn: function(r) {
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось загрузить модуль');
                }, scope: this}
            }
        });
    },

    saveForm: function() {
        var form = this.getForm();
        if (!form.isValid()) { return false; }
        var values = form.getValues();
        values.action = 'mgr/module/update';
        values.id = this.moduleId;
        values.is_active = Training.utils.toBool(form.findField('is_active').getValue()) ? 1 : 0;
        values.is_required = Training.utils.toBool(form.findField('is_required').getValue()) ? 1 : 0;
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: values,
            listeners: {success: {fn: function(){ this.loadModule(); MODx.msg.alert('Готово', 'Модуль сохранён'); }, scope: this}}
        });
    }
});
Ext.reg('training-module-general-form', Training.form.ModuleGeneral);

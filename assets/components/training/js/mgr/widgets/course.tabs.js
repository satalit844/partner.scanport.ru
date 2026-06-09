Training.panel.CourseTabs = function(config) {
    config = config || {};

    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-course-tabs',
        border: true,
        deferredRender: false,
        defaults: {
            border: false,
            autoHeight: true
        },
        items: [{
            title: 'Общие',
            xtype: 'training-course-general-form',
            courseId: this.courseId
        }, {
            title: 'Модули',
            xtype: 'training-grid-course-modules',
            courseId: this.courseId
        }, {
            title: 'Доступ',
            xtype: 'training-grid-course-access',
            courseId: this.courseId
        }, {
            title: 'Сертификат',
            xtype: 'training-course-certificate-panel',
            courseId: this.courseId
        }]
    });

    Training.panel.CourseTabs.superclass.constructor.call(this, config);
};

Ext.extend(Training.panel.CourseTabs, MODx.Tabs);
Ext.reg('training-course-tabs', Training.panel.CourseTabs);


Training.form.CourseGeneral = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-course-general-form',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/course/update'
        },
        bodyCssClass: 'main-wrapper',
        cls: 'container',
        labelWidth: 190,
        autoHeight: true,
        border: false,
        bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
        defaults: {
            anchor: '100%'
        },
        items: [{
            html: '<div style="padding:0 0 16px;color:#666;line-height:1.6;">На уровне курса остаются только общие параметры и доступы. Презентации теперь загружаются в карточке каждого модуля отдельно.</div>',
            border: false
        }, {
            xtype: 'hidden',
            name: 'id',
            value: this.courseId
        }, {
            xtype: 'fieldset',
            title: 'Основное',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'displayfield',
                fieldLabel: 'ID курса',
                name: 'id_label'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Resource ID',
                name: 'resource_id'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Название',
                name: 'pagetitle'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Алиас',
                name: 'alias'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Контекст',
                name: 'context_key'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'URI',
                name: 'uri'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Опубликован ресурс',
                name: 'published_label'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Модулей',
                name: 'modules_count'
            }, {
                xtype: 'xcheckbox',
                boxLabel: 'Курс активен',
                hideLabel: true,
                name: 'is_active',
                inputValue: 1,
                checked: true
            }]
        }],
        buttons: [{
            text: 'Сохранить',
            handler: this.saveForm,
            scope: this
        }, {
            text: 'Открыть ресурс',
            handler: function() {
                var form = this.getForm();
                var values = form.getValues();
                if (values.resource_id) {
                    MODx.loadPage('resource/update', 'id=' + values.resource_id);
                }
            },
            scope: this
        }, {
            text: 'Назад к списку',
            handler: function() {
                window.location.href = Training.utils.buildUrl({}, ['course_id', 'module_id']);
            }
        }]
    });

    Training.form.CourseGeneral.superclass.constructor.call(this, config);

    this.on('afterrender', this.loadCourse, this);
};

Ext.extend(Training.form.CourseGeneral, MODx.FormPanel, {
    loadCourse: function() {
        var form = this.getForm();
        this.getEl().mask('Загружаем курс...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/get',
                id: this.courseId
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.getEl().unmask();
                        var obj = Training.utils.getResultData(r);
                        form.setValues(obj);
                        Training.utils.setCheckboxValue(form, 'is_active', obj.is_active);
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось загрузить курс');
                    },
                    scope: this
                }
            }
        });
    },

    saveForm: function() {
        var form = this.getForm();
        if (!form.isValid()) {
            return false;
        }

        var values = form.getValues();
        values.action = 'mgr/course/update';
        values.id = this.courseId;
        values.is_active = Training.utils.toBool(form.findField('is_active').getValue()) ? 1 : 0;

        this.getEl().mask('Сохраняем курс...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: values,
            listeners: {
                success: {
                    fn: function() {
                        this.getEl().unmask();
                        this.loadCourse();
                        MODx.msg.alert('Готово', 'Курс сохранён');
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось сохранить курс');
                    },
                    scope: this
                }
            }
        });
    }
});

Ext.reg('training-course-general-form', Training.form.CourseGeneral);

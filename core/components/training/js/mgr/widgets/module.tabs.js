Training.panel.ModuleTabs = function(config) {
    config = config || {};

    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-module-tabs',
        border: true,
        deferredRender: false,
        defaults: {
            border: false,
            autoHeight: true
        },
        items: [{
            title: 'Общие',
            xtype: 'training-module-general-form',
            moduleId: this.moduleId,
            courseId: this.courseId
        }, {
            title: 'Видео модуля',
            xtype: 'panel',
            layout: 'anchor',
            autoHeight: true,
            items: [{
                xtype: 'training-grid-module-lessons',
                moduleId: this.moduleId,
                anchor: '100%'
            }, {
                xtype: 'training-grid-module-videos',
                moduleId: this.moduleId,
                lessonId: 0,
                anchor: '100%',
                style: 'margin-top:12px;'
            }, {
                xtype: 'training-grid-module-slides',
                moduleId: this.moduleId,
                lessonId: 0,
                anchor: '100%',
                style: 'margin-top:12px;'
            }]
        }]
    });

    Training.panel.ModuleTabs.superclass.constructor.call(this, config);
};

Ext.extend(Training.panel.ModuleTabs, MODx.Tabs);
Ext.reg('training-module-tabs', Training.panel.ModuleTabs);


Training.combo.ModuleVideoFiles = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;

    Ext.applyIf(config, {
        hiddenName: config.name || 'video_source_selector',
        name: config.name || 'video_source_selector',
        fieldLabel: config.fieldLabel || 'Выбрать файл с сервера',
        valueField: 'path',
        displayField: 'path',
        editable: false,
        forceSelection: true,
        triggerAction: 'all',
        mode: 'remote',
        pageSize: 30,
        anchor: '100%',
        emptyText: 'Открыть список доступных видеофайлов',
        store: new Ext.data.JsonStore({
            url: Training.config.connector_url,
            root: 'results',
            totalProperty: 'total',
            idProperty: 'path',
            fields: ['path', 'name', 'dir', 'ext', 'filesize'],
            baseParams: {
                action: 'mgr/module/video/files',
                module_id: this.moduleId
            }
        })
    });

    Training.combo.ModuleVideoFiles.superclass.constructor.call(this, config);
};

Ext.extend(Training.combo.ModuleVideoFiles, MODx.combo.ComboBox);
Ext.reg('training-combo-module-video-files', Training.combo.ModuleVideoFiles);


Training.form.ModuleGeneral = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-module-general-form',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/module/update'
        },
        bodyCssClass: 'main-wrapper',
        cls: 'container',
        labelWidth: 220,
        autoHeight: true,
        border: false,
        bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
        defaults: {
            anchor: '100%'
        },
        items: [{
            html: '<div style="padding:0 0 16px;color:#666;line-height:1.6;">Презентация теперь привязывается к модулю. Здесь выбирается исходный PPT/PPTX/PDF модуля, он разбирается в PDF и JPG-слайды, а дальше эти слайды используются в lesson/video-логике.</div>',
            border: false
        }, {
            xtype: 'hidden',
            name: 'id',
            value: this.moduleId
        }, {
            xtype: 'hidden',
            name: 'course_id',
            value: this.courseId
        }, {
            xtype: 'fieldset',
            title: 'Основное',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'displayfield',
                fieldLabel: 'ID модуля',
                name: 'id_label'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'ID курса',
                name: 'course_id_label'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Курс',
                name: 'course_title'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Resource ID модуля',
                name: 'resource_id'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Название модуля',
                name: 'pagetitle'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'URI',
                name: 'uri'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Опубликован ресурс',
                name: 'published_label'
            }, {
                xtype: 'xcheckbox',
                boxLabel: 'Модуль активен',
                hideLabel: true,
                name: 'is_active',
                inputValue: 1,
                checked: true
            }, {
                xtype: 'xcheckbox',
                boxLabel: 'Модуль обязателен для прохождения',
                hideLabel: true,
                name: 'is_required',
                inputValue: 1,
                checked: true
            }]
        }, {
            xtype: 'fieldset',
            title: 'Презентация модуля',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'button',
                text: 'Выбрать файл с сервера',
                style: 'margin-bottom:10px;',
                handler: function() {
                    var field = this.getForm().findField('source_presentation');
                    if (field) {
                        Training.utils.openPathBrowser(field, {
                            source: Training.config.media_source || 3,
                            allowedFileTypes: 'ppt,pptx,pdf'
                        });
                    }
                },
                scope: this
            }, {
                xtype: 'textfield',
                fieldLabel: 'Исходный PPT/PPTX/PDF',
                name: 'source_presentation',
                anchor: '100%',
                emptyText: '/assets/uploads/training/module-1/source.pptx'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Статус презентации',
                name: 'presentation_status'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'PDF',
                name: 'presentation_pdf',
                renderer: Training.utils.renderFileLink
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Папка слайдов',
                name: 'slides_dir'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Папка найдена',
                name: 'slides_dir_exists_label'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Слайдов в папке',
                name: 'available_slides_count'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Привязано слайдов к урокам',
                name: 'slides_count'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Создан',
                name: 'createdon'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Обновлён',
                name: 'updatedon'
            }]
        }],
        buttons: [{
            text: 'Сохранить',
            handler: this.saveForm,
            scope: this
        }, {
            text: 'Разобрать презентацию в слайды',
            handler: this.processPresentation,
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
            text: 'Назад к модулям курса',
            handler: function() {
                var form = this.getForm();
                var values = form.getValues();
                window.location.href = Training.utils.buildUrl({
                    course_id: values.course_id || this.courseId
                }, ['module_id']);
            },
            scope: this
        }, {
            text: 'Назад к курсам',
            handler: function() {
                window.location.href = Training.utils.buildUrl({}, ['course_id', 'module_id']);
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
            params: {
                action: 'mgr/module/get',
                id: this.moduleId
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.getEl().unmask();
                        var obj = Training.utils.getResultData(r);
                        form.setValues(obj);
                        Training.utils.setCheckboxValue(form, 'is_active', obj.is_active);
                        Training.utils.setCheckboxValue(form, 'is_required', obj.is_required);
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось загрузить модуль');
                    },
                    scope: this
                }
            }
        });
    },

    getRequestErrorMessage: function(r, fallback) {
        fallback = fallback || 'Ошибка запроса';

        if (r && r.message) {
            return r.message;
        }

        try {
            if (r && r.a && r.a.response && r.a.response.responseText) {
                return r.a.response.responseText;
            }
        } catch (e) {}

        try {
            if (r && r.response && r.response.responseText) {
                return r.response.responseText;
            }
        } catch (e) {}

        try {
            if (r && r.a && r.a.status) {
                return 'HTTP ' + r.a.status + ': ' + fallback;
            }
        } catch (e) {}

        try {
            if (r && r.status) {
                return 'HTTP ' + r.status + ': ' + fallback;
            }
        } catch (e) {}

        return fallback;
    },

    saveForm: function() {
        var form = this.getForm();
        if (!form.isValid()) {
            return false;
        }

        var sourceField = form.findField('source_presentation');
        var params = {
            action: 'mgr/module/update',
            id: this.moduleId,
            course_id: form.findField('course_id') ? (form.findField('course_id').getValue() || this.courseId) : this.courseId,
            is_active: Training.utils.toBool(form.findField('is_active').getValue()) ? 1 : 0,
            is_required: Training.utils.toBool(form.findField('is_required').getValue()) ? 1 : 0,
            source_presentation: sourceField ? String(sourceField.getValue() || '').replace(/^\s+|\s+$/g, '') : ''
        };

        this.getEl().mask('Сохраняем модуль...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: params,
            listeners: {
                success: {
                    fn: function() {
                        this.getEl().unmask();
                        this.loadModule();
                        MODx.msg.alert('Готово', 'Модуль сохранён');
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', this.getRequestErrorMessage(r, 'Не удалось сохранить модуль'));
                    },
                    scope: this
                }
            }
        });
    },

    processPresentation: function() {
        var form = this.getForm();
        var sourceField = form.findField('source_presentation');
        var sourcePresentation = sourceField ? String(sourceField.getValue() || '').replace(/^\s+|\s+$/g, '') : '';

        this.getEl().mask('Обрабатываем презентацию модуля...');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/module/presentation/process',
                module_id: this.moduleId,
                source_presentation: sourcePresentation
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.getEl().unmask();
                        this.loadModule();

                        var lessonsGrid = Ext.getCmp('training-grid-module-lessons');
                        if (lessonsGrid) {
                            lessonsGrid.refresh();
                        }

                        var slidesGrid = Ext.getCmp('training-grid-module-slides');
                        if (slidesGrid) {
                            slidesGrid.refresh();
                        }

                        var message = 'Презентация модуля обработана';
                        if (r && r.object && typeof r.object.slides_count !== 'undefined') {
                            message += '. Слайдов: ' + r.object.slides_count;
                        }
                        MODx.msg.alert('Готово', message);
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', this.getRequestErrorMessage(r, 'Не удалось обработать презентацию модуля'));
                    },
                    scope: this
                }
            }
        });
    }
});

Ext.reg('training-module-general-form', Training.form.ModuleGeneral);

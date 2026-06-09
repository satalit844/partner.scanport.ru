Training.panel.CourseCertificate = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-course-certificate-panel',
        layout: 'anchor',
        autoHeight: true,
        border: false,
        defaults: {
            anchor: '100%'
        },
        items: [{
            xtype: 'training-course-certificate-form',
            courseId: this.courseId
        }, {
            xtype: 'training-grid-course-certificates',
            courseId: this.courseId
        }]
    });

    Training.panel.CourseCertificate.superclass.constructor.call(this, config);
};
Ext.extend(Training.panel.CourseCertificate, Ext.Panel);
Ext.reg('training-course-certificate-panel', Training.panel.CourseCertificate);


Training.form.CourseCertificate = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;

    Ext.apply(config, {
        id: 'training-course-certificate-form',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/course/certificate/update'},
        bodyCssClass: 'main-wrapper',
        cls: 'container training-certificate-form-panel',
        labelWidth: 220,
        autoHeight: true,
        border: false,
        buttonAlign: 'left',
        minButtonWidth: 150,
        bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
        defaults: {anchor: '100%'},
        items: [{
            xtype: 'hidden',
            name: 'course_id',
            value: this.courseId
        }, {
            html: '<div style="padding:0 0 16px;color:#666;line-height:1.6;">Здесь настраивается шаблон сертификата для текущего курса. Сохраняете PDF-шаблон и координаты ФИО, названия курса и даты завершения, затем можно сгенерировать сертификаты для завершивших курс пользователей. Нижняя таблица показывает, кто завершил курс и создан ли для него файл сертификата.</div>',
            border: false
        }, {
            xtype: 'fieldset',
            title: 'Шаблон сертификата',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: [{
                xtype: 'button',
                text: 'Выбрать PDF с сервера',
                minWidth: 190,
                style: 'margin-bottom:10px;',
                handler: function() {
                    var field = this.getForm().findField('template_pdf');
                    if (field) {
                        Training.utils.openPathBrowser(field, {source: Training.config.media_source || 3, allowedFileTypes: 'pdf'});
                    }
                },
                scope: this
            }, {
                xtype: 'textfield',
                fieldLabel: 'PDF-шаблон',
                name: 'template_pdf'
            }, {
                xtype: 'displayfield',
                fieldLabel: 'Превью шаблона',
                name: 'template_preview',
                renderer: Training.utils.renderFileLink
            }, {
                xtype: 'textfield',
                fieldLabel: 'Папка вывода',
                name: 'output_dir',
                emptyText: '/assets/training/certificates/course_1/'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Страница шаблона',
                name: 'page_no',
                allowBlank: false,
                minValue: 1,
                value: 1
            }, {
                xtype: 'textfield',
                fieldLabel: 'Формат даты',
                name: 'date_format',
                value: 'd.m.Y'
            }, {
                xtype: 'xcheckbox',
                boxLabel: 'Шаблон активен',
                hideLabel: true,
                name: 'is_active',
                inputValue: 1,
                checked: true
            }]
        }, {
            xtype: 'fieldset',
            title: 'ФИО',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: this.buildFieldItems('fullname', '#7B4F92', 28)
        }, {
            xtype: 'fieldset',
            title: 'Название курса',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: this.buildFieldItems('course_title', '#FFFFFF', 24)
        }, {
            xtype: 'fieldset',
            title: 'Дата завершения',
            autoHeight: true,
            defaults: {anchor: '100%'},
            items: this.buildFieldItems('completed_date', '#7B4F92', 20)
        }],
        buttons: [{
            text: 'Сохранить',
            minWidth: 120,
            handler: this.saveForm,
            scope: this
        }, {
            text: 'Сгенерировать всем',
            minWidth: 170,
            handler: this.generateAll,
            scope: this
        }, {
            text: 'Обновить',
            minWidth: 120,
            handler: this.loadForm,
            scope: this
        }]
    });

    Training.form.CourseCertificate.superclass.constructor.call(this, config);
    this.on('afterrender', this.loadForm, this);
};
Ext.extend(Training.form.CourseCertificate, MODx.FormPanel, {
    buildFieldItems: function(prefix, defaultColor, defaultSize) {
        return [{
            xtype: 'textfield',
            fieldLabel: 'X',
            name: prefix + '_x',
            value: 0
        }, {
            xtype: 'textfield',
            fieldLabel: 'Y',
            name: prefix + '_y',
            value: 0
        }, {
            xtype: 'textfield',
            fieldLabel: 'Макс. ширина',
            name: prefix + '_max_width',
            value: 0
        }, {
            xtype: 'textfield',
            fieldLabel: 'Размер шрифта',
            name: prefix + '_font_size',
            value: defaultSize
        }, {
            xtype: 'textfield',
            fieldLabel: 'Цвет',
            name: prefix + '_color',
            value: defaultColor
        }, {
            xtype: 'combo',
            hiddenName: prefix + '_align',
            fieldLabel: 'Выравнивание',
            mode: 'local',
            editable: false,
            triggerAction: 'all',
            store: new Ext.data.ArrayStore({fields: ['value','label'], data: [['left','Слева'],['center','По центру'],['right','Справа']]}),
            valueField: 'value',
            displayField: 'label',
            value: 'left'
        }];
    },

    loadForm: function() {
        var form = this.getForm();
        this.getEl().mask('Загружаем шаблон сертификата...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/course/certificate/get', course_id: this.courseId},
            listeners: {
                success: {fn: function(r) {
                    this.getEl().unmask();
                    var obj = Training.utils.getResultData(r);
                    form.setValues(obj);
                    Training.utils.setCheckboxValue(form, 'is_active', obj.is_active);
                }, scope: this},
                failure: {fn: function(r) {
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось загрузить настройки сертификата');
                }, scope: this}
            }
        });
    },

    saveForm: function() {
        var form = this.getForm();
        if (!form.isValid()) { return false; }
        var values = form.getValues();
        values.action = 'mgr/course/certificate/update';
        values.course_id = this.courseId;
        values.is_active = Training.utils.toBool(form.findField('is_active').getValue()) ? 1 : 0;
        this.getEl().mask('Сохраняем шаблон сертификата...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: values,
            listeners: {
                success: {fn: function() {
                    this.getEl().unmask();
                    this.loadForm();
                    var grid = Ext.getCmp('training-grid-course-certificates');
                    if (grid) { grid.refresh(); }
                    MODx.msg.alert('Готово', 'Настройки сертификата сохранены');
                }, scope: this},
                failure: {fn: function(r) {
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось сохранить сертификат');
                }, scope: this}
            }
        });
    },

    generateAll: function() {
        this.getEl().mask('Генерируем сертификаты...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/course/certificate/generate', course_id: this.courseId, force: 1},
            listeners: {
                success: {fn: function(r) {
                    this.getEl().unmask();
                    var grid = Ext.getCmp('training-grid-course-certificates');
                    if (grid) { grid.refresh(); }
                    var obj = Training.utils.getResultData(r);
                    MODx.msg.alert('Готово', 'Сгенерировано сертификатов: ' + (obj.generated_count || 0));
                }, scope: this},
                failure: {fn: function(r) {
                    this.getEl().unmask();
                    MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось сгенерировать сертификаты');
                }, scope: this}
            }
        });
    }
});
Ext.reg('training-course-certificate-form', Training.form.CourseCertificate);


Training.grid.CourseCertificates = function(config) {
    config = config || {};
    this.courseId = parseInt(config.courseId || Training.config.course_id || 0, 10) || 0;
    this.sm = new Ext.grid.CheckboxSelectionModel({width: 30});

    var fields = [
        'id',
        'user_id',
        'user_course_id',
        'display_name',
        'email',
        'completedon',
        'completedon_formatted',
        'certificate_id',
        'certificate_generated',
        'certificate_generated_label',
        'certificate_status',
        'issuedon',
        'issuedon_formatted',
        'file_path',
        'preview_image'
    ];

    this.store = new Ext.data.JsonStore({
        url: Training.config.connector_url,
        root: 'results',
        totalProperty: 'total',
        idProperty: 'id',
        fields: fields,
        remoteSort: false,
        baseParams: {
            action: 'mgr/course/certificates/getlist',
            course_id: this.courseId
        },
        listeners: {
            exception: function(proxy, type, action, options, response) {
                var message = 'Не удалось загрузить завершивших курс пользователей';
                try {
                    var data = Ext.decode(response.responseText || '{}') || {};
                    if (data.message) {
                        message = data.message;
                    }
                } catch (e) {}
                MODx.msg.alert('Сертификаты', message);
            }
        }
    });

    this.pagingToolbar = new Ext.PagingToolbar({
        pageSize: 20,
        store: this.store,
        displayInfo: true,
        displayMsg: 'Показано {0} - {1} из {2}',
        emptyMsg: 'Нет данных для отображения'
    });

    Ext.applyIf(config, {
        id: 'training-grid-course-certificates',
        cls: 'container training-certificate-grid',
        border: true,
        loadMask: true,
        stripeRows: true,
        autoHeight: true,
        minHeight: 120,
        store: this.store,
        sm: this.sm,
        columns: [
            this.sm,
            {header:'ID пользователя', dataIndex:'user_id', width:90},
            {header:'Пользователь', dataIndex:'display_name', width:250},
            {header:'Email', dataIndex:'email', width:220},
            {header:'Завершил курс', dataIndex:'completedon_formatted', width:140},
            {header:'Сертификат создан', dataIndex:'certificate_generated_label', width:130},
            {header:'Выдан', dataIndex:'issuedon_formatted', width:140},
            {header:'Файл', dataIndex:'file_path', width:300, renderer: function(value) {
                value = value || '';
                if (!value || value === '—') {
                    return '—';
                }
                if (Training.utils && Training.utils.renderFileLink) {
                    return Training.utils.renderFileLink(value);
                }
                return '<a href="' + Ext.util.Format.htmlEncode(value) + '" target="_blank">' + Ext.util.Format.htmlEncode(value) + '</a>';
            }}
        ],
        viewConfig: {
            forceFit: false,
            emptyText: 'Нет завершивших курс пользователей для отображения',
            deferEmptyText: false
        },
        tbar: [{
            text: 'Сгенерировать выбранным',
            minWidth: 180,
            handler: function() {
                var ids = this.getSelectedUserIds();
                if (!ids.length) {
                    MODx.msg.alert('Сертификаты', 'Выберите пользователей');
                    return;
                }
                this.generate(ids);
            },
            scope: this
        }, '-', {
            text: 'Обновить',
            minWidth: 100,
            handler: function() { this.refresh(); },
            scope: this
        }],
        bbar: this.pagingToolbar
    });

    Training.grid.CourseCertificates.superclass.constructor.call(this, config);

    this.on('afterrender', function() {
        this.refresh();
    }, this);
};
Ext.extend(Training.grid.CourseCertificates, Ext.grid.GridPanel, {
    getSelectedUserIds: function() {
        var ids = [];
        var seen = {};
        var records = this.getSelectionModel().getSelections() || [];

        Ext.each(records, function(rec) {
            var id = parseInt(rec.get('user_id') || rec.get('id') || 0, 10) || 0;
            if (id > 0 && !seen[id]) {
                seen[id] = true;
                ids.push(id);
            }
        });

        return ids;
    },

    refresh: function() {
        var store = this.getStore();
        if (!store) { return; }

        this.courseId = parseInt(this.courseId || Training.config.course_id || 0, 10) || 0;

        store.baseParams = store.baseParams || {};
        store.baseParams.action = 'mgr/course/certificates/getlist';
        store.baseParams.course_id = this.courseId;

        store.load({
            params: {
                start: 0,
                limit: 20,
                action: 'mgr/course/certificates/getlist',
                course_id: this.courseId
            }
        });
    },

    generate: function(ids) {
        this.getEl().mask('Генерация сертификатов...');
        Ext.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/certificate/generate',
                course_id: this.courseId,
                force: 1,
                user_ids: ids.join(',')
            },
            success: function(response) {
                this.getEl().unmask();
                this.refresh();

                var data = {};
                try {
                    data = Ext.decode(response.responseText || '{}') || {};
                } catch (e) {}

                var obj = data.object || data.results || data || {};
                MODx.msg.alert('Готово', 'Сгенерировано сертификатов: ' + (obj.generated_count || 0));
            },
            failure: function(response) {
                this.getEl().unmask();
                var message = 'Не удалось сгенерировать сертификаты';
                try {
                    var data = Ext.decode(response.responseText || '{}') || {};
                    if (data.message) { message = data.message; }
                } catch (e) {}
                MODx.msg.alert('Ошибка', message);
            },
            scope: this
        });
    }
});
Ext.reg('training-grid-course-certificates', Training.grid.CourseCertificates);

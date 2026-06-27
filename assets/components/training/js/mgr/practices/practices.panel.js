(function () {
    'use strict';

    function initTrainingPractices() {
        if (!window.Ext || !window.MODx || !Ext.ns || !MODx.Panel || !MODx.grid || !MODx.Window) {
            window.setTimeout(initTrainingPractices, 50);
            return;
        }

        Ext.ns('Training');
        Ext.ns('Training.config');
        Ext.ns('Training.panel');
        Ext.ns('Training.form');
        Ext.ns('Training.grid');
        Ext.ns('Training.window');
        Ext.ns('Training.combo');
        Ext.ns('Training.utils');

        Training.config = Training.config || {};
        Training.config.connector_url = Training.config.connector_url || (MODx.config.assets_url + 'components/training/connector.php');

        Training.utils.toBool = Training.utils.toBool || function(value) {
            return value === true || value === 1 || value === '1' || value === 'true' || value === 'yes';
        };

        Training.utils.renderBoolean = Training.utils.renderBoolean || function(value) {
            var active = Training.utils.toBool(value);
            if (active) {
                return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#e6f6ea;color:#1f7a35;font-weight:600;">Да</span>';
            }
            return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#f3f3f3;color:#888;font-weight:600;">Нет</span>';
        };

        Training.utils.escapeHtml = Training.utils.escapeHtml || function(value) {
            value = value == null ? '' : String(value);
            return value
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };


        Training.utils.setCheckboxValue = Training.utils.setCheckboxValue || function(form, name, value) {
            if (!form || !form.findField) {
                return;
            }
            var field = form.findField(name);
            if (field && field.setValue) {
                field.setValue(Training.utils.toBool(value) ? 1 : 0);
            }
        };

        Training.utils.getSelectedRecord = function(grid) {
            if (!grid) {
                return null;
            }

            var sm = grid.getSelectionModel ? grid.getSelectionModel() : grid.sm;
            if (!sm) {
                return null;
            }

            if (sm.getSelected) {
                return sm.getSelected();
            }

            if (sm.getSelections) {
                var rows = sm.getSelections();
                return rows && rows.length ? rows[0] : null;
            }

            return null;
        };

                Training.combo.PracticeStatus = function(config) {
            config = config || {};

            Ext.applyIf(config, {
                store: new Ext.data.SimpleStore({
                    fields: ['value', 'label'],
                    data: [
                        ['', 'Все статусы'],
                        ['submitted', 'Отправлено'],
                        ['in_review', 'На проверке'],
                        ['revision', 'На доработке'],
                        ['approved', 'Принято'],
                        ['rejected', 'Отклонено']
                    ]
                }),
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                triggerAction: 'all',
                editable: false,
                forceSelection: true,
                emptyText: 'Все статусы'
            });

            Training.combo.PracticeStatus.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.combo.PracticeStatus, Ext.form.ComboBox);
        Ext.reg('training-combo-practice-status', Training.combo.PracticeStatus);
Training.combo.PracticeActive = function(config) {
            config = config || {};
            Ext.applyIf(config, {
                hiddenName: config.name || 'active',
                store: new Ext.data.SimpleStore({
                    fields: ['value', 'label'],
                    data: [[1, 'Да'], [0, 'Нет']]
                }),
                displayField: 'label',
                valueField: 'value',
                mode: 'local',
                triggerAction: 'all',
                editable: false,
                value: 1,
                anchor: '100%'
            });
            Training.combo.PracticeActive.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.combo.PracticeActive, MODx.combo.ComboBox);
        Ext.reg('training-combo-practice-active', Training.combo.PracticeActive);

        Training.combo.PracticeCourses = function(config) {
            config = config || {};

            Ext.applyIf(config, {
                hiddenName: config.name || 'course_id',
                fieldLabel: config.fieldLabel || 'Курс',
                valueField: 'id',
                displayField: 'display',
                editable: true,
                forceSelection: true,
                triggerAction: 'all',
                minChars: 1,
                pageSize: 20,
                mode: 'remote',
                anchor: '100%',
                emptyText: 'Начните вводить название курса...',
                store: new Ext.data.JsonStore({
                    url: Training.config.connector_url,
                    root: 'results',
                    totalProperty: 'total',
                    idProperty: 'id',
                    fields: ['id', 'resource_id', 'pagetitle', 'display', 'is_active'],
                    baseParams: {
                        action: 'mgr/practice/courses/getlist'
                    }
                })
            });

            Training.combo.PracticeCourses.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.combo.PracticeCourses, MODx.combo.ComboBox);
        Ext.reg('training-combo-practice-courses', Training.combo.PracticeCourses);

        Training.combo.PracticeModules = function(config) {
            config = config || {};
            this.courseId = parseInt(config.courseId || 0, 10) || 0;

            Ext.applyIf(config, {
                hiddenName: config.name || 'module_id',
                fieldLabel: config.fieldLabel || 'Модуль',
                valueField: 'id',
                displayField: 'display',
                editable: true,
                forceSelection: true,
                triggerAction: 'all',
                minChars: 1,
                pageSize: 20,
                mode: 'remote',
                anchor: '100%',
                emptyText: 'Сначала выберите курс...',
                store: new Ext.data.JsonStore({
                    url: Training.config.connector_url,
                    root: 'results',
                    totalProperty: 'total',
                    idProperty: 'id',
                    fields: ['id', 'course_id', 'resource_id', 'pagetitle', 'display', 'is_active'],
                    baseParams: {
                        action: 'mgr/practice/modules/getlist',
                        course_id: this.courseId
                    }
                })
            });

            Training.combo.PracticeModules.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.combo.PracticeModules, MODx.combo.ComboBox, {
            setCourse: function(courseId) {
                courseId = parseInt(courseId || 0, 10) || 0;
                this.courseId = courseId;
                this.store.baseParams.course_id = courseId;
                this.emptyText = courseId > 0 ? 'Начните вводить название модуля...' : 'Сначала выберите курс...';
                this.applyEmptyText();
            }
        });
        Ext.reg('training-combo-practice-modules', Training.combo.PracticeModules);

                Training.panel.Practices = function(config) {
            config = config || {};

            Ext.apply(config, {
                id: 'training-panel-practices',
                baseCls: 'modx-formpanel',
                layout: 'anchor',
                hideMode: 'offsets',
                items: [{
                    html: '<h2>Практические задания</h2>',
                    style: {margin: '15px 0'}
                }, {
                    xtype: 'training-practice-tabs',
                    cls: 'main-wrapper',
                    preventRender: true,
                    anchor: '100%'
                }]
            });

            Training.panel.Practices.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.panel.Practices, MODx.Panel);
        Ext.reg('training-panel-practices', Training.panel.Practices);

        Training.panel.PracticeTabs = function(config) {
            config = config || {};

            Ext.apply(config, {
                id: 'training-practices-tabs',
                border: true,
                deferredRender: false,
                defaults: {
                    border: false,
                    autoHeight: true
                },
                items: [{
                    title: 'Задания',
                    xtype: 'training-grid-practices'
                }, {
                    title: 'Попытки и переписка',
                    xtype: 'training-panel-practice-review'
                }]
            });

            Training.panel.PracticeTabs.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.panel.PracticeTabs, MODx.Tabs);
        Ext.reg('training-practice-tabs', Training.panel.PracticeTabs);
Training.grid.Practices = function(config) {
            config = config || {};
            this.sm = new Ext.grid.CheckboxSelectionModel();

            Ext.applyIf(config, {
                id: 'training-grid-practices',
                cls: 'modx-grid training-grid-practices',
                url: Training.config.connector_url,
                baseParams: {
                    action: 'mgr/practice/practices/getlist'
                },
                save_action: 'mgr/practice/practices/update',
                autosave: false,
                sm: this.sm,
                fields: [
                    'id','course_id','module_id','title','description','template_file','template_file_name','image',
                    'deadline_at','deadline_at_formatted','deadline_days','allowed_extensions','max_file_size',
                    'active','active_text','rank','createdon','createdon_formatted','editedon','editedon_formatted',
                    'course_title','module_title','course_display','module_display'
                ],
                paging: true,
                remoteSort: true,
                sortBy: 'rank',
                sortDir: 'ASC',
                sortInfo: {field: 'rank', direction: 'ASC'},
                anchor: '100%',
                autoHeight: true,
                columns: [
                    this.sm,
                    {header: 'ID', dataIndex: 'id', width: 55, sortable: true},
                    {header: 'Курс', dataIndex: 'course_display', width: 230, sortable: true},
                    {header: 'Модуль', dataIndex: 'module_display', width: 240, sortable: true},
                    {header: 'Название', dataIndex: 'title', width: 360, sortable: true},
                    {header: 'Активно', dataIndex: 'active', width: 90, sortable: true, renderer: Training.utils.renderBoolean},
                    {header: 'Срок', dataIndex: 'deadline_at_formatted', width: 150, sortable: true},
                    {header: 'Ранг', dataIndex: 'rank', width: 80, sortable: true}
                ],
                tbar: [{
                    text: 'Создать задание',
                    handler: this.createPractice,
                    scope: this
                }, {
                    text: 'Изменить',
                    handler: this.updatePractice,
                    scope: this
                }, {
                    text: 'Удалить',
                    handler: this.removePractice,
                    scope: this
                }, '->', {
                    xtype: 'textfield',
                    id: 'training-practices-search',
                    emptyText: 'Поиск...',
                    width: 260,
                    listeners: {
                        render: {
                            fn: function(tf) {
                                tf.getEl().on('keyup', function(e) {
                                    if (e.getKey() === Ext.EventObject.ENTER) {
                                        this.search();
                                    }
                                }, this);
                            },
                            scope: this
                        }
                    }
                }, {
                    text: 'Найти',
                    handler: this.search,
                    scope: this
                }, {
                    text: 'Сбросить',
                    handler: this.resetSearch,
                    scope: this
                }]
            });

            Training.grid.Practices.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.grid.Practices, MODx.grid.Grid, {
            getMenu: function() {
                this.addContextMenuItem([{
                    text: 'Изменить',
                    handler: this.updatePractice,
                    scope: this
                }, '-', {
                    text: 'Удалить / отключить',
                    handler: this.removePractice,
                    scope: this
                }]);
            },

            search: function() {
                var field = Ext.getCmp('training-practices-search');
                this.getStore().baseParams.query = field ? field.getValue() : '';
                this.getBottomToolbar().changePage(1);
            },

            resetSearch: function() {
                var field = Ext.getCmp('training-practices-search');
                if (field) {
                    field.setValue('');
                }
                this.getStore().baseParams.query = '';
                this.getBottomToolbar().changePage(1);
            },

            createPractice: function(btn, e) {
                var win = MODx.load({
                    xtype: 'training-window-practice',
                    title: 'Создать практическое задание',
                    action: 'mgr/practice/practices/create',
                    listeners: {
                        success: {
                            fn: this.refresh,
                            scope: this
                        }
                    }
                });
                win.show(e ? e.target : btn.getEl());
            },

            updatePractice: function(btn, e) {
                var rec = this.menu && this.menu.record ? this.menu.record : Training.utils.getSelectedRecord(this);
                if (!rec) {
                    MODx.msg.alert('Ошибка', 'Выберите практическое задание');
                    return false;
                }

                var win = MODx.load({
                    xtype: 'training-window-practice',
                    title: 'Изменить практическое задание',
                    action: 'mgr/practice/practices/update',
                    record: rec.data || rec,
                    listeners: {
                        success: {
                            fn: this.refresh,
                            scope: this
                        }
                    }
                });
                win.show(e ? e.target : false);
            },

            removePractice: function() {
                var rec = this.menu && this.menu.record ? this.menu.record : Training.utils.getSelectedRecord(this);
                if (!rec) {
                    MODx.msg.alert('Ошибка', 'Выберите практическое задание');
                    return false;
                }

                MODx.msg.confirm({
                    title: 'Удалить задание',
                    text: 'Если у задания уже есть попытки, оно будет отключено. Продолжить?',
                    url: Training.config.connector_url,
                    params: {
                        action: 'mgr/practice/practices/remove',
                        id: rec.id || rec.data.id
                    },
                    listeners: {
                        success: {
                            fn: this.refresh,
                            scope: this
                        }
                    }
                });
            }
        });
        Ext.reg('training-grid-practices', Training.grid.Practices);

        Training.window.Practice = function(config) {
            config = config || {};
            this.record = config.record || null;
            this.isUpdate = !!this.record;
            this.courseFieldId = Ext.id();
            this.moduleFieldId = Ext.id();

            Ext.applyIf(config, {
                width: 760,
                autoHeight: true,
                url: Training.config.connector_url,
                action: config.action || 'mgr/practice/practices/create',
                labelAlign: 'top',
                fields: [{
                    xtype: 'hidden',
                    name: 'id'
                }, {
                    xtype: 'training-combo-practice-courses',
                    id: this.courseFieldId,
                    name: 'course_id',
                    allowBlank: false,
                    listeners: {
                        select: {
                            fn: function(combo, record) {
                                var moduleCombo = Ext.getCmp(this.moduleFieldId);
                                if (!moduleCombo) {
                                    return;
                                }
                                moduleCombo.setCourse(record.get('id'));
                                moduleCombo.setValue('');
                                moduleCombo.setRawValue('');
                                moduleCombo.store.removeAll();
                            },
                            scope: this
                        }
                    }
                }, {
                    xtype: 'training-combo-practice-modules',
                    id: this.moduleFieldId,
                    name: 'module_id',
                    allowBlank: false
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Название',
                    name: 'title',
                    anchor: '100%',
                    allowBlank: false
                }, {
                    xtype: 'textarea',
                    fieldLabel: 'Описание / инструкция',
                    name: 'description',
                    anchor: '100%',
                    height: 120
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Файл задания / шаблон',
                    name: 'template_file',
                    anchor: '100%',
                    emptyText: 'assets/.../template.docx'
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Название файла для пользователя',
                    name: 'template_file_name',
                    anchor: '100%'
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Картинка задания',
                    name: 'image',
                    anchor: '100%',
                    emptyText: 'theme/images/training/tests/logo-scanport.svg'
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Срок до даты',
                    name: 'deadline_at',
                    anchor: '100%',
                    emptyText: '2026-05-30 18:00:00'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Срок дней',
                    name: 'deadline_days',
                    anchor: '100%',
                    allowDecimals: false,
                    allowNegative: false,
                    value: 0
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Ранг',
                    name: 'rank',
                    anchor: '100%',
                    allowDecimals: false,
                    allowNegative: false,
                    value: 0
                }, {
                    xtype: 'xcheckbox',
                    boxLabel: 'Активно',
                    hideLabel: true,
                    name: 'active',
                    checked: true
                }, {
                    xtype: 'textfield',
                    fieldLabel: 'Разрешенные расширения',
                    name: 'allowed_extensions',
                    anchor: '100%',
                    value: 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Макс. размер файла, байт',
                    name: 'max_file_size',
                    anchor: '100%',
                    allowDecimals: false,
                    allowNegative: false,
                    value: 52428800
                }]
            });

            Training.window.Practice.superclass.constructor.call(this, config);
            this.on('afterrender', this.applyRecord, this);
        };
        Ext.extend(Training.window.Practice, MODx.Window, {
            applyRecord: function() {
                var form = this.fp ? this.fp.getForm() : null;
                if (!form) {
                    return;
                }

                if (!this.record) {
                    form.setValues({
                        active: 1,
                        allowed_extensions: 'pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip',
                        max_file_size: 52428800,
                        rank: 0,
                        deadline_days: 0,
                        image: 'theme/images/training/tests/logo-scanport.svg'
                    });
                    return;
                }

                form.setValues(this.record);
                Training.utils.setCheckboxValue(form, 'active', this.record.active);

                var courseCombo = Ext.getCmp(this.courseFieldId);
                if (courseCombo) {
                    courseCombo.setValue(this.record.course_id || 0);
                    courseCombo.setRawValue(this.record.course_display || this.record.course_title || ('#' + this.record.course_id));
                }

                var moduleCombo = Ext.getCmp(this.moduleFieldId);
                if (moduleCombo) {
                    moduleCombo.setCourse(this.record.course_id || 0);
                    moduleCombo.setValue(this.record.module_id || 0);
                    moduleCombo.setRawValue(this.record.module_display || this.record.module_title || ('#' + this.record.module_id));
                }
            }
        });
        Ext.reg('training-window-practice', Training.window.Practice);

        Training.panel.PracticeReview = function(config) {
            config = config || {};

            Ext.applyIf(config, {
                id: 'training-panel-practice-review',
                layout: 'anchor',
                border: false,
items: [{
                    xtype: 'training-grid-practice-attempts',
                    preventRender: true,
                    anchor: '100%',
                    listeners: {
                        rowclick: function(grid, rowIndex) {
                            var rec = grid.getStore().getAt(rowIndex);
                            var messagesGrid = Ext.getCmp('training-grid-practice-messages');
                            if (messagesGrid && rec) {
                                messagesGrid.setAttempt(rec.data.id, rec.data);
                            }
                        }
                    }
                }, {
                    xtype: 'training-grid-practice-messages',
                    preventRender: true,
                    anchor: '100%'
                }, {
                    xtype: 'training-panel-practice-reply',
                    preventRender: true,
                    anchor: '100%'
                }]
            });

            Training.panel.PracticeReview.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.panel.PracticeReview, MODx.Panel);
        Ext.reg('training-panel-practice-review', Training.panel.PracticeReview);

        Training.grid.PracticeAttempts = function(config) {
            config = config || {};
            this.sm = new Ext.grid.CheckboxSelectionModel();

            Ext.applyIf(config, {
                id: 'training-grid-practice-attempts',
                cls: 'modx-grid training-grid-practice-attempts',
                url: Training.config.connector_url,
                baseParams: {
                    action: 'mgr/practice/attempts/getlist'
                },
                sm: this.sm,
                fields: [
                    'id','practice_id','practice_title','course_id','course_display','module_id','module_display',
                    'user_id','user_display','email','attempt_num','attempt_no','status','status_text','deadline_at_formatted',
                    'createdon_formatted','submittedon_formatted','reviewedon_formatted','is_latest'
                ],
                paging: true,
                remoteSort: true,
                anchor: '100%',
                autoHeight: true,
                columns: [
                    this.sm,
                    {header: 'ID', dataIndex: 'id', width: 55, sortable: true},
                    {header: 'Задание', dataIndex: 'practice_title', width: 260, sortable: true},
                    {header: 'Пользователь', dataIndex: 'user_display', width: 180, sortable: true},
                    {header: 'Статус', dataIndex: 'status_text', width: 130, sortable: true},
                    {header: 'Попытка', dataIndex: 'attempt_num', width: 80, sortable: true, renderer: function(value, meta, rec) { return value || rec.data.attempt_no || 1; }},
                    {header: 'Отправлено', dataIndex: 'submittedon_formatted', width: 150, sortable: true},
                    {header: 'Курс', dataIndex: 'course_display', width: 180, sortable: true},
                    {header: 'Модуль', dataIndex: 'module_display', width: 180, sortable: true}
                ],
                tbar: [{
                    xtype: 'textfield',
                    id: 'training-practice-attempts-search',
                    emptyText: 'Поиск по пользователю или заданию...',
                    width: 300,
                    listeners: {
                        render: {
                            fn: function(tf) {
                                tf.getEl().on('keyup', function(e) {
                                    if (e.getKey() === Ext.EventObject.ENTER) {
                                        this.search();
                                    }
                                }, this);
                            },
                            scope: this
                        }
                    }
                }, {
                    xtype: 'training-combo-practice-status',
                    id: 'training-practice-attempts-status',
                    width: 170,
                    listeners: {
                        select: {
                            fn: this.search,
                            scope: this
                        }
                    }
                }, {
                    text: 'Найти',
                    handler: this.search,
                    scope: this
                }, {
                    text: 'Сбросить',
                    handler: this.resetSearch,
                    scope: this
                }, '-', {
                    text: 'Принять',
                    handler: function() { this.setStatus('approved'); },
                    scope: this
                }, {
                    text: 'На доработку',
                    handler: function() { this.setStatus('revision'); },
                    scope: this
                }, {
                    text: 'Отклонить',
                    handler: function() { this.setStatus('rejected'); },
                    scope: this
                }, '->', {
                    text: 'Обновить',
                    handler: function() { this.refresh(); },
                    scope: this
                }]
            });

            Training.grid.PracticeAttempts.superclass.constructor.call(this, config);

            this.getStore().on('load', function(store) {
                var messagesGrid = Ext.getCmp('training-grid-practice-messages');
                if (!messagesGrid) {
                    return;
                }

                if (!store || !store.getCount || store.getCount() <= 0) {
                    messagesGrid.setAttempt(0, null);
                    return;
                }

                var rec = store.getAt(0);
                if (rec) {
                    var sm = this.getSelectionModel ? this.getSelectionModel() : null;
                    if (sm && sm.grid && typeof sm.selectRow === 'function') {
                        try {
                            sm.selectRow(0);
                        } catch (e) {
                            // В старом ExtJS/MODX selection model иногда еще не привязан к grid.
                            // Это не должно ломать загрузку переписки.
                        }
                    }
                    messagesGrid.setAttempt(rec.data.id, rec.data);
                }
            }, this);
        };
        Ext.extend(Training.grid.PracticeAttempts, MODx.grid.Grid, {
            getMenu: function() {
                this.addContextMenuItem([{
                    text: 'На проверке',
                    handler: function() {
                        this.setStatus('in_review');
                    },
                    scope: this
                }, {
                    text: 'Принять',
                    handler: function() {
                        this.setStatus('approved');
                    },
                    scope: this
                }, {
                    text: 'На доработку',
                    handler: function() {
                        this.setStatus('revision');
                    },
                    scope: this
                }, {
                    text: 'Отклонить',
                    handler: function() {
                        this.setStatus('rejected');
                    },
                    scope: this
                }]);
            },

            search: function() {
                var query = Ext.getCmp('training-practice-attempts-search');
                var status = Ext.getCmp('training-practice-attempts-status');
                this.getStore().baseParams.query = query ? query.getValue() : '';
                this.getStore().baseParams.status = status ? status.getValue() : '';
                this.getBottomToolbar().changePage(1);
            },

            resetSearch: function() {
                var query = Ext.getCmp('training-practice-attempts-search');
                var status = Ext.getCmp('training-practice-attempts-status');
                if (query) {
                    query.setValue('');
                }
                if (status) {
                    status.setValue('');
                }
                this.getStore().baseParams.query = '';
                this.getStore().baseParams.status = '';
                this.getBottomToolbar().changePage(1);
            },

            setStatus: function(status) {
                var rec = this.menu && this.menu.record ? this.menu.record : Training.utils.getSelectedRecord(this);
                if (!rec) {
                    MODx.msg.alert('Ошибка', 'Выберите попытку');
                    return false;
                }

                var labels = {
                    in_review: 'На проверке',
                    approved: 'Принято',
                    revision: 'На доработку',
                    rejected: 'Отклонено'
                };

                MODx.msg.confirm({
                    title: 'Сменить статус',
                    text: 'Изменить статус попытки на «' + (labels[status] || status) + '»?',
                    url: Training.config.connector_url,
                    params: {
                        action: 'mgr/practice/attempts/status',
                        id: rec.id || rec.data.id,
                        status: status
                    },
                    listeners: {
                        success: {
                            fn: function() {
                                this.refresh();
                                var messagesGrid = Ext.getCmp('training-grid-practice-messages');
                                if (messagesGrid) {
                                    messagesGrid.refresh();
                                }
                            },
                            scope: this
                        }
                    }
                });
            }
        });
        Ext.reg('training-grid-practice-attempts', Training.grid.PracticeAttempts);

                        Training.panel.PracticeReply = function(config) {
            config = config || {};

            Ext.apply(config, {
                id: 'training-panel-practice-reply',
                border: false,
                autoHeight: true,
                layout: 'form',
                labelAlign: 'top',
                hideLabels: true,
                bodyStyle: 'padding:0 0 8px 0;',
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    xtype: 'fieldset',
                    title: 'Ответ пользователю',
                    autoHeight: true,
                    layout: 'form',
                    labelAlign: 'top',
                    hideLabels: true,
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        xtype: 'textarea',
                        id: 'training-practice-message-text',
                        name: 'message',
                        hideLabel: true,
                        emptyText: 'Введите ответ пользователю...',
                        height: 120,
                        grow: false
                    }, {
                        xtype: 'xcheckbox',
                        id: 'training-practice-message-done',
                        name: 'mark_done',
                        boxLabel: 'Задание выполнено',
                        hideLabel: true,
                        inputValue: 1
                    }]
                }],
                buttons: [{
                    id: 'training-practice-message-send',
                    text: 'Отправить ответ',
                    handler: function() {
                        var messagesGrid = Ext.getCmp('training-grid-practice-messages');

                        if (messagesGrid && messagesGrid.sendMessage) {
                            messagesGrid.sendMessage();
                        }
                    }
                }]
            });

            Training.panel.PracticeReply.superclass.constructor.call(this, config);
        };
        Ext.extend(Training.panel.PracticeReply, MODx.Panel);
        Ext.reg('training-panel-practice-reply', Training.panel.PracticeReply);Training.grid.PracticeMessages = function(config) {
            config = config || {};

            Ext.applyIf(config, {
                id: 'training-grid-practice-messages',
                cls: 'modx-grid training-grid-practice-messages',
                url: Training.config.connector_url,
                baseParams: {
                    action: 'mgr/practice/messages/getlist',
                    attempt_id: 0
                },
                fields: [
                    'id','attempt_id','practice_id','author_id','author_type','author_display','message',
                    'message_html','files_html','files_text','is_system','createdon_formatted'
                ],
                paging: false,
                remoteSort: false,
                anchor: '100%',
                autoHeight: true,
                title: 'Переписка по выбранной попытке',
                columns: [{
                    header: 'Дата',
                    dataIndex: 'createdon_formatted',
                    width: 140
                }, {
                    header: 'Автор',
                    dataIndex: 'author_display',
                    width: 160
                }, {
                    header: 'Тип',
                    dataIndex: 'author_type',
                    width: 90
                }, {
                    header: 'Сообщение',
                    dataIndex: 'message_html',
                    width: 420,
                    renderer: function(value) {
                        return '<div class="training-practice-message-cell">' + (value || '') + '</div>';
                    }
                }, {
                    header: 'Файлы',
                    dataIndex: 'files_html',
                    width: 260,
                    renderer: function(value) {
                        return '<div class="training-practice-files-cell">' + (value || '') + '</div>';
                    }
                }],
                tbar: [{
                    xtype: 'displayfield',
                    id: 'training-practice-selected-attempt',
                    value: 'Выберите попытку выше'
                }]
            });

            Training.grid.PracticeMessages.superclass.constructor.call(this, config);
            this.attemptId = 0;
        };
        Ext.extend(Training.grid.PracticeMessages, MODx.grid.Grid, {
            setAttempt: function(attemptId, record) {
                this.attemptId = parseInt(attemptId || 0, 10) || 0;

                var store = this.getStore ? this.getStore() : null;
                if (store) {
                    store.baseParams = store.baseParams || {};
                    store.baseParams.action = 'mgr/practice/messages/getlist';
                    store.baseParams.attempt_id = this.attemptId;
                }
                if (this.config && this.config.baseParams) {
                    this.config.baseParams.attempt_id = this.attemptId;
                }
                if (this.baseParams) {
                    this.baseParams.attempt_id = this.attemptId;
                }

                var label = this.attemptId > 0 ? ('Попытка #' + this.attemptId) : 'Выберите попытку выше';
                if (record) {
                    label += ' — ' + (record.user_display || '') + ' / ' + (record.practice_title || '');
                }

                var display = Ext.getCmp('training-practice-selected-attempt');
                if (display) {
                    display.setValue(Training.utils.escapeHtml(label));
                }

                var textField = Ext.getCmp('training-practice-message-text');
                if (textField) {
                    textField.setValue('');
                }

                var doneField = Ext.getCmp('training-practice-message-done');
                if (doneField) {
                    doneField.setValue(false);
                }

                this.loadMessages();
            },

            loadMessages: function() {
                var store = this.getStore ? this.getStore() : null;
                if (!store) {
                    return false;
                }

                store.baseParams = store.baseParams || {};
                store.baseParams.action = 'mgr/practice/messages/getlist';
                store.baseParams.attempt_id = this.attemptId || 0;

                if (!this.attemptId) {
                    if (store.removeAll) {
                        store.removeAll();
                    }
                    return false;
                }

                store.load({
                    params: {
                        action: 'mgr/practice/messages/getlist',
                        attempt_id: this.attemptId,
                        start: 0,
                        limit: 0
                    }
                });
                return true;
            },

            refresh: function() {
                return this.loadMessages();
            },

            getCurrentAttemptId: function() {
                var attemptId = parseInt(this.attemptId || 0, 10) || 0;
                if (attemptId > 0) {
                    return attemptId;
                }

                var attemptsGrid = Ext.getCmp('training-grid-practice-attempts');
                var rec = attemptsGrid ? Training.utils.getSelectedRecord(attemptsGrid) : null;
                if (rec && rec.data && rec.data.id) {
                    attemptId = parseInt(rec.data.id || 0, 10) || 0;
                    if (attemptId > 0) {
                        this.setAttempt(attemptId, rec.data);
                        return attemptId;
                    }
                }

                return 0;
            },

            setReplyBusy: function(isBusy) {
                var btn = Ext.getCmp('training-practice-message-send');
                if (btn) {
                    btn.setDisabled(!!isBusy);
                    btn.setText(isBusy ? 'Отправляем...' : 'Отправить ответ');
                }
            },

            sendMessage: function() {
                var textField = Ext.getCmp('training-practice-message-text');
                var doneField = Ext.getCmp('training-practice-message-done');
                var text = textField
                    ? Ext.util.Format.trim(String(textField.getValue() || ''))
                    : '';
                var markDone = doneField && doneField.getValue
                    ? !!doneField.getValue()
                    : false;

                var attemptId = this.getCurrentAttemptId();

                if (!attemptId) {
                    MODx.msg.alert('Ошибка', 'Сначала выберите попытку');
                    return false;
                }

                if (!text) {
                    if (markDone) {
                        text = 'Задание принято.';
                    } else {
                        MODx.msg.alert('Ошибка', 'Напишите ответ пользователю');
                        return false;
                    }
                }

                this.setReplyBusy(true);

                Ext.Ajax.request({
                    url: Training.config.connector_url,
                    method: 'POST',
                    params: {
                        action: 'mgr/practice/messages/create',
                        attempt_id: attemptId,
                        message: text,
                        mark_done: markDone ? 1 : 0,
                        send_revision: markDone ? 0 : 1
                    },
                    success: function(response) {
                        var res = null;
                        try {
                            res = Ext.decode(response.responseText || '{}');
                        } catch (e) {
                            res = null;
                        }

                        if (!res || res.success !== true) {
                            MODx.msg.alert('Ошибка', (res && res.message) ? res.message : 'Не удалось отправить ответ');
                            this.setReplyBusy(false);
                            return;
                        }

                        if (textField) {
                            textField.setValue('');
                        }
                        if (doneField) {
                            doneField.setValue(false);
                        }

                        this.attemptId = attemptId;
                        this.loadMessages();

                        var attempts = Ext.getCmp('training-grid-practice-attempts');
                        if (attempts) {
                            attempts.refresh();
                        }

                        MODx.msg.status({title: 'Готово', message: res.message || 'Ответ отправлен', delay: 2});
                        this.setReplyBusy(false);
                    },
                    failure: function(response) {
                        var message = 'Ошибка отправки ответа';
                        if (response && response.responseText) {
                            message += ': ' + response.responseText.substring(0, 300);
                        }
                        MODx.msg.alert('Ошибка', message);
                        this.setReplyBusy(false);
                    },
                    scope: this
                });

                return true;
            }
        });
        Ext.reg('training-grid-practice-messages', Training.grid.PracticeMessages);

        Ext.onReady(function() {
            if (!Ext.getCmp('training-panel-practices')) {
                MODx.add({xtype: 'training-panel-practices'});
            }
        });
    }

    initTrainingPractices();
})();

Training.panel.CourseProgress = function(config) {
    config = config || {};

    this.courseId = parseInt(config.courseId || Training.config.course_id || 0, 10) || 0;
    this.applyButtonId = Ext.id();
    this.previewButtonId = Ext.id();
    this.moduleFieldId = Ext.id();
    this.lessonFieldId = Ext.id();
    this.planPanelId = Ext.id();
    this.summaryPanelId = Ext.id();
    this.planReady = false;

    /*
     * ExtJS 3 Form.getValues() может вернуть пустую строку для ComboBox,
     * хотя пользователь уже выбрал запись. Храним реальные ID из select event
     * и используем их как надёжный источник для плана.
     */
    this.selectedUserId = 0;
    this.selectedModuleId = 0;
    this.selectedLessonId = 0;
    this.selectedMode = 'view';

    this.userStore = new Ext.data.JsonStore({
        url: Training.config.connector_url,
        root: 'results',
        totalProperty: 'total',
        idProperty: 'user_id',
        fields: [
            'user_id',
            'display_name',
            'email',
            'username',
            'status',
            'progress_percent',
            'completed_modules',
            'total_modules',
            'current_module_id',
            'current_module_label',
            'current_lesson_label'
        ],
        baseParams: {
            action: 'mgr/course/progress/users/getlist',
            course_id: this.courseId
        }
    });

    this.moduleStore = new Ext.data.JsonStore({
        url: Training.config.connector_url,
        root: 'results',
        totalProperty: 'total',
        idProperty: 'id',
        fields: [
            'id',
            'display_name',
            'menuindex',
            'is_active',
            'is_required',
            'status',
            'progress_percent',
            'completed',
            'lessons_total',
            'lessons_completed',
            'is_current'
        ],
        baseParams: {
            action: 'mgr/course/progress/modules/getlist',
            course_id: this.courseId
        }
    });

    this.lessonStore = new Ext.data.JsonStore({
        url: Training.config.connector_url,
        root: 'results',
        totalProperty: 'total',
        idProperty: 'id',
        fields: [
            'id',
            'display_name',
            'sort_order',
            'is_active',
            'duration_seconds',
            'status',
            'progress_percent',
            'completed',
            'is_current'
        ],
        baseParams: {
            action: 'mgr/course/progress/lessons/getlist',
            course_id: this.courseId,
            module_id: 0
        }
    });

    Ext.apply(config, {
        id: 'training-course-progress-panel',
        layout: 'anchor',
        autoHeight: true,
        border: false,
        cls: 'container training-course-progress-panel',
        defaults: {
            anchor: '100%'
        },
        items: [{
            xtype: 'panel',
            border: false,
            bodyStyle: 'padding:16px;background:#fff;border:1px solid #e5e5e5;border-radius:6px;',
            items: [{
                html: '<div style="padding:0 0 16px;color:#666;line-height:1.6;">'
                    + 'Режим «Просмотр прогресса» ничего не изменяет и показывает, где пользователь остановился. '
                    + 'В режимах назначения доступны только незавершённые модули и уроки. '
                    + 'Сначала показывается план: какие модули, уроки, обязательные тесты и практики будут закрыты. '
                    + 'После подтверждения изменения применяются одной транзакцией. '
                    + 'Перенос назад здесь намеренно отключён: для сброса прогресса нужен отдельный сценарий.</div>',
                border: false
            }, {
                xtype: 'form',
                id: 'training-course-progress-form',
                border: false,
                labelWidth: 180,
                defaults: {
                    anchor: '100%'
                },
                items: [{
                    xtype: 'combo',
                    fieldLabel: 'Пользователь курса',
                    name: 'user_id',
                    store: this.userStore,
                    valueField: 'user_id',
                    displayField: 'display_name',
                    mode: 'remote',
                    triggerAction: 'all',
                    editable: true,
                    typeAhead: true,
                    minChars: 0,
                    forceSelection: true,
                    allowBlank: false,
                    emptyText: 'Выберите пользователя',
                    listeners: {
                        select: {
                            fn: function(combo, record) {
                                this.selectedUserId = parseInt(record.get('user_id') || record.id || 0, 10) || 0;
                                this.selectedModuleId = 0;
                                this.selectedLessonId = 0;
                                this.resetPlan();
                                this.loadUserSummary(this.selectedUserId, this.isViewMode());
                                this.updateModeControls(true);
                            },
                            scope: this
                        }
                    }
                }, {
                    xtype: 'combo',
                    fieldLabel: 'Режим',
                    name: 'mode',
                    hiddenName: 'mode',
                    mode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    forceSelection: true,
                    allowBlank: false,
                    valueField: 'value',
                    displayField: 'label',
                    value: 'view',
                    store: new Ext.data.ArrayStore({
                        fields: ['value', 'label'],
                        data: [
                            ['view', 'Просмотр прогресса'],
                            ['lesson', 'Остановить на уроке'],
                            ['module', 'Завершить модуль']
                        ]
                    }),
                    listeners: {
                        select: {
                            fn: function(combo) {
                                var value = combo.getValue();
                                this.selectedMode = (value === 'view' || value === 'module') ? value : 'lesson';
                                this.resetPlan();
                                this.updateModeControls(true);

                                if (this.selectedUserId > 0 && this.isViewMode()) {
                                    this.loadUserSummary(this.selectedUserId, true);
                                }
                            },
                            scope: this
                        }
                    }
                }, {
                    xtype: 'combo',
                    id: this.moduleFieldId,
                    fieldLabel: 'Целевой модуль',
                    name: 'module_id',
                    store: this.moduleStore,
                    valueField: 'id',
                    displayField: 'display_name',
                    mode: 'remote',
                    triggerAction: 'all',
                    editable: false,
                    forceSelection: true,
                    allowBlank: false,
                    disabled: true,
                    emptyText: 'Сначала выберите пользователя',
                    listeners: {
                        select: {
                            fn: function(combo, record) {
                                this.selectedModuleId = parseInt(record.get('id') || record.id || 0, 10) || 0;
                                this.selectedLessonId = 0;
                                this.reloadLessons(this.selectedModuleId);
                                this.resetPlan();
                            },
                            scope: this
                        }
                    }
                }, {
                    xtype: 'combo',
                    id: this.lessonFieldId,
                    fieldLabel: 'Урок остановки',
                    name: 'lesson_id',
                    store: this.lessonStore,
                    valueField: 'id',
                    displayField: 'display_name',
                    /*
                     * Уроки загружаются вручную после выбора модуля.
                     * Local-режим здесь важен: ExtJS не должен выполнять второй
                     * удалённый запрос при клике по стрелке и терять module_id.
                     */
                    mode: 'local',
                    triggerAction: 'all',
                    editable: false,
                    forceSelection: true,
                    allowBlank: false,
                    disabled: true,
                    emptyText: 'Сначала выберите модуль',
                    listeners: {
                        select: {
                            fn: function(combo, record) {
                                this.selectedLessonId = parseInt(record.get('id') || record.id || 0, 10) || 0;
                                this.resetPlan();
                            },
                            scope: this
                        }
                    }
                }],
                buttons: [{
                    id: this.previewButtonId,
                    text: 'Показать прогресс',
                    minWidth: 150,
                    handler: this.requestPlan,
                    scope: this
                }, {
                    text: 'Обновить данные',
                    minWidth: 140,
                    handler: this.reloadData,
                    scope: this
                }, {
                    id: this.applyButtonId,
                    text: 'Применить план',
                    minWidth: 150,
                    disabled: true,
                    handler: this.confirmApply,
                    scope: this
                }]
            }, {
                xtype: 'panel',
                id: this.summaryPanelId,
                border: false,
                style: 'margin-top:16px;',
                bodyStyle: 'padding:14px;background:#f7fbff;border:1px solid #cfe4f5;border-radius:6px;',
                html: '<div style="color:#777;">Выберите пользователя, чтобы увидеть его текущий прогресс.</div>'
            }, {
                xtype: 'panel',
                id: this.planPanelId,
                border: false,
                style: 'margin-top:16px;',
                bodyStyle: 'padding:14px;background:#fffaf1;border:1px solid #f1d7a6;border-radius:6px;',
                html: '<div style="color:#777;">План изменений ещё не сформирован.</div>'
            }]
        }]
    });

    Training.panel.CourseProgress.superclass.constructor.call(this, config);

    this.on('afterrender', function() {
        this.updateModeControls(false);
        this.doLayout();

        /*
         * Инициализируем штатные ExtJS-списки после первого layout,
         * пока поля уже имеют корректную ширину и никогда не скрываются.
         */
        Ext.defer(this.initializeProgressComboLists, 1, this);

        this.reloadData();
    }, this);
};

Ext.extend(Training.panel.CourseProgress, Ext.Panel, {
    getForm: function() {
        var panel = Ext.getCmp('training-course-progress-form');
        return panel ? panel.getForm() : null;
    },

    /*
     * По умолчанию ComboBox создаёт выпадающий список при первом фокусе.
     * Здесь создаём штатные списки один раз после layout формы: ExtJS берёт
     * listWidth из фактической ширины wrap, без переопределения expand()
     * и без искусственных listWidth/minListWidth.
     */
    initializeProgressComboLists: function() {
        var form = this.getForm();

        if (!form) {
            return;
        }

        Ext.each(['module_id', 'lesson_id'], function(fieldName) {
            var combo = form.findField(fieldName);

            if (!combo || !combo.rendered || combo.list || typeof combo.initList !== 'function') {
                return;
            }

            combo.initList();
        });
    },

    getValues: function() {
        var form = this.getForm();
        if (!form) {
            return null;
        }

        var values = form.getValues() || {};
        var userField = form.findField('user_id');
        var moduleField = form.findField('module_id');
        var lessonField = form.findField('lesson_id');
        var modeField = form.findField('mode');

        /*
         * Приоритет: реальные ID из последнего select event.
         * Затем: getValue() ComboBox. Form.getValues() оставлен только как
         * последний запасной вариант.
         */
        var userId = this.selectedUserId
            || (userField ? parseInt(userField.getValue() || 0, 10) : 0)
            || parseInt(values.user_id || 0, 10)
            || 0;

        var moduleId = this.selectedModuleId
            || (moduleField ? parseInt(moduleField.getValue() || 0, 10) : 0)
            || parseInt(values.module_id || 0, 10)
            || 0;

        var lessonId = this.selectedLessonId
            || (lessonField ? parseInt(lessonField.getValue() || 0, 10) : 0)
            || parseInt(values.lesson_id || 0, 10)
            || 0;

        var mode = this.selectedMode
            || (modeField ? modeField.getValue() : '')
            || values.mode
            || 'lesson';

        values.course_id = this.courseId;
        values.user_id = userId;
        values.module_id = moduleId;
        values.lesson_id = lessonId;
        values.mode = (mode === 'view' || mode === 'module') ? mode : 'lesson';

        return values;
    },

    reloadData: function() {
        this.userStore.load({
            params: {
                action: 'mgr/course/progress/users/getlist',
                course_id: this.courseId,
                start: 0,
                limit: 0
            }
        });

        this.moduleStore.removeAll();
        this.lessonStore.removeAll();

        this.selectedModuleId = 0;
        this.selectedLessonId = 0;

        var form = this.getForm();
        if (form) {
            form.findField('module_id').reset();
            form.findField('lesson_id').reset();
        }

        this.resetPlan();
        this.updateModeControls(false);

        if (this.selectedUserId > 0) {
            if (this.isViewMode()) {
                this.loadUserSummary(this.selectedUserId, true);
            } else {
                this.reloadModules(this.selectedUserId);
            }
        }
    },

    setFieldEmptyText: function(field, text) {
        /*
         * В ExtJS 3 у ComboBox нет setEmptyText().
         * emptyText — свойство конфигурации; applyEmptyText есть не во всех
         * полях. Поэтому обновляем текст безопасно, без вызова несуществующего
         * метода и без изменения значения поля.
         */
        if (!field) {
            return;
        }

        field.emptyText = text;

        if (typeof field.applyEmptyText === 'function') {
            field.applyEmptyText();
        }
    },

    reloadModules: function(userId) {
        userId = parseInt(userId || 0, 10) || 0;

        var form = this.getForm();
        if (!form) {
            return;
        }

        var moduleField = form.findField('module_id');
        var lessonField = form.findField('lesson_id');

        this.selectedModuleId = 0;
        this.selectedLessonId = 0;
        this.moduleStore.removeAll();
        this.lessonStore.removeAll();
        moduleField.reset();
        lessonField.reset();
        lessonField.setDisabled(true);
        this.setFieldEmptyText(lessonField, 'Сначала выберите модуль');

        if (userId <= 0 || this.isViewMode()) {
            moduleField.setDisabled(true);
            this.setFieldEmptyText(moduleField, userId <= 0 ? 'Сначала выберите пользователя' : 'Режим просмотра не изменяет прогресс');
            return;
        }

        moduleField.setDisabled(true);
        this.setFieldEmptyText(moduleField, 'Загружаем незавершённые модули...');

        this.moduleStore.baseParams = this.moduleStore.baseParams || {};
        this.moduleStore.baseParams.action = 'mgr/course/progress/modules/getlist';
        this.moduleStore.baseParams.course_id = this.courseId;
        this.moduleStore.baseParams.user_id = userId;
        this.moduleStore.baseParams.exclude_completed = 1;

        this.moduleStore.load({
            params: {
                action: 'mgr/course/progress/modules/getlist',
                course_id: this.courseId,
                user_id: userId,
                exclude_completed: 1,
                start: 0,
                limit: 0
            },
            callback: function(records, operation, success) {
                moduleField.lastQuery = null;

                if (success && records && records.length > 0) {
                    moduleField.setDisabled(false);
                    this.setFieldEmptyText(moduleField, 'Выберите незавершённый модуль');
                } else {
                    moduleField.reset();
                    moduleField.setDisabled(true);
                    this.setFieldEmptyText(moduleField, 'У пользователя нет незавершённых модулей');
                }

                this.doLayout();
            },
            scope: this
        });
    },

    reloadLessons: function(moduleId) {
        moduleId = parseInt(moduleId || 0, 10) || 0;
        this.selectedLessonId = 0;

        var form = this.getForm();
        if (!form) {
            return;
        }

        var lessonField = form.findField('lesson_id');
        var userId = this.selectedUserId || 0;

        lessonField.reset();
        lessonField.setDisabled(moduleId <= 0 || userId <= 0);
        this.setFieldEmptyText(
            lessonField,
            moduleId > 0 ? 'Загружаем незавершённые уроки...' : 'Сначала выберите модуль'
        );

        this.lessonStore.baseParams = this.lessonStore.baseParams || {};
        this.lessonStore.baseParams.action = 'mgr/course/progress/lessons/getlist';
        this.lessonStore.baseParams.course_id = this.courseId;
        this.lessonStore.baseParams.module_id = moduleId;
        this.lessonStore.baseParams.user_id = userId;
        this.lessonStore.baseParams.exclude_completed = 1;

        this.lessonStore.removeAll();

        if (moduleId > 0 && userId > 0 && !this.isViewMode()) {
            this.lessonStore.load({
                params: {
                    action: 'mgr/course/progress/lessons/getlist',
                    course_id: this.courseId,
                    module_id: moduleId,
                    user_id: userId,
                    exclude_completed: 1,
                    start: 0,
                    limit: 0
                },
                callback: function(records, operation, success) {
                    lessonField.lastQuery = null;

                    if (success && records && records.length > 0) {
                        lessonField.setDisabled(false);
                        this.setFieldEmptyText(lessonField, 'Выберите незавершённый урок');
                    } else {
                        lessonField.reset();
                        lessonField.setDisabled(true);
                        this.setFieldEmptyText(lessonField, 'Для этого модуля нет незавершённых уроков');
                    }

                    this.toggleLessonField();
                },
                scope: this
            });
        } else {
            lessonField.reset();
            lessonField.setDisabled(true);
            this.setFieldEmptyText(lessonField, 'Сначала выберите модуль');
        }
    },

    isViewMode: function() {
        return this.selectedMode === 'view';
    },

    updateModeControls: function(reloadOptions) {
        var form = this.getForm();
        if (!form) {
            return;
        }

        var modeField = form.findField('mode');
        var moduleField = form.findField('module_id');
        var lessonField = form.findField('lesson_id');
        var previewButton = Ext.getCmp(this.previewButtonId);
        var applyButton = Ext.getCmp(this.applyButtonId);
        var mode = modeField ? modeField.getValue() : this.selectedMode;
        var isViewMode = mode === 'view';
        var isLessonMode = mode === 'lesson';

        this.selectedMode = (mode === 'view' || mode === 'module') ? mode : 'lesson';

        if (previewButton) {
            previewButton.setText(isViewMode ? 'Показать прогресс' : 'Показать план');
        }

        if (isViewMode) {
            this.selectedModuleId = 0;
            this.selectedLessonId = 0;
            this.moduleStore.removeAll();
            this.lessonStore.removeAll();

            moduleField.reset();
            lessonField.reset();
            moduleField.setDisabled(true);
            lessonField.setDisabled(true);
            this.setFieldEmptyText(moduleField, 'Режим просмотра не изменяет прогресс');
            this.setFieldEmptyText(lessonField, 'Режим просмотра не изменяет прогресс');

            if (applyButton) {
                applyButton.setDisabled(true);
                applyButton.hide();
            }

            this.setPanelHtml(
                this.planPanelId,
                '<div style="color:#777;">Режим просмотра: изменения не применяются. Выберите пользователя или нажмите «Показать прогресс».</div>'
            );
        } else {
            moduleField.setDisabled(this.selectedUserId <= 0);
            this.setFieldEmptyText(
                moduleField,
                this.selectedUserId > 0 ? 'Выберите незавершённый модуль' : 'Сначала выберите пользователя'
            );

            if (isLessonMode) {
                lessonField.setDisabled(this.selectedModuleId <= 0);
                this.setFieldEmptyText(
                    lessonField,
                    this.selectedModuleId > 0 ? 'Выберите незавершённый урок' : 'Сначала выберите модуль'
                );
            } else {
                this.selectedLessonId = 0;
                lessonField.reset();
                lessonField.setDisabled(true);
                this.setFieldEmptyText(lessonField, 'Урок не требуется для завершения модуля');
            }

            if (applyButton) {
                applyButton.show();
                applyButton.setDisabled(true);
            }

            if (reloadOptions && this.selectedUserId > 0) {
                this.reloadModules(this.selectedUserId);
            }
        }

        this.doLayout();
    },

    toggleLessonField: function() {
        this.updateModeControls(false);
    },

    setPanelHtml: function(panelId, html) {
        var panel = Ext.getCmp(panelId);

        if (!panel) {
            return;
        }

        if (panel.rendered && panel.body) {
            panel.body.update(html);
        } else {
            panel.html = html;
        }
    },

    getProgressStatusText: function(status, completed) {
        if (completed || status === 'completed') {
            return 'Завершено';
        }

        if (status === 'in_progress') {
            return 'В процессе';
        }

        return 'Не начат';
    },

    buildProgressDetailsHtml: function(modules) {
        modules = modules || [];

        if (!modules.length) {
            return '<div style="margin-top:12px;color:#777;">По модулям пока нет данных.</div>';
        }

        var html = '<div style="margin-top:14px;"><b>Модули курса</b></div>';

        Ext.each(modules, function(module) {
            var isCurrent = parseInt(module.is_current || 0, 10) === 1;
            var completed = parseInt(module.completed || 0, 10) === 1;
            var percent = Math.round(parseFloat(module.progress_percent || 0));
            var lessonsCompleted = parseInt(module.lessons_completed || 0, 10) || 0;
            var lessonsTotal = parseInt(module.lessons_total || 0, 10) || 0;
            var title = Ext.util.Format.htmlEncode(module.display_name || ('Модуль #' + module.id));
            var status = this.getProgressStatusText(module.status || '', completed);
            var background = isCurrent ? '#f5f0ff' : (completed ? '#f4fbf6' : '#fff');
            var border = isCurrent ? '#d8c7f3' : (completed ? '#cbe8d1' : '#e5e5e5');
            var currentMark = isCurrent ? ' <b style="color:#6f42c1;">— текущий</b>' : '';

            html += '<div style="margin-top:8px;padding:9px 10px;border:1px solid ' + border + ';background:' + background + ';">'
                + '<b>' + title + '</b>' + currentMark + '<br>'
                + '<span style="color:#555;">' + Ext.util.Format.htmlEncode(status)
                + ' · ' + Ext.util.Format.htmlEncode(String(percent)) + '%'
                + ' · уроков: ' + Ext.util.Format.htmlEncode(String(lessonsCompleted))
                + '/' + Ext.util.Format.htmlEncode(String(lessonsTotal))
                + '</span>';

            if (isCurrent && module.lessons && module.lessons.length) {
                html += '<div style="margin:8px 0 0 12px;"><b>Уроки текущего модуля</b><ul style="margin:6px 0 0 18px;">';

                Ext.each(module.lessons, function(lesson) {
                    var lessonCompleted = parseInt(lesson.completed || 0, 10) === 1;
                    var lessonCurrent = parseInt(lesson.is_current || 0, 10) === 1;
                    var lessonPercent = Math.round(parseFloat(lesson.progress_percent || 0));
                    var lessonStatus = this.getProgressStatusText(lesson.status || '', lessonCompleted);
                    var lessonMark = lessonCurrent ? ' <b style="color:#6f42c1;">— остановился здесь</b>' : '';

                    html += '<li>'
                        + Ext.util.Format.htmlEncode(lesson.display_name || ('Урок #' + lesson.id))
                        + ' — ' + Ext.util.Format.htmlEncode(lessonStatus)
                        + ' (' + Ext.util.Format.htmlEncode(String(lessonPercent)) + '%)'
                        + lessonMark
                        + '</li>';
                }, this);

                html += '</ul></div>';
            }

            html += '</div>';
        }, this);

        return html;
    },

    buildSummaryHtml: function(obj, detailed) {
        obj = obj || {};

        var html = '<div><b>Текущий прогресс пользователя</b></div>'
            + '<div style="margin-top:8px;">'
            + '<b>Статус курса:</b> ' + Ext.util.Format.htmlEncode(obj.course_status || '—') + '<br>'
            + '<b>Прогресс:</b> ' + Ext.util.Format.htmlEncode(String(obj.progress_percent || 0)) + '%'
            + ' (' + Ext.util.Format.htmlEncode(String(obj.completed_modules || 0))
            + '/' + Ext.util.Format.htmlEncode(String(obj.total_modules || 0)) + ' модулей)<br>'
            + '<b>Текущий модуль:</b> ' + Ext.util.Format.htmlEncode(obj.current_module_label || '—') + '<br>'
            + '<b>Текущий урок:</b> ' + Ext.util.Format.htmlEncode(obj.current_lesson_label || '—')
            + '</div>';

        if (detailed) {
            html += this.buildProgressDetailsHtml(obj.modules || []);
        }

        return html;
    },

    loadUserSummary: function(userId, detailed) {
        userId = parseInt(userId || 0, 10) || 0;
        detailed = detailed === true;
        var summaryPanel = Ext.getCmp(this.summaryPanelId);

        if (!userId || !summaryPanel) {
            return;
        }

        this.setPanelHtml(this.summaryPanelId, '<div style="color:#777;">Загружаем текущий прогресс...</div>');
        summaryPanel.getEl().mask('Загрузка...');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/progress/summary',
                course_id: this.courseId,
                user_id: userId,
                detail: detailed ? 1 : 0
            },
            listeners: {
                success: {
                    fn: function(r) {
                        summaryPanel.getEl().unmask();
                        var obj = Training.utils.getResultData(r) || {};
                        this.setPanelHtml(this.summaryPanelId, this.buildSummaryHtml(obj, detailed));
                        summaryPanel.doLayout();
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        summaryPanel.getEl().unmask();
                        var message = (r && r.message) ? r.message : 'Не удалось загрузить прогресс';
                        this.setPanelHtml(this.summaryPanelId, '<div style="color:#b30000;">' + Ext.util.Format.htmlEncode(message) + '</div>');
                    },
                    scope: this
                }
            }
        });
    },

    requestPlan: function() {
        var form = this.getForm();
        var values = this.getValues();

        if (!form || !values) {
            return;
        }

        if (values.mode === 'view') {
            if (values.user_id <= 0) {
                MODx.msg.alert('Прогресс', 'Выберите пользователя для просмотра прогресса.');
                return;
            }

            this.resetPlan();
            this.setPanelHtml(
                this.planPanelId,
                '<div style="color:#777;">Режим просмотра: изменения не применяются. Ниже показана текущая учебная точка пользователя.</div>'
            );
            this.loadUserSummary(values.user_id, true);
            return;
        }

        if (!form.isValid()) {
            MODx.msg.alert('Прогресс', 'Выберите пользователя, режим и незавершённый модуль. Для режима «Остановить на уроке» также выберите незавершённый урок.');
            return;
        }

        if (values.mode === 'lesson' && values.lesson_id <= 0) {
            MODx.msg.alert('Прогресс', 'Выберите урок, на котором пользователь должен остановиться.');
            return;
        }

        var planPanel = Ext.getCmp(this.planPanelId);
        if (!planPanel) {
            return;
        }

        this.resetPlan();
        this.setPanelHtml(this.planPanelId, '<div style="color:#777;">Формируем безопасный план изменений...</div>');
        planPanel.getEl().mask('Расчёт...');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: Ext.apply(values, {
                action: 'mgr/course/progress/plan'
            }),
            listeners: {
                success: {
                    fn: function(r) {
                        planPanel.getEl().unmask();
                        var obj = Training.utils.getResultData(r) || {};
                        this.setPanelHtml(this.planPanelId, obj.plan_html || '<div>План сформирован.</div>');
                        planPanel.doLayout();
                        this.planReady = true;
                        var applyButton = Ext.getCmp(this.applyButtonId);
                        if (applyButton) {
                            applyButton.setDisabled(false);
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        planPanel.getEl().unmask();
                        var message = (r && r.message) ? r.message : 'Не удалось сформировать план';
                        this.setPanelHtml(this.planPanelId, '<div style="color:#b30000;">' + Ext.util.Format.htmlEncode(message) + '</div>');
                    },
                    scope: this
                }
            }
        });
    },

    confirmApply: function() {
        if (!this.planReady) {
            MODx.msg.alert('Прогресс', 'Сначала сформируйте план изменений.');
            return;
        }

        var values = this.getValues();
        if (!values || values.mode === 'view') {
            return;
        }

        var userRecord = this.userStore.getById ? this.userStore.getById(values.user_id) : null;
        var userName = userRecord ? userRecord.get('display_name') : ('Пользователь #' + values.user_id);

        Ext.Msg.confirm(
            'Подтверждение',
            'Применить сформированный план для пользователя <b>' + Ext.util.Format.htmlEncode(userName) + '</b>?',
            function(button) {
                if (button === 'yes') {
                    this.applyPlan(values);
                }
            },
            this
        );
    },

    applyPlan: function(values) {
        if (!values || values.mode === 'view') {
            return;
        }

        var panel = Ext.getCmp(this.planPanelId);
        if (!panel) {
            return;
        }

        panel.getEl().mask('Применяем изменения...');
        var applyButton = Ext.getCmp(this.applyButtonId);
        if (applyButton) {
            applyButton.setDisabled(true);
        }

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: Ext.apply(values, {
                action: 'mgr/course/progress/apply'
            }),
            listeners: {
                success: {
                    fn: function(r) {
                        panel.getEl().unmask();
                        var obj = Training.utils.getResultData(r) || {};
                        this.setPanelHtml(this.planPanelId, obj.result_html || '<div>Прогресс обновлён.</div>');
                        panel.doLayout();
                        this.planReady = false;
                        this.loadUserSummary(values.user_id);

                        var applyButton = Ext.getCmp(this.applyButtonId);
                        if (applyButton) {
                            applyButton.setDisabled(true);
                        }

                        MODx.msg.alert('Готово', (r && r.message) ? r.message : 'Прогресс пользователя обновлён');
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        panel.getEl().unmask();
                        this.planReady = false;
                        var applyButton = Ext.getCmp(this.applyButtonId);
                        if (applyButton) {
                            applyButton.setDisabled(true);
                        }
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось применить прогресс');
                    },
                    scope: this
                }
            }
        });
    },

    resetPlan: function() {
        this.planReady = false;
        var applyButton = Ext.getCmp(this.applyButtonId);
        if (applyButton) {
            applyButton.setDisabled(true);
        }

        var planPanel = Ext.getCmp(this.planPanelId);
        if (planPanel) {
            this.setPanelHtml(this.planPanelId, '<div style="color:#777;">План изменений ещё не сформирован.</div>');
        }
    }
});

Ext.reg('training-course-progress-panel', Training.panel.CourseProgress);

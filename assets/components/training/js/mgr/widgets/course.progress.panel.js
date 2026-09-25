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
                                this.selectedMode = value === 'module' ? 'module' : (value === 'view' ? 'view' : 'lesson');
                                this.selectedModuleId = 0;
                                this.selectedLessonId = 0;
                                this.resetPlan();
                                this.updateModeControls(true);

                                if (this.selectedUserId > 0) {
                                    this.loadUserSummary(this.selectedUserId, this.isViewMode());
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
                                this.updateModeControls(false);
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
                    text: 'Показать план',
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

    isViewMode: function() {
        return this.selectedMode === 'view';
    },

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
            || 'view';

        values.course_id = this.courseId;
        values.user_id = userId;
        values.module_id = moduleId;
        values.lesson_id = lessonId;
        values.mode = mode === 'module' ? 'module' : (mode === 'view' ? 'view' : 'lesson');

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

        this.lessonStore.removeAll();
        this.lessonStore.baseParams.module_id = 0;

        var form = this.getForm();
        if (form) {
            form.findField('module_id').reset();
            form.findField('lesson_id').reset();
        }

        this.selectedModuleId = 0;
        this.selectedLessonId = 0;
        this.resetPlan();
        this.updateModeControls(true);
    },

    setFieldEmptyText: function(field, text) {
        if (!field) {
            return;
        }

        field.emptyText = text;

        if (typeof field.applyEmptyText === 'function') {
            field.applyEmptyText();
        }
    },

    updateModeControls: function(reloadOptions) {
        var form = this.getForm();
        if (!form) {
            return;
        }

        var modeField = form.findField('mode');
        var moduleField = form.findField('module_id');
        var lessonField = form.findField('lesson_id');
        var mode = this.selectedMode || (modeField ? modeField.getValue() : '') || 'view';
        var isViewMode = mode === 'view';
        var isLessonMode = mode === 'lesson';
        var previewButton = Ext.getCmp(this.previewButtonId);
        var applyButton = Ext.getCmp(this.applyButtonId);

        if (modeField && modeField.getValue() !== mode) {
            modeField.setValue(mode);
        }

        if (isViewMode) {
            this.selectedModuleId = 0;
            this.selectedLessonId = 0;
            moduleField.reset();
            lessonField.reset();
            moduleField.setDisabled(true);
            lessonField.setDisabled(true);
            this.setFieldEmptyText(moduleField, 'Режим просмотра не изменяет прогресс');
            this.setFieldEmptyText(lessonField, 'Режим просмотра не изменяет прогресс');

            if (previewButton) {
                previewButton.setDisabled(true);
            }

            if (applyButton) {
                applyButton.setDisabled(true);
            }

            return;
        }

        if (previewButton) {
            previewButton.setDisabled(this.selectedUserId <= 0);
        }

        if (applyButton) {
            applyButton.setDisabled(true);
        }

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

        if (reloadOptions && this.selectedUserId > 0) {
            this.reloadModules(this.selectedUserId);
        }
    },

    reloadModules: function(userId) {
        userId = parseInt(userId || 0, 10) || 0;
        this.selectedModuleId = 0;
        this.selectedLessonId = 0;

        var form = this.getForm();
        if (!form) {
            return;
        }

        var moduleField = form.findField('module_id');
        var lessonField = form.findField('lesson_id');

        moduleField.reset();
        lessonField.reset();
        lessonField.setDisabled(true);
        this.setFieldEmptyText(lessonField, 'Сначала выберите модуль');

        this.moduleStore.baseParams = this.moduleStore.baseParams || {};
        this.moduleStore.baseParams.action = 'mgr/course/progress/modules/getlist';
        this.moduleStore.baseParams.course_id = this.courseId;
        this.moduleStore.baseParams.user_id = userId;
        this.moduleStore.baseParams.only_incomplete = 1;

        this.moduleStore.removeAll();

        if (userId <= 0) {
            moduleField.setDisabled(true);
            return;
        }

        moduleField.setDisabled(false);

        this.moduleStore.load({
            params: {
                action: 'mgr/course/progress/modules/getlist',
                course_id: this.courseId,
                user_id: userId,
                only_incomplete: 1,
                start: 0,
                limit: 0
            }
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
        lessonField.reset();
        lessonField.setDisabled(moduleId <= 0 || !this.selectedUserId || !this.isViewMode() && this.selectedMode !== 'lesson');
        this.setFieldEmptyText(lessonField, moduleId > 0 ? 'Выберите незавершённый урок' : 'Сначала выберите модуль');

        this.lessonStore.baseParams = this.lessonStore.baseParams || {};
        this.lessonStore.baseParams.action = 'mgr/course/progress/lessons/getlist';
        this.lessonStore.baseParams.course_id = this.courseId;
        this.lessonStore.baseParams.module_id = moduleId;
        this.lessonStore.baseParams.user_id = this.selectedUserId;
        this.lessonStore.baseParams.only_incomplete = 1;

        this.lessonStore.removeAll();

        if (moduleId > 0 && this.selectedUserId > 0 && this.selectedMode === 'lesson') {
            lessonField.setDisabled(false);
            this.lessonStore.load({
                params: {
                    action: 'mgr/course/progress/lessons/getlist',
                    course_id: this.courseId,
                    module_id: moduleId,
                    user_id: this.selectedUserId,
                    only_incomplete: 1,
                    start: 0,
                    limit: 0
                }
            });
        }
    },

    loadUserSummary: function(userId, viewMode) {
        userId = parseInt(userId || 0, 10) || 0;
        var summaryPanel = Ext.getCmp(this.summaryPanelId);

        if (!summaryPanel) {
            return;
        }

        if (userId <= 0) {
            summaryPanel.update('<div style="color:#777;">Выберите пользователя, чтобы увидеть его текущий прогресс.</div>');
            return;
        }

        summaryPanel.update('<div style="color:#777;">Загружаем текущий прогресс…</div>');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/progress/summary',
                course_id: this.courseId,
                user_id: userId,
                include_lessons: viewMode ? 1 : 0
            },
            listeners: {
                success: {
                    fn: function(response) {
                        var data = response.object || {};
                        summaryPanel.update(data.html || '<div style="color:#777;">Данные о прогрессе не найдены.</div>');
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        summaryPanel.update('<div style="color:#c00;">Не удалось загрузить прогресс: ' + Ext.util.Format.htmlEncode(response.message || 'Неизвестная ошибка') + '</div>');
                    },
                    scope: this
                }
            }
        });
    },

    requestPlan: function() {
        if (this.isViewMode()) {
            return;
        }

        var values = this.getValues();
        var form = this.getForm();
        var planPanel = Ext.getCmp(this.planPanelId);

        if (!values || !form) {
            return;
        }

        if (!values.user_id) {
            MODx.msg.alert(_('error'), 'Выберите пользователя');
            return;
        }

        if (!values.module_id) {
            MODx.msg.alert(_('error'), 'Выберите незавершённый модуль');
            return;
        }

        if (values.mode === 'lesson' && !values.lesson_id) {
            MODx.msg.alert(_('error'), 'Выберите незавершённый урок');
            return;
        }

        planPanel.update('<div style="color:#777;">Формируем план изменений…</div>');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/progress/plan',
                course_id: this.courseId,
                user_id: values.user_id,
                module_id: values.module_id,
                lesson_id: values.lesson_id,
                mode: values.mode
            },
            listeners: {
                success: {
                    fn: function(response) {
                        var data = response.object || {};
                        this.planReady = true;
                        this.lastPlanValues = values;
                        planPanel.update(data.html || '<div style="color:#777;">План изменений пуст.</div>');
                        var applyButton = Ext.getCmp(this.applyButtonId);
                        if (applyButton) {
                            applyButton.setDisabled(false);
                        }
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        this.resetPlan();
                        planPanel.update('<div style="color:#c00;">Не удалось сформировать план: ' + Ext.util.Format.htmlEncode(response.message || 'Неизвестная ошибка') + '</div>');
                    },
                    scope: this
                }
            }
        });
    },

    resetPlan: function() {
        this.planReady = false;
        this.lastPlanValues = null;
        var applyButton = Ext.getCmp(this.applyButtonId);
        var planPanel = Ext.getCmp(this.planPanelId);

        if (applyButton) {
            applyButton.setDisabled(true);
        }

        if (planPanel) {
            planPanel.update('<div style="color:#777;">План изменений ещё не сформирован.</div>');
        }
    },

    confirmApply: function() {
        if (this.isViewMode() || !this.planReady || !this.lastPlanValues) {
            return;
        }

        MODx.msg.confirm({
            title: 'Подтверждение изменений',
            text: 'Применить показанный план? Изменения будут записаны в прогресс пользователя.',
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/progress/apply',
                course_id: this.courseId,
                user_id: this.lastPlanValues.user_id,
                module_id: this.lastPlanValues.module_id,
                lesson_id: this.lastPlanValues.lesson_id,
                mode: this.lastPlanValues.mode
            },
            listeners: {
                success: {
                    fn: function(response) {
                        MODx.msg.alert(_('success'), response.message || 'Изменения применены');
                        this.reloadData();

                        if (this.selectedUserId > 0) {
                            this.loadUserSummary(this.selectedUserId, this.isViewMode());
                        }
                    },
                    scope: this
                }
            }
        });
    }
});

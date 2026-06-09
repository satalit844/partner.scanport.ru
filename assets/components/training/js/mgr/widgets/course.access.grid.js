Training.combo.AccessPrincipalType = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'principal_type',
        fieldLabel: config.fieldLabel || 'Тип доступа',
        mode: 'local',
        editable: false,
        triggerAction: 'all',
        store: new Ext.data.ArrayStore({
            fields: ['value', 'label'],
            data: [
                ['user', 'Пользователь'],
                ['group', 'Группа']
            ]
        }),
        valueField: 'value',
        displayField: 'label',
        value: config.value || 'user',
        anchor: '100%'
    });

    Training.combo.AccessPrincipalType.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.AccessPrincipalType, MODx.combo.ComboBox);
Ext.reg('training-combo-access-principal-type', Training.combo.AccessPrincipalType);

Training.combo.AccessRole = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'access_role',
        fieldLabel: config.fieldLabel || 'Права',
        mode: 'local',
        editable: false,
        triggerAction: 'all',
        store: new Ext.data.ArrayStore({
            fields: ['value', 'label'],
            data: [
                ['employee', 'Сотрудник'],
                ['director', 'Директор']
            ]
        }),
        valueField: 'value',
        displayField: 'label',
        value: config.value || 'employee',
        anchor: '100%'
    });

    Training.combo.AccessRole.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.AccessRole, MODx.combo.ComboBox);
Ext.reg('training-combo-access-role', Training.combo.AccessRole);

Training.combo.AccessUsers = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'principal_user_id',
        fieldLabel: config.fieldLabel || 'Пользователь',
        valueField: 'id',
        displayField: 'display',
        editable: true,
        forceSelection: true,
        triggerAction: 'all',
        minChars: 1,
        pageSize: 20,
        mode: 'remote',
        anchor: '100%',
        store: new Ext.data.JsonStore({
            url: Training.config.connector_url,
            root: 'results',
            totalProperty: 'total',
            idProperty: 'id',
            fields: ['id', 'username', 'fullname', 'email', 'display'],
            baseParams: {
                action: 'mgr/course/access/users'
            }
        })
    });

    Training.combo.AccessUsers.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.AccessUsers, MODx.combo.ComboBox);
Ext.reg('training-combo-access-users', Training.combo.AccessUsers);

Training.combo.AccessGroups = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'principal_group_id',
        fieldLabel: config.fieldLabel || 'Группа',
        valueField: 'id',
        displayField: 'display',
        editable: true,
        forceSelection: true,
        triggerAction: 'all',
        minChars: 1,
        pageSize: 20,
        mode: 'remote',
        listWidth: 420,
        anchor: '100%',
        store: new Ext.data.JsonStore({
            url: Training.config.connector_url,
            root: 'results',
            totalProperty: 'total',
            idProperty: 'id',
            fields: ['id', 'name', 'display'],
            baseParams: {
                action: 'mgr/course/access/groups'
            }
        })
    });

    Training.combo.AccessGroups.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.AccessGroups, MODx.combo.ComboBox);
Ext.reg('training-combo-access-groups', Training.combo.AccessGroups);

Training.window.CourseAccess = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;
    this.record = config.record || null;
    this.isUpdate = !!this.record;
    this.userFieldId = Ext.id();
    this.groupFieldId = Ext.id();

    Ext.applyIf(config, {
        title: this.isUpdate ? 'Редактирование доступа' : 'Добавление доступа',
        width: 520,
        autoHeight: true,
        url: Training.config.connector_url,
        action: this.isUpdate ? 'mgr/course/access/update' : 'mgr/course/access/create',
        fields: [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'hidden',
            name: 'course_id',
            value: this.courseId
        }, {
            xtype: 'training-combo-access-principal-type',
            name: 'principal_type',
            value: this.record ? this.record.principal_type : 'user',
            listeners: {
                select: {
                    fn: this.togglePrincipalFields,
                    scope: this
                }
            }
        }, {
            xtype: 'training-combo-access-users',
            name: 'principal_user_id',
            fieldLabel: 'Пользователь',
            id: this.userFieldId
        }, {
            xtype: 'training-combo-access-groups',
            name: 'principal_group_id',
            fieldLabel: 'Группа',
            hidden: true,
            id: this.groupFieldId
        }, {
            xtype: 'training-combo-access-role',
            name: 'access_role',
            value: this.record ? (this.record.access_role || 'employee') : 'employee'
        }, {
            xtype: 'hidden',
            name: 'principal_id'
        }, {
            xtype: 'xdatetime',
            fieldLabel: 'Активно с',
            name: 'active_from',
            anchor: '100%'
        }, {
            xtype: 'xdatetime',
            fieldLabel: 'Активно до',
            name: 'active_to',
            anchor: '100%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: 'Активно',
            hideLabel: true,
            name: 'is_active',
            checked: this.record ? Training.utils.toBool(this.record.is_active) : true
        }]
    });

    Training.window.CourseAccess.superclass.constructor.call(this, config);
    this.on('afterrender', this.applyRecord, this);
};
Ext.extend(Training.window.CourseAccess, MODx.Window, {
    applyRecord: function() {
        if (!this.fp) {
            return;
        }

        var form = this.fp.getForm();
        if (this.record) {
            form.setValues(this.record);
            Training.utils.setCheckboxValue(form, 'is_active', this.record.is_active);

            if (this.record.principal_type === 'group') {
                var groupField = Ext.getCmp(this.groupFieldId);
                if (groupField) {
                    groupField.setValue(this.record.principal_id);
                    groupField.setRawValue(this.record.principal_label || this.record.principal_name || '');
                }
                var groupHidden = form.findField('principal_group_id');
                if (groupHidden) {
                    groupHidden.setValue(this.record.principal_id);
                }
            } else {
                var userField = Ext.getCmp(this.userFieldId);
                if (userField) {
                    userField.setValue(this.record.principal_id);
                    userField.setRawValue(this.record.principal_label || this.record.principal_name || '');
                }
                var userHidden = form.findField('principal_user_id');
                if (userHidden) {
                    userHidden.setValue(this.record.principal_id);
                }
            }

            var principalIdField = form.findField('principal_id');
            if (principalIdField) {
                principalIdField.setValue(this.record.principal_id || 0);
            }
        }

        this.togglePrincipalFields();
    },

    togglePrincipalFields: function() {
        var form = this.fp ? this.fp.getForm() : null;
        if (!form) {
            return;
        }

        var typeField = form.findField('principal_type');
        var type = typeField ? typeField.getValue() : 'user';
        var userField = Ext.getCmp(this.userFieldId);
        var groupField = Ext.getCmp(this.groupFieldId);

        if (userField && groupField) {
            if (type === 'group') {
                userField.hide();
                groupField.show();
            } else {
                groupField.hide();
                userField.show();
            }
            this.doLayout();
        }
    },

    submit: function() {
        var form = this.fp ? this.fp.getForm() : null;
        if (!form || !form.isValid()) {
            return false;
        }

        var type = form.findField('principal_type').getValue();
        var principalId = 0;
        if (type === 'group') {
            principalId = parseInt(form.findField('principal_group_id').getValue(), 10) || 0;
        } else {
            principalId = parseInt(form.findField('principal_user_id').getValue(), 10) || 0;
        }

        if (!principalId) {
            MODx.msg.alert('Внимание', type === 'group' ? 'Выберите группу' : 'Выберите пользователя');
            return false;
        }

        form.findField('principal_id').setValue(principalId);
        this.getEl().mask('Сохраняем...');

        form.submit({
            url: this.config.url,
            params: {
                action: this.config.action,
                course_id: this.courseId,
                principal_id: principalId
            },
            success: function() {
                this.getEl().unmask();
                this.hide();
                var grid = Ext.getCmp('training-grid-course-access');
                if (grid) {
                    grid.menu.record = null;
                    grid.refresh();
                }
                this.fireEvent('success');
            },
            failure: function(formObj, action) {
                this.getEl().unmask();
                var message = 'Не удалось сохранить доступ';
                if (action && action.result && action.result.message) {
                    message = action.result.message;
                }
                MODx.msg.alert('Ошибка', message);
            },
            scope: this
        });

        return true;
    }
});
Ext.reg('training-window-course-access', Training.window.CourseAccess);

Training.grid.CourseAccess = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.updateBtnId = Ext.id();
    this.removeBtnId = Ext.id();

    Ext.applyIf(config, {
        id: 'training-grid-course-access',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/course/access/getlist',
            course_id: this.courseId
        },
        sm: this.sm,
        paging: true,
        remoteSort: true,
        autoHeight: true,
        anchor: '100%',
        multi_select: true,
        fields: [
            'id',
            'course_id',
            'principal_type',
            'principal_type_label',
            'principal_id',
            'principal_name',
            'principal_label',
            'access_role',
            'access_role_label',
            'assigned_by',
            'assigned_by_label',
            'active_from',
            'active_to',
            'is_active',
            'is_active_now',
            'createdon'
        ],
        columns: [this.sm, {
            header: 'ID',
            dataIndex: 'id',
            width: 60
        }, {
            header: 'Тип',
            dataIndex: 'principal_type_label',
            width: 100
        }, {
            header: 'Кому',
            dataIndex: 'principal_label',
            width: 360
        }, {
            header: 'Права',
            dataIndex: 'access_role_label',
            width: 110
        }, {
            header: 'Активно',
            dataIndex: 'is_active',
            width: 80,
            renderer: Training.utils.renderBoolean
        }, {
            header: 'Действует сейчас',
            dataIndex: 'is_active_now',
            width: 120,
            renderer: Training.utils.renderBoolean
        }, {
            header: 'С',
            dataIndex: 'active_from',
            width: 140
        }, {
            header: 'По',
            dataIndex: 'active_to',
            width: 140
        }, {
            header: 'Назначил',
            dataIndex: 'assigned_by_label',
            width: 180
        }, {
            header: 'Создано',
            dataIndex: 'createdon',
            width: 140
        }],
        tbar: [{
            text: 'Добавить доступ',
            handler: this.createAccess,
            scope: this
        }, '-', {
            id: this.updateBtnId,
            text: 'Изменить',
            disabled: true,
            handler: function(){ this.updateAccess(); },
            scope: this
        }, {
            id: this.removeBtnId,
            text: 'Удалить',
            disabled: true,
            handler: function(){ this.removeAccess(); },
            scope: this
        }, '-', {
            text: 'Синхронизировать пользователей',
            handler: this.syncUsers,
            scope: this
        }, '->', {
            text: 'Обновить',
            handler: function() {
                this.refresh();
            },
            scope: this
        }],
        listeners: {
            rowdblclick: {
                fn: function(grid, rowIndex) {
                    var rec = grid.store.getAt(rowIndex);
                    if (rec) {
                        this.updateAccess(rec);
                    }
                },
                scope: this
            },
            rowcontextmenu: {
                fn: function(grid, rowIndex, e) {
                    var rec = grid.store.getAt(rowIndex);
                    if (!rec) {
                        return;
                    }
                    grid.getSelectionModel().selectRow(rowIndex);
                    this.menu.record = rec;
                    this.updateActionButtons();
                    this.getMenu();
                    if (this.menu) {
                        this.menu.showAt(e.getXY());
                    }
                    e.stopEvent();
                },
                scope: this
            },
            cellclick: {
                fn: function(grid, rowIndex, columnIndex) {
                    var rec = grid.store.getAt(rowIndex);
                    var cm = grid.getColumnModel();
                    var dataIndex = cm.getDataIndex(columnIndex);
                    if (rec && dataIndex === 'is_active') {
                        this.toggleActive(rec);
                    }
                },
                scope: this
            }
        }
    });

    Training.grid.CourseAccess.superclass.constructor.call(this, config);

    this.getStore().on('load', function() {
        this.menu.record = null;
        this.updateActionButtons();
    }, this);

    if (this.sm && this.sm.on) {
        this.sm.on('selectionchange', this.updateActionButtons, this);
    }
};

Ext.extend(Training.grid.CourseAccess, MODx.grid.Grid, {
    getSelectedAccessIds: function() {
        var ids = [];

        if (typeof this._getSelectedIds === 'function') {
            var selected = this._getSelectedIds();
            if (Ext.isArray(selected) && selected.length) {
                return selected;
            }
            if (typeof selected === 'string' && selected.length) {
                return selected.split(',');
            }
        }

        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        if (sm && sm.getSelections) {
            Ext.each(sm.getSelections(), function(rec) {
                var id = parseInt(rec.get ? rec.get('id') : rec.id, 10) || 0;
                if (id) {
                    ids.push(id);
                }
            });
        }

        if (!ids.length && this.menu && this.menu.record) {
            var menuId = parseInt(Training.utils.getRecordValue(this.menu.record, 'id'), 10) || 0;
            if (menuId) {
                ids.push(menuId);
            }
        }

        return ids;
    },

    getSingleSelectedAccess: function() {
        if (this.menu && this.menu.record) {
            return this.menu.record;
        }

        var ids = this.getSelectedAccessIds();
        if (!ids.length) {
            return null;
        }

        var id = parseInt(ids[0], 10) || 0;
        if (!id || !this.store) {
            return null;
        }

        var rec = this.store.getById ? this.store.getById(id) : null;
        if (!rec && this.store.each) {
            this.store.each(function(item) {
                if (!rec && parseInt(item.get('id'), 10) === id) {
                    rec = item;
                }
            });
        }

        return rec;
    },

    updateActionButtons: function() {
        var ids = this.getSelectedAccessIds();
        var updateBtn = Ext.getCmp(this.updateBtnId);
        var removeBtn = Ext.getCmp(this.removeBtnId);

        if (updateBtn) {
            updateBtn.setDisabled(ids.length !== 1);
        }
        if (removeBtn) {
            removeBtn.setDisabled(ids.length < 1);
        }
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSingleSelectedAccess();
        if (!rec) {
            return false;
        }

        var isActive = Training.utils.toBool(Training.utils.getRecordValue(rec, 'is_active'));

        this.menu.record = rec;
        this.menu.removeAll();
        this.menu.add({
            text: 'Изменить',
            handler: function() {
                this.updateAccess(rec);
            },
            scope: this
        }, {
            text: isActive ? 'Выключить' : 'Включить',
            handler: function() {
                this.toggleActive(rec);
            },
            scope: this
        }, '-', {
            text: 'Удалить',
            handler: function() {
                this.removeAccess(rec);
            },
            scope: this
        });

        return true;
    },

    createAccess: function() {
        var w = MODx.load({
            xtype: 'training-window-course-access',
            courseId: this.courseId,
            baseParams: {
                action: 'mgr/course/access/create'
            },
            listeners: {
                success: {
                    fn: function() {
                        this.menu.record = null;
                        this.refresh();
                    },
                    scope: this
                }
            }
        });

        w.setValues({
            course_id: this.courseId,
            principal_type: 'user',
            access_role: 'employee',
            is_active: 1
        });
        w.show();
    },

    updateAccess: function(rec) {
        rec = rec || this.getSingleSelectedAccess();
        if (!rec) {
            MODx.msg.alert('Внимание', 'Выбери одну запись доступа для редактирования');
            return false;
        }

        var recordData = rec.data || rec;
        var w = MODx.load({
            xtype: 'training-window-course-access',
            title: 'Изменить доступ',
            baseParams: {
                action: 'mgr/course/access/update'
            },
            courseId: this.courseId,
            record: recordData,
            listeners: {
                success: {
                    fn: function() {
                        this.menu.record = null;
                        this.refresh();
                    },
                    scope: this
                }
            }
        });

        w.show();
    },

    toggleActive: function(rec) {
        rec = rec || this.getSingleSelectedAccess();
        if (!rec) {
            return false;
        }

        var values = Ext.apply({}, rec.data || rec);
        values.action = 'mgr/course/access/update';
        values.course_id = this.courseId;
        values.is_active = Training.utils.toBool(values.is_active) ? 0 : 1;

        this.getEl().mask('Сохраняем доступ...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: values,
            listeners: {
                success: {
                    fn: function() {
                        this.getEl().unmask();
                        this.menu.record = null;
                        this.refresh();
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось сохранить доступ');
                    },
                    scope: this
                }
            }
        });
    },

    removeAccess: function(rec) {
        var ids = [];
        if (rec) {
            ids = [Training.utils.getRecordValue(rec, 'id')];
        } else {
            ids = this.getSelectedAccessIds();
        }

        var cleanIds = [];
        Ext.each(ids, function(item) {
            item = parseInt(item, 10) || 0;
            if (item) {
                cleanIds.push(item);
            }
        });
        ids = cleanIds;

        if (!ids.length) {
            MODx.msg.alert('Внимание', 'Выбери доступы для удаления');
            return false;
        }

        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные доступы?' : 'Удалить выбранный доступ?',
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/access/remove',
                ids: ids.join(',')
            },
            listeners: {
                success: {
                    fn: function() {
                        this.menu.record = null;
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
    },

    syncUsers: function() {
        this.getEl().mask('Синхронизируем пользователей...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/access/syncusers',
                course_id: this.courseId
            },
            listeners: {
                success: {
                    fn: function(r) {
                        this.getEl().unmask();
                        this.menu.record = null;
                        this.refresh();
                        var data = Training.utils.getResultData(r) || {};
                        MODx.msg.alert(
                            'Готово',
                            'Синхронизация пересобирает персональные назначения курса в training_user_courses по текущим активным доступам.<br><br>' +
                            'Создано: ' + (data.created || 0) + '<br>Обновлено: ' + (data.updated || 0) + '<br>Отозвано: ' + (data.deactivated || 0)
                        );
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось синхронизировать пользователей');
                    },
                    scope: this
                }
            }
        });
    }
});

Ext.reg('training-grid-course-access', Training.grid.CourseAccess);
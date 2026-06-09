Training.combo.ManagerLinkUsers = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'user_id',
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
        listWidth: 520,
        store: new Ext.data.JsonStore({
            url: Training.config.connector_url,
            root: 'results',
            totalProperty: 'total',
            idProperty: 'id',
            fields: ['id', 'username', 'fullname', 'email', 'display'],
            baseParams: {
                action: 'mgr/managerlink/users'
            }
        })
    });

    Training.combo.ManagerLinkUsers.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.ManagerLinkUsers, MODx.combo.ComboBox);
Ext.reg('training-combo-manager-link-users', Training.combo.ManagerLinkUsers);

Training.window.ManagerLink = function(config) {
    config = config || {};
    this.record = config.record || null;
    this.isUpdate = !!this.record;
    this.managerFieldId = Ext.id();
    this.employeeFieldId = Ext.id();

    Ext.applyIf(config, {
        title: this.isUpdate ? 'Редактирование связи' : 'Добавление связи',
        width: 620,
        autoHeight: true,
        url: Training.config.connector_url,
        action: this.isUpdate ? 'mgr/managerlink/update' : 'mgr/managerlink/create',
        fields: [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'training-combo-manager-link-users',
            id: this.managerFieldId,
            name: 'manager_user_id',
            fieldLabel: 'Директор',
            valueNotFoundText: this.record ? (this.record.manager_label || '') : ''
        }, {
            xtype: 'training-combo-manager-link-users',
            id: this.employeeFieldId,
            name: 'employee_user_id',
            fieldLabel: 'Сотрудник',
            valueNotFoundText: this.record ? (this.record.employee_label || '') : ''
        }, {
            xtype: 'xcheckbox',
            boxLabel: 'Связь активна',
            hideLabel: true,
            name: 'is_active',
            checked: this.record ? Training.utils.toBool(this.record.is_active) : true
        }]
    });

    Training.window.ManagerLink.superclass.constructor.call(this, config);
    this.on('afterrender', this.applyRecord, this);
    this.on('show', function() {
        Ext.defer(this.applyRecord, 50, this);
    }, this);
};
Ext.extend(Training.window.ManagerLink, MODx.Window, {
    applyComboDisplay: function(field, value, label) {
        if (!field) {
            return;
        }

        value = parseInt(value, 10) || 0;
        label = label || '';

        var store = field.getStore ? field.getStore() : null;
        if (store && value) {
            var rec = null;
            if (store.getById) {
                rec = store.getById(value) || store.getById(String(value));
            }
            if (!rec && store.recordType) {
                rec = new store.recordType({
                    id: value,
                    username: '',
                    fullname: '',
                    email: '',
                    display: label,
                    name: label
                }, value);
                store.add(rec);
            }
        }

        field.valueNotFoundText = label;
        field.lastSelectionText = label;

        if (field.hiddenField) {
            field.hiddenField.value = value;
        }

        field.setValue(String(value));

        if (typeof field.setRawValue === 'function') {
            field.setRawValue(label);
        }

        field.value = String(value);

        Ext.defer(function() {
            if (!field || field.destroyed) {
                return;
            }
            field.valueNotFoundText = label;
            field.lastSelectionText = label;
            if (field.hiddenField) {
                field.hiddenField.value = value;
            }
            if (typeof field.setRawValue === 'function') {
                field.setRawValue(label);
            }
            field.value = String(value);
        }, 120);
    },

    applyRecord: function() {
        if (!this.fp || !this.record) {
            return;
        }

        var form = this.fp.getForm();
        form.setValues(this.record);
        Training.utils.setCheckboxValue(form, 'is_active', this.record.is_active);

        var managerField = form.findField('manager_user_id') || Ext.getCmp(this.managerFieldId);
        var employeeField = form.findField('employee_user_id') || Ext.getCmp(this.employeeFieldId);

        this.applyComboDisplay(managerField, this.record.manager_user_id || 0, this.record.manager_label || '');
        this.applyComboDisplay(employeeField, this.record.employee_user_id || 0, this.record.employee_label || '');
    },

    submit: function() {
        var form = this.fp ? this.fp.getForm() : null;
        if (!form || !form.isValid()) {
            return false;
        }

        var managerField = form.findField('manager_user_id');
        var employeeField = form.findField('employee_user_id');
        var managerUserId = parseInt(managerField.getValue(), 10) || 0;
        var employeeUserId = parseInt(employeeField.getValue(), 10) || 0;

        if (!managerUserId) {
            MODx.msg.alert('Внимание', 'Выбери директора');
            return false;
        }
        if (!employeeUserId) {
            MODx.msg.alert('Внимание', 'Выбери сотрудника');
            return false;
        }

        this.getEl().mask('Сохраняем связь...');
        form.submit({
            url: this.config.url,
            params: {
                action: this.config.action,
                manager_user_id: managerUserId,
                employee_user_id: employeeUserId
            },
            success: function() {
                this.getEl().unmask();
                this.hide();
                var grid = Ext.getCmp('training-grid-manager-links');
                if (grid) {
                    grid.menu.record = null;
                    grid.refresh();
                }
                this.fireEvent('success');
            },
            failure: function(formObj, action) {
                this.getEl().unmask();
                var message = 'Не удалось сохранить связь';
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
Ext.reg('training-window-manager-link', Training.window.ManagerLink);

Training.grid.ManagerLinks = function(config) {
    config = config || {};
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.updateBtnId = Ext.id();
    this.removeBtnId = Ext.id();

    Ext.applyIf(config, {
        id: 'training-grid-manager-links',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/managerlink/getlist'
        },
        sm: this.sm,
        paging: true,
        remoteSort: true,
        autoHeight: true,
        anchor: '100%',
        multi_select: true,
        fields: [
            'id',
            'manager_user_id',
            'manager_label',
            'employee_user_id',
            'employee_label',
            'is_active',
            'createdon',
            'createdby',
            'createdby_label'
        ],
        columns: [this.sm, {
            header: 'ID',
            dataIndex: 'id',
            width: 60
        }, {
            header: 'Директор',
            dataIndex: 'manager_label',
            width: 320
        }, {
            header: 'Сотрудник',
            dataIndex: 'employee_label',
            width: 320
        }, {
            header: 'Активна',
            dataIndex: 'is_active',
            width: 90,
            renderer: Training.utils.renderBoolean
        }, {
            header: 'Создал',
            dataIndex: 'createdby_label',
            width: 180
        }, {
            header: 'Создано',
            dataIndex: 'createdon',
            width: 140
        }],
        tbar: [{
            text: 'Добавить связь',
            handler: this.createLink,
            scope: this
        }, '-', {
            id: this.updateBtnId,
            text: 'Изменить',
            disabled: true,
            handler: function(){ this.updateLink(); },
            scope: this
        }, {
            id: this.removeBtnId,
            text: 'Удалить',
            disabled: true,
            handler: function(){ this.removeLink(); },
            scope: this
        }, '->', {
            text: 'Обновить',
            handler: function() {
                this.refresh();
            },
            scope: this
        }],
        listeners: {
            rowclick: {
                fn: function() {
                    this.menu.record = null;
                    this.updateActionButtons();
                },
                scope: this
            },
            rowdblclick: {
                fn: function(grid, rowIndex) {
                    var rec = grid.store.getAt(rowIndex);
                    if (rec) {
                        this.updateLink(rec);
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

    Training.grid.ManagerLinks.superclass.constructor.call(this, config);

    this.getStore().on('load', function() {
        this.menu.record = null;
        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        if (sm && sm.clearSelections) {
            sm.clearSelections();
        }
        this.updateActionButtons();
    }, this);

    if (this.sm && this.sm.on) {
        this.sm.on('selectionchange', this.updateActionButtons, this);
    }
};

Ext.extend(Training.grid.ManagerLinks, MODx.grid.Grid, {
    getSelectedLinkIds: function() {
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

    getSingleSelectedLink: function() {
        if (this.menu && this.menu.record) {
            return this.menu.record;
        }

        var ids = this.getSelectedLinkIds();
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
        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        var count = 0;

        if (sm) {
            if (typeof sm.getCount === 'function') {
                count = sm.getCount();
            } else if (typeof sm.getSelections === 'function') {
                count = sm.getSelections().length;
            }
        }

        if (!count && this.menu && this.menu.record) {
            count = 1;
        }

        var updateBtn = Ext.getCmp(this.updateBtnId);
        var removeBtn = Ext.getCmp(this.removeBtnId);

        if (updateBtn) {
            updateBtn.setDisabled(count !== 1);
        }
        if (removeBtn) {
            removeBtn.setDisabled(count < 1);
        }
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSingleSelectedLink();
        if (!rec) {
            return false;
        }

        var isActive = Training.utils.toBool(Training.utils.getRecordValue(rec, 'is_active'));
        this.menu.record = rec;
        this.menu.removeAll();
        this.menu.add({
            text: 'Изменить',
            handler: function() {
                this.updateLink(rec);
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
                this.removeLink(rec);
            },
            scope: this
        });

        return true;
    },

    createLink: function() {
        var w = MODx.load({
            xtype: 'training-window-manager-link',
            baseParams: {
                action: 'mgr/managerlink/create'
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
            is_active: 1
        });
        w.show();
    },

    updateLink: function(rec) {
        rec = rec || this.getSingleSelectedLink();
        if (!rec) {
            MODx.msg.alert('Внимание', 'Выбери одну связь для редактирования');
            return false;
        }

        var recordData = rec.data || rec;
        var w = MODx.load({
            xtype: 'training-window-manager-link',
            title: 'Изменить связь',
            baseParams: {
                action: 'mgr/managerlink/update'
            },
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
        rec = rec || this.getSingleSelectedLink();
        if (!rec) {
            return false;
        }

        var values = Ext.apply({}, rec.data || rec);
        values.action = 'mgr/managerlink/update';
        values.is_active = Training.utils.toBool(values.is_active) ? 0 : 1;

        this.getEl().mask('Сохраняем связь...');
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
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось сохранить связь');
                    },
                    scope: this
                }
            }
        });
    },

    removeLink: function(rec) {
        var ids = [];
        if (rec) {
            ids = [Training.utils.getRecordValue(rec, 'id')];
        } else {
            ids = this.getSelectedLinkIds();
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
            MODx.msg.alert('Внимание', 'Выбери связи для удаления');
            return false;
        }

        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные связи?' : 'Удалить выбранную связь?',
            url: Training.config.connector_url,
            params: {
                action: 'mgr/managerlink/remove',
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
    }
});

Ext.reg('training-grid-manager-links', Training.grid.ManagerLinks);

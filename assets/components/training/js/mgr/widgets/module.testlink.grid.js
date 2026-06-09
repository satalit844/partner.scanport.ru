Training.combo.ModuleActivityType = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'link_type',
        fieldLabel: config.fieldLabel || 'Тип активности',
        mode: 'local',
        editable: false,
        triggerAction: 'all',
        store: new Ext.data.ArrayStore({
            fields: ['value', 'label'],
            data: [
                ['test', 'Тест'],
                ['practice', 'Практическая работа']
            ]
        }),
        valueField: 'value',
        displayField: 'label',
        value: config.value || 'test',
        anchor: '100%'
    });

    Training.combo.ModuleActivityType.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.ModuleActivityType, MODx.combo.ComboBox);
Ext.reg('training-combo-module-activity-type', Training.combo.ModuleActivityType);


Training.combo.UserTestTests = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'usertest_test_id',
        fieldLabel: config.fieldLabel || 'Тест',
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
            fields: ['id', 'name', 'description', 'display', 'active', 'type', 'test_type', 'count_questions', 'time_test'],
            baseParams: {
                action: 'mgr/module/testlink/tests',
                active_only: 1
            }
        })
    });

    Training.combo.UserTestTests.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.UserTestTests, MODx.combo.ComboBox);
Ext.reg('training-combo-usertest-tests', Training.combo.UserTestTests);


Training.combo.ModulePractices = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        hiddenName: config.name || 'practice_id',
        fieldLabel: config.fieldLabel || 'Практическое задание',
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
            fields: ['id', 'title', 'name', 'description', 'display', 'active', 'course_id', 'module_id'],
            baseParams: {
                action: 'mgr/module/testlink/practices',
                active_only: 1,
                course_id: config.courseId || Training.config.course_id || 0,
                module_id: config.moduleId || Training.config.module_id || 0
            }
        })
    });

    Training.combo.ModulePractices.superclass.constructor.call(this, config);
};
Ext.extend(Training.combo.ModulePractices, MODx.combo.ComboBox);
Ext.reg('training-combo-module-practices', Training.combo.ModulePractices);


Training.window.ModuleTestLink = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;
    this.record = config.record || null;
    this.isUpdate = !!this.record;
    this.defaultLinkType = config.linkType || (this.record && this.record.link_type ? this.record.link_type : 'test');
    this.typeFieldId = Ext.id();
    this.testFieldId = Ext.id();
    this.practiceFieldId = Ext.id();

    Ext.applyIf(config, {
        title: this.isUpdate ? 'Изменить привязку' : (this.defaultLinkType === 'practice' ? 'Добавить практическую работу' : 'Добавить тест'),
        width: 640,
        autoHeight: true,
        url: Training.config.connector_url,
        action: this.isUpdate ? 'mgr/module/testlink/update' : 'mgr/module/testlink/create',
        fields: [{
            xtype: 'hidden',
            name: 'id'
        }, {
            xtype: 'hidden',
            name: 'course_id',
            value: this.courseId
        }, {
            xtype: 'hidden',
            name: 'module_id',
            value: this.moduleId
        }, {
            xtype: 'training-combo-module-activity-type',
            name: 'link_type',
            id: this.typeFieldId,
            value: this.defaultLinkType,
            listeners: {
                select: {fn: this.applyActivityType, scope: this},
                change: {fn: this.applyActivityType, scope: this}
            }
        }, {
            xtype: 'training-combo-usertest-tests',
            name: 'usertest_test_id',
            id: this.testFieldId
        }, {
            xtype: 'training-combo-module-practices',
            name: 'practice_id',
            id: this.practiceFieldId,
            courseId: this.courseId,
            moduleId: this.moduleId,
            hidden: true
        }, {
            xtype: 'numberfield',
            fieldLabel: 'Порядок',
            name: 'sort_order',
            allowDecimals: false,
            allowNegative: false,
            value: 1,
            anchor: '100%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: 'Обязательная активность',
            hideLabel: true,
            name: 'is_required',
            checked: true
        }, {
            xtype: 'numberfield',
            fieldLabel: 'Макс. попыток',
            name: 'max_attempts',
            allowDecimals: false,
            allowNegative: false,
            value: this.defaultLinkType === 'practice' ? 5 : 0,
            anchor: '100%'
        }, {
            xtype: 'numberfield',
            fieldLabel: 'Проходной %',
            name: 'min_pass_percent',
            allowDecimals: true,
            decimalPrecision: 2,
            allowNegative: false,
            minValue: 0,
            maxValue: 100,
            value: 0,
            anchor: '100%'
        }, {
            xtype: 'xcheckbox',
            boxLabel: 'Блокировать следующий модуль до успешного прохождения',
            hideLabel: true,
            name: 'block_next_module_until_passed',
            checked: false
        }]
    });

    Training.window.ModuleTestLink.superclass.constructor.call(this, config);
    this.on('afterrender', this.applyRecord, this);
};

Ext.extend(Training.window.ModuleTestLink, MODx.Window, {
    getTypeValue: function() {
        var typeField = Ext.getCmp(this.typeFieldId);
        var value = typeField ? typeField.getValue() : this.defaultLinkType;
        return value === 'practice' ? 'practice' : 'test';
    },

    applyActivityType: function() {
        var type = this.getTypeValue();
        var testField = Ext.getCmp(this.testFieldId);
        var practiceField = Ext.getCmp(this.practiceFieldId);

        if (testField) {
            if (type === 'practice') {
                testField.hide();
                testField.allowBlank = true;
            } else {
                testField.show();
                testField.allowBlank = false;
            }
        }

        if (practiceField) {
            if (type === 'practice') {
                practiceField.show();
                practiceField.allowBlank = false;
                if (practiceField.store && practiceField.store.baseParams) {
                    practiceField.store.baseParams.course_id = this.courseId;
                    practiceField.store.baseParams.module_id = this.moduleId;
                }
            } else {
                practiceField.hide();
                practiceField.allowBlank = true;
            }
        }

        if (this.fp && this.fp.doLayout) {
            this.fp.doLayout();
        }
        if (this.doLayout) {
            this.doLayout();
        }
    },

    applyRecord: function() {
        if (!this.fp) {
            return;
        }

        var form = this.fp.getForm();
        if (this.record) {
            form.setValues(this.record);
            Training.utils.setCheckboxValue(form, 'is_required', this.record.is_required);
            Training.utils.setCheckboxValue(form, 'block_next_module_until_passed', this.record.block_next_module_until_passed);
        }

        var typeField = Ext.getCmp(this.typeFieldId);
        var type = this.record && this.record.link_type ? this.record.link_type : this.defaultLinkType;
        if (typeField) {
            typeField.setValue(type);
            typeField.setRawValue(type === 'practice' ? 'Практическая работа' : 'Тест');
        }

        var testField = Ext.getCmp(this.testFieldId);
        var practiceField = Ext.getCmp(this.practiceFieldId);
        if (this.record && type === 'practice') {
            if (practiceField) {
                practiceField.setValue(this.record.practice_id || this.record.usertest_test_id || 0);
                practiceField.setRawValue(this.record.test_display || this.record.practice_title || '');
            }
        } else if (this.record && testField) {
            testField.setValue(this.record.usertest_test_id || 0);
            testField.setRawValue(this.record.test_display || this.record.test_name || '');
        }

        this.applyActivityType();
    }
});

Ext.reg('training-window-module-testlink', Training.window.ModuleTestLink);


Training.grid.ModuleTestLinks = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config, {
        id: 'training-grid-module-test-links',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/module/testlinks/getlist',
            module_id: this.moduleId,
            course_id: this.courseId
        },
        sm: this.sm,
        fields: [
            'id', 'course_id', 'module_id', 'usertest_test_id', 'practice_id', 'link_type', 'link_type_label',
            'sort_order', 'is_required', 'max_attempts', 'min_pass_percent',
            'block_next_module_until_passed', 'test_name', 'test_description', 'test_active', 'test_display', 'practice_title', 'createdon'
        ],
        paging: true,
        remoteSort: true,
        sortBy: 'sort_order',
        sortDir: 'ASC',
        sortInfo: {field: 'sort_order', direction: 'ASC'},
        autoHeight: true,
        anchor: '100%',
        columns: [
            this.sm,
            {header: 'ID', dataIndex: 'id', width: 50},
            {header: 'Порядок', dataIndex: 'sort_order', width: 70},
            {header: 'Тип', dataIndex: 'link_type_label', width: 180},
            {header: 'Тест / практика', dataIndex: 'test_display', width: 340},
            {
                header: 'Обязательно',
                dataIndex: 'is_required',
                width: 95,
                renderer: Training.utils.renderBoolean
            },
            {
                header: 'Попытки',
                dataIndex: 'max_attempts',
                width: 80,
                renderer: function(value) {
                    value = parseInt(value, 10) || 0;
                    return value > 0 ? value : '∞';
                }
            },
            {
                header: 'Проходной %',
                dataIndex: 'min_pass_percent',
                width: 100,
                renderer: function(value) {
                    var num = parseFloat(value || 0);
                    return (Math.round(num * 100) / 100) + '%';
                }
            },
            {
                header: 'Блок. след. модуль',
                dataIndex: 'block_next_module_until_passed',
                width: 130,
                renderer: Training.utils.renderBoolean
            }
        ],
        tbar: [{
            text: 'Добавить тест',
            handler: function() {
                this.createLink('test');
            },
            scope: this
        }, {
            text: 'Добавить практику',
            handler: function() {
                this.createLink('practice');
            },
            scope: this
        }, {
            text: 'Изменить',
            handler: function() {
                this.updateLink();
            },
            scope: this
        }, {
            text: 'Удалить',
            handler: function() {
                this.removeLink();
            },
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
                    this.getMenu();
                    if (this.menu) {
                        this.menu.showAt(e.getXY());
                    }
                    e.stopEvent();
                },
                scope: this
            }
        }
    });

    Training.grid.ModuleTestLinks.superclass.constructor.call(this, config);
};

Ext.extend(Training.grid.ModuleTestLinks, MODx.grid.Grid, {
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
        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        if (sm && sm.getSelections) {
            var selections = sm.getSelections();
            if (selections && selections.length) {
                return selections[0];
            }
        }

        if (this.menu && this.menu.record) {
            return this.menu.record;
        }

        var ids = this.getSelectedLinkIds();
        if (!ids.length || !this.store) {
            return null;
        }

        var id = parseInt(ids[0], 10) || 0;
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

    createLink: function(linkType) {
        linkType = linkType === 'practice' ? 'practice' : 'test';
        var w = MODx.load({
            xtype: 'training-window-module-testlink',
            title: linkType === 'practice' ? 'Добавить практическую работу' : 'Добавить тест',
            baseParams: {action: 'mgr/module/testlink/create'},
            moduleId: this.moduleId,
            courseId: this.courseId,
            linkType: linkType,
            listeners: {
                success: {
                    fn: function() {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
        w.setValues({
            course_id: this.courseId,
            module_id: this.moduleId,
            link_type: linkType,
            is_required: 1,
            max_attempts: linkType === 'practice' ? 5 : 0,
            min_pass_percent: 0,
            block_next_module_until_passed: 0
        });
        w.show();
    },

    updateLink: function(rec) {
        rec = rec || this.getSingleSelectedLink();
        if (!rec) {
            MODx.msg.alert('Внимание', 'Выбери привязку');
            return false;
        }

        var recordData = rec.data || rec;
        var w = MODx.load({
            xtype: 'training-window-module-testlink',
            title: 'Изменить привязку',
            baseParams: {action: 'mgr/module/testlink/update'},
            moduleId: this.moduleId,
            courseId: this.courseId,
            record: recordData,
            linkType: recordData.link_type || 'test',
            listeners: {
                success: {
                    fn: function() {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
        w.show();
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
            MODx.msg.alert('Внимание', 'Выбери привязки для удаления');
            return false;
        }

        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные привязки?' : 'Удалить выбранную привязку?',
            url: Training.config.connector_url,
            params: {
                action: 'mgr/module/testlink/remove',
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

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSingleSelectedLink();
        if (!rec) {
            return false;
        }

        this.addContextMenuItem([{
            text: 'Изменить',
            handler: function() {
                this.updateLink(rec);
            },
            scope: this
        }, '-', {
            text: 'Удалить',
            handler: function() {
                this.removeLink(rec);
            },
            scope: this
        }]);

        return true;
    }
});

Ext.reg('training-grid-module-test-links', Training.grid.ModuleTestLinks);

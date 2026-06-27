UserTest.grid.Groups = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-groups';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/group/getlist'
        },
        listeners: {
            rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            }
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
            /* getRowClass: function (rec) {
                return !rec.data.active
                    ? 'usertest-grid-row-disabled'
                    : '';
            } */
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    UserTest.grid.Groups.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Groups, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        var w = MODx.load({
            xtype: 'usertest-group-window-create',
            id: Ext.id(),
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
        w.setValues({active: true, count_questions: 0, count_questions_on_page: 0, time_test: 0});
        w.show(e.target);
    },

    updateItem: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/group/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-group-window-update',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('usertest_groups_remove')
                : _('usertest_group_remove'),
            text: ids.length > 1
                ? _('usertest_groups_remove_confirm')
                : _('usertest_group_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/group/remove',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        return true;
    },
	
	editTestGroup: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/group/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-grouptests-window',
                            id: Ext.id(),
                            record: r,
                            listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            }
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
	
    getFields: function () {
        return ['id', 'name', 'description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('usertest_item_name'),
            dataIndex: 'name',
            sortable: true,
            width: 200,
        }, {
            header: _('usertest_item_description'),
            dataIndex: 'description',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 120,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_group_create'),
            handler: this.createItem,
            scope: this
        }, '->', {
            xtype: 'usertest-field-search',
            width: 250,
            listeners: {
                search: {
                    fn: function (field) {
                        this._doSearch(field);
                    }, scope: this
                },
                clear: {
                    fn: function (field) {
                        field.setValue('');
                        this._clearSearch();
                    }, scope: this
                },
            }
        }];
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName == 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof(row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                }
                else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        }
        return this.processEvent('click', e);
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();

        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }

        return ids;
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-groups', UserTest.grid.Groups);

UserTest.window.CreateGroup = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-group-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_group_create'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/group/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateGroup.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.CreateGroup, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('usertest_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_item_description'),
            name: 'description',
            id: config.id + '-description',
            height: 150,
            anchor: '99%'
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-group-window-create', UserTest.window.CreateGroup);


UserTest.window.UpdateGroup = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-group-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_group_update'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/group/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateGroup.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateGroup, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('usertest_item_name'),
            name: 'name',
            id: config.id + '-name',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_item_description'),
            name: 'description',
            id: config.id + '-description',
            anchor: '99%',
            height: 150,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-group-window-update', UserTest.window.UpdateGroup);

UserTest.window.GroupTests = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grouptests-window';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_questions'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        //action: 'mgr/test/create',
        fields: [{
				xtype: 'hidden',
				fieldLabel: _('usertest_item_id'),
				name: 'id',
				id: config.id + '-' + 'id',
				anchor: '99%'
			},{
				xtype: 'displayfield',
				fieldLabel: _('usertest_group'),
				name: 'name',
				id: config.id + '-name',
				anchor: '99%',
				allowBlank: false,
			}, {
				//title: _('usertest_test_questions'),
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-grouplinks',
					id: config.id + '-grouplinks',
					cls: 'main-wrapper',
					group_id: config.record.object.id,
				}]
			}],
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                //this.submit()
            }, scope: this
        }],
		buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { config.closeAction !== 'close' ? this.hide() : this.close(); }
        }]
    });
    UserTest.window.GroupTests.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.GroupTests, MODx.Window, {});
Ext.reg('usertest-grouptests-window', UserTest.window.GroupTests);
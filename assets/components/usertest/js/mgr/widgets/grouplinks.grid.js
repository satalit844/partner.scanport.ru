UserTest.grid.GroupLinks = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-grouplinks';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/grouplink/getlist',
			group_id: config.group_id
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
		ddGroup: 'dd',
		enableDragDrop: true,
		listeners: {
			render: {fn: this._initDD, scope: this}
		}
    });
    UserTest.grid.GroupLinks.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.GroupLinks, MODx.grid.Grid, {
    windows: {},

	_initDD: function(grid) {
		new Ext.dd.DropTarget(grid.el, {
			ddGroup : 'dd',
			copy:false,
			notifyDrop : function(dd, e, data) {
				var store = grid.store.data.items;
				var target = store[dd.getDragData(e).rowIndex].data;
				var source = store[data.rowIndex].data;
				if ((target.parent == source.parent) && (target.id != source.id)) {
					dd.el.mask(_('loading'),'x-mask-loading');
					MODx.Ajax.request({
						url: UserTest.config.connector_url
						,params: {
							action: 'mgr/grouplink/sort'
							,source: source.id
							,target: target.id
						}
						,listeners: {
							success: {fn:function(r) {dd.el.unmask();grid.refresh();},scope:grid}
							,failure: {fn:function(r) {dd.el.unmask();},scope:grid}
						}
					});
				}
			}
		});
	},
	
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        group_id = this.config.group_id;
		var w = MODx.load({
            xtype: 'usertest-grouplink-window-create',
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
        w.setValues({group_id: group_id});
        w.show(e.target);
    },

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('usertest_grouplinks_remove')
                : _('usertest_grouplink_remove'),
            text: ids.length > 1
                ? _('usertest_grouplinks_remove_confirm')
                : _('usertest_grouplink_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/grouplink/remove',
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
	
    getFields: function () {
        return ['id', 'menuindex', 'group_id', 'test_id', 'test_name', 'test_description', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
			header: _('usertest_menuindex'),
            dataIndex: 'menuindex',
            sortable: false,
            width: 70,
        }, {
			header: _('usertest_test_id'),
            dataIndex: 'test_id',
            sortable: true,
            width: 70,
        }, {
            header: _('usertest_item_name'),
            dataIndex: 'test_name',
            sortable: true,
            width: 100,
        }, {
            header: _('usertest_item_description'),
            dataIndex: 'test_description',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 50,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_grouplink_create'),
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
Ext.reg('usertest-grid-grouplinks', UserTest.grid.GroupLinks);

UserTest.window.CreateGroupLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grouplink-window-create';//usertest-grouplink-window-create
    }
    Ext.applyIf(config, {
        title: _('usertest_grouplink_create'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/grouplink/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateGroupLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.CreateGroupLink, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('usertest_group_id'),
            name: 'group_id',
            id: config.id + '-group_id',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'test-combo',
			fieldLabel: _('usertest_test'),
			//name: 'org_id',
			id: config.id + '-' + 'test_id',
			anchor: '99%',
			allowBlank: false,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-grouplink-window-create', UserTest.window.CreateGroupLink);

UserTest.combo.Dadata = function(config) {
    config = config || {};
    Ext.applyIf(config,{
	    minChars: 2,
	    hideTrigger: true,
		triggerAction: 'all',
	    emptyText: '',
		typeAhead: true,
	    //pageSize: true, // указание на то что нужно вывести листалку
		fields: ['value','search_value'],
		name: 'query',
		displayField: 'search_value',
		url: UserTest.config.connector_url,
		baseParams: config.baseParams || {},
		editable: true,
        autoSelect : false,
	});
	Ext.applyIf(config,{
        store: new Ext.data.JsonStore({
            url: config.connector || config.url
            ,root: 'results'
            ,totalProperty: 'total'
            ,fields: config.fields
            ,errorReader: MODx.util.JSONReader
            ,baseParams: config.baseParams || {}
            ,remoteSort: config.remoteSort || false
            ,autoDestroy: true
            ,listeners: {
                'loadexception': {fn: function(o,trans,resp) {
                    var status = _('code') + ': ' + resp.status + ' test ' + resp.statusText + '<br/>';
                    MODx.msg.alert(_('error'), status + resp.responseText);
                }}
            }
        }),
    });
    UserTest.combo.Dadata.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Dadata,Ext.form.ComboBox);
Ext.reg('combo-dadata',UserTest.combo.Dadata);

UserTest.combo.Test = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/test/getlist',

        },
		hideTrigger: false,
		fields: ['id' , 'name'],
		displayField: 'name',
		valueField: 'id',
		hiddenName:'test_id',
		hiddenValue: '',
    });
    UserTest.combo.Test.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Test ,UserTest.combo.Dadata);
Ext.reg('test-combo',UserTest.combo.Test);

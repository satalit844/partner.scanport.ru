UserTest.window.UserCategorys = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-usercategorys-window';
    }
	//console.info(config.record.object);
    Ext.applyIf(config, {
        title: _('usertest_categorys'),
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
				fieldLabel: _('usertest_test_id'),
				name: 'test_id',
				id: config.id + '-test_id',
				anchor: '99%',
				allowBlank: false,
			}, {
				//title: _('usertest_test_questions'),
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-usercategorys',
					cls: 'main-wrapper',
					result_id: config.record.object.id
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
    UserTest.window.UserCategorys.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UserCategorys, MODx.Window, {});
Ext.reg('usertest-usercategorys-window', UserTest.window.UserCategorys);

UserTest.grid.UserCategorys = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-usercategorys';
    }
	//console.info(config);
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/usercategory/getlist',
			result_id: config.result_id
        },
		pageSize: 10,
        listeners: {
            /* rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            } */
        },
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0,
/*             getRowClass: function (rec) {
                return !rec.data.active
                    ? 'usertest-grid-row-disabled'
                    : '';
            } */
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    UserTest.grid.UserCategorys.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.UserCategorys, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },
	
    getFields: function () {
        return ['id', 'result_id', 'category_id', 'category_name', 'cat_point', 'max_point', 'variant_id', 'variant'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_category_name'),
            dataIndex: 'category_name',
            sortable: false,
            width: 150,
        }, {
			header: _('usertest_point'),
            dataIndex: 'cat_point',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_max_point'),
            dataIndex: 'max_point',
            sortable: true,
            width: 100,
        }, {
            header: _('usertest_userresult_variant'),
            dataIndex: 'variant',
            sortable: false,
            width: 200,
        }];
    },

    getTopBar: function () {
        return ['->', {
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
Ext.reg('usertest-grid-usercategorys', UserTest.grid.UserCategorys);

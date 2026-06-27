UserTest.window.ShowTestsInVariantSet = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-window-show_tests_in_variant_set';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_variant_link'),
        width: 1200,
        autoHeight: true,
        url: UserTest.config.connector_url,
        //action: 'mgr/test/add_variant_set',
        fields: [{
				xtype: 'hidden',
				fieldLabel: _('usertest_item_id'),
				name: 'id',
				id: config.id + '-' + 'id',
				anchor: '99%'
			},{
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-show_tests_in_variant_set',
					id: config.id + '-grid-show_tests_in_variant_set',
					cls: 'main-wrapper',
					variant_set_id: config.record.object.id,
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
	//console.log(config);
    UserTest.window.ShowTestsInVariantSet.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.ShowTestsInVariantSet, MODx.Window, {});
Ext.reg('usertest-window-show_tests_in_variant_set', UserTest.window.ShowTestsInVariantSet);

UserTest.grid.ShowTestsInVariantSet = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-show_tests_in_variant_set';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/test/getlist',
			variant_set_id: config.variant_set_id
        },
		pageSize: 10,
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
        autoHeight: true
    });
    UserTest.grid.ShowTestsInVariantSet.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.ShowTestsInVariantSet, MODx.grid.Grid, {
    windows: {},
	
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },
	
    getFields: function () { 
		return ['id', 'name', 'description'];
    },

    getColumns: function (config) {
        var Columns1 =[{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 40
        }, {
            header: _('usertest_item_name'),
            dataIndex: 'name',
            sortable: true,
            width: 100,
        }, {
			header: _('usertest_item_description'),
			dataIndex: 'description',
			sortable: false,
			width: 100,
        }];
		return Columns1;
    },

    getTopBar: function (config) {
        var TopBar1 = [ '->', {
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
		return TopBar1;
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
Ext.reg('usertest-grid-show_tests_in_variant_set', UserTest.grid.ShowTestsInVariantSet);


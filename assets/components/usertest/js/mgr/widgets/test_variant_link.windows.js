UserTest.window.TestVariantLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_variant_link';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_variant_link'),
        width: 1200,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test/add_variant_set',
        fields: [{
				xtype: 'hidden',
				fieldLabel: _('usertest_item_id'),
				name: 'id',
				id: config.id + '-' + 'id',
				anchor: '99%'
			}, {
				xtype: 'displayfield',
				fieldLabel: _('usertest_test'),
				name: 'name',
				id: config.id + '-name',
				anchor: '99%',
				allowBlank: false,
			},{
				xtype: 'usertest-variant_set-combo',
				fieldLabel: _('usertest_variantset'),
				id: config.id + '-' + 'variant_set_id',
				anchor: '99%',
				listeners: {
					render: {
						fn: function (tf) {
							tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
								this.submit(false);
							}, this);
						}, scope: this
					},
					'select': {
						fn: function() { 
							this.submit(false);
						},scope:this}
				}
			},{
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-test_variant_link',
					id: config.id + '-grid-test_variant_link',
					cls: 'main-wrapper',
					test_id: config.record.object.id,
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
        }],
		listeners: {
            'success': {
				fn: function() { 
					var w1 = Ext.getCmp(this.config.id + '-grid-test_variant_link');
					w1.refresh();
				},scope:this}
        }
    });
	//console.log(config);
    UserTest.window.TestVariantLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.TestVariantLink, MODx.Window, {});
Ext.reg('usertest-test_variant_link', UserTest.window.TestVariantLink);

UserTest.grid.TestVariantLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-test_variant_link';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/test_variant_link/getlist',
			test_id: config.test_id
			//question_id: config.question_id
        },
		//save_action: 'mgr/test_variant_link/autosave',
		//autosave: true,
		pageSize: 10,
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
    UserTest.grid.TestVariantLink.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.TestVariantLink, MODx.grid.Grid, {
    windows: {},
	
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },
	
    updateItem: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;
		use_category = this.config.use_category;
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/test_variant_link/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-test_variant_link-update',
                            id: Ext.id(),
							use_category:use_category,
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
						r.object.name = this.config.name;
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
	
	editVariant: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var variant_id = this.menu.record.variant_id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/variant/get',
                id: variant_id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w1 = Ext.getCmp('usertest-variant-window-update');
						if (w1) {w1.hide().getEl().remove();}
						r.object.test_id = r.object.id;
						var w = MODx.load({
                            xtype: 'usertest-variant-window-update',
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
		return ['id', 'test_id', 'variant_id', 'start_point', 'use_custom_point', 'end_point', 'result', 'category_id', 'category_name', 'passed', 'actions'];
    },

    getColumns: function (config) {
        var Columns1 =[{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 40
        }, {
            header: _('usertest_variant_id'),
            dataIndex: 'variant_id',
            sortable: true,
            width: 40,
        }, {
			header: _('usertest_category_name'),
			dataIndex: 'category_name',
			sortable: false,
			width: 100,
		}, {
            header: _('usertest_use_custom_point'),
            dataIndex: 'use_custom_point',
			renderer: UserTest.utils.renderBoolean,
			sortable: false,
			width: 40,
		}, {
            header: _('usertest_start_point'),
            dataIndex: 'start_point',
            sortable: false,
            width: 40,
        }, {
			header: _('usertest_end_point'),
            dataIndex: 'end_point',
            sortable: false,
            width: 40,
        }, {
			header: _('usertest_passed'),
            dataIndex: 'passed',
            renderer: UserTest.utils.renderBoolean,
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_result'),
            dataIndex: 'result',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 70,
            id: 'actions'
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
Ext.reg('usertest-grid-test_variant_link', UserTest.grid.TestVariantLink);

UserTest.window.UpdateTestVariantLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_variant_link-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_item_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test_variant_link/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateTestVariantLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateTestVariantLink, MODx.Window, {

    getFields: function (config) {
        var Fields2 = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        },{
            xtype: 'hidden',
            name: 'test_id',
            id: config.id + '-test_id',
        },{
			xtype: 'hidden',
            name: 'variant_id',
            id: config.id + '-variant_id',
        },{
			xtype: 'xcheckbox',
            boxLabel: _('usertest_use_custom_point'),
            name: 'use_custom_point',
            id: config.id + '-use_custom_point',
		},{
            xtype: 'textfield',
			fieldLabel: _('usertest_start_point'),
			name: 'start_point',
			id: config.id + '-start_point',
			anchor: '99%',
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_end_point'),
			name: 'end_point',
			id: config.id + '-end_point',
			anchor: '99%',
        }];

		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-test_variant_link-update', UserTest.window.UpdateTestVariantLink);

UserTest.combo.VariantSet = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/variantsets/getlist',

        },
		hideTrigger: false,
		fields: ['id' , 'name'],
		displayField: 'name',
		valueField: 'id',
		hiddenName:'variant_set_id',
		hiddenValue: '',
    });
    UserTest.combo.VariantSet.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.VariantSet ,UserTest.combo.Dadata);
Ext.reg('usertest-variant_set-combo',UserTest.combo.VariantSet);


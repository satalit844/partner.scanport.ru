UserTest.window.Variants = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-variants-window';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_variants'),
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
				fieldLabel: _('usertest_variantset'),
				name: 'name',
				id: config.id + '-name',
				anchor: '99%',
				allowBlank: false,
			}, {
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-variants',
					cls: 'main-wrapper',
					variant_set_id: config.record.object.id,
					//name: config.record.object.name,
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
    UserTest.window.Variants.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.Variants, MODx.Window, {});
Ext.reg('usertest-variants-window', UserTest.window.Variants);

UserTest.grid.Variants = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-variants';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/variant/getlist',
			variant_set_id: config.variant_set_id
        },
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
        autoHeight: true,
    });
    UserTest.grid.Variants.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Variants, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        variant_set_id = this.config.variant_set_id;
		var w = MODx.load({
            xtype: 'usertest-variant-window-create',
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
        w.setValues({variant_set_id: variant_set_id, passed: false});
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
                action: 'mgr/variant/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
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

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('usertest_variants_remove')
                : _('usertest_variant_remove'),
            text: ids.length > 1
                ? _('usertest_variants_remove_confirm')
                : _('usertest_variant_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/variant/remove',
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
        return ['id', 'variant_set_id', 'start_point', 'end_point', 'result', 'category_id', 'category_name','passed', 'actions'];
    },

    getColumns: function (config) {
        var Columns1 = [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 40
        }, {
            header: _('usertest_variant_set_id'),
            dataIndex: 'variant_set_id',
            sortable: true,
            width: 40,
        }, {
			header: _('usertest_category_name'),
			dataIndex: 'category_name',
			sortable: false,
			width: 100,
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
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_variant_create'),
            handler: this.createItem,
            scope: this
        }, '->', 
		{
			xtype: 'category-combo',
			id: config.id + '-search-field-category',
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		},{
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
        if(tf){
			this.getStore().baseParams.query = tf.getValue();
		}
		var category_id = Ext.getCmp(this.config.id + '-search-field-category');
		if(category_id) this.getStore().baseParams.category_id = category_id.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
		this.getStore().baseParams.category_id = '';
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-variants', UserTest.grid.Variants);

UserTest.window.CreateVariant = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-variant-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_variant_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/variant/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateVariant.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariants == "1") {
			MODx.loadRTE(config.id + '-result');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariants == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-result');
		}
    },this);
};
Ext.extend(UserTest.window.CreateVariant, MODx.Window, {

    getFields: function (config) {
        //return ['id', 'test_id', 'start_point', 'end_point', 'result', 'actions'];
		var Fields1 = [{
            xtype: 'hidden',
            fieldLabel: _('usertest_variant_set_id'),
            name: 'variant_set_id',
            id: config.id + '-variant_set_id',
            anchor: '99%',
            allowBlank: false,
		}, {
			xtype: 'category-combo',
			fieldLabel: _('usertest_category_name'),
			id: config.id + '-' + 'category',
			anchor: '99%'
		}, {	
			xtype: 'textfield',
			fieldLabel: _('usertest_start_point'),
			name: 'start_point',
			id: config.id + '-start_point',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textfield',
			fieldLabel: _('usertest_end_point'),
			name: 'end_point',
			id: config.id + '-end_point',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'xcheckbox',
            boxLabel: _('usertest_passed'),
            name: 'passed',
            id: config.id + '-passed',
            checked: false,
		}, {
            xtype: 'textarea',
            fieldLabel: _('usertest_result'),
            name: 'result',
            id: config.id + '-result',
            height: 150,
            anchor: '99%'
        }];
		return Fields1;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-variant-window-create', UserTest.window.CreateVariant);


UserTest.window.UpdateVariant = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-variant-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_variant_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/variant/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateVariant.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariants == "1") {
			MODx.loadRTE(config.id + '-result');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariants == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-result');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateVariant, MODx.Window, {

    getFields: function (config) {
        var Fields2 = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            fieldLabel: _('usertest_variant_set_id'),
            name: 'variant_set_id',
            id: config.id + '-variant_set_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'category-combo',
			fieldLabel: _('usertest_category_name'),
			id: config.id + '-' + 'category',
			anchor: '99%'
		}, {
            xtype: 'textfield',
			fieldLabel: _('usertest_start_point'),
			name: 'start_point',
			id: config.id + '-start_point',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'textfield',
			fieldLabel: _('usertest_end_point'),
			name: 'end_point',
			id: config.id + '-end_point',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'xcheckbox',
            boxLabel: _('usertest_passed'),
            name: 'passed',
            id: config.id + '-passed',
            checked: false,
		}, {
            xtype: 'textarea',
            fieldLabel: _('usertest_result'),
            name: 'result',
            id: config.id + '-result',
            height: 150,
            anchor: '99%'
        }];
		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-variant-window-update', UserTest.window.UpdateVariant);

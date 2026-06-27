UserTest.grid.VariantSets = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-variantsets';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/variantsets/getlist',
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
    });
    UserTest.grid.VariantSets.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.VariantSets, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        // находим dom-элемент
		var w = Ext.getCmp('usertest-variantsets-window-create');
		// если есть, скрываем и удаляем
		if (w) {w.hide().getEl().remove();}
		var w = MODx.load({
            xtype: 'usertest-variantsets-window-create',
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
        w.setValues({active: true, count_questions: 0, count_questions_on_page: 0, time_variantsets: 0, type: 1, count_variantsets_answer: 0, use_block_q_number: 1});
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
                action: 'mgr/variantsets/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        // находим dom-элемент
						var w = Ext.getCmp('usertest-variantsets-window-update');
						// если есть, скрываем и удаляем
						if (w) {w.hide().getEl().remove();}
						var w = MODx.load({
                            xtype: 'usertest-variantsets-window-update',
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
                ? _('usertest_items_remove')
                : _('usertest_item_remove'),
            text: ids.length > 1
                ? _('usertest_items_remove_confirm')
                : _('usertest_item_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/variantsets/remove',
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

    editVariants: function (btn, e, row) {
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
                action: 'mgr/variantsets/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
						// находим dom-элемент
						var w = Ext.getCmp('usertest-variants-window');
						// если есть, скрываем и удаляем
						if (w) {w.hide().getEl().remove();}
                        var w = MODx.load({
                            xtype: 'usertest-variants-window',
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
	
	showTests: function (btn, e, row) {
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
                action: 'mgr/variantsets/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w1 = Ext.getCmp('usertest-window-show_tests_in_variant_set');
						if (w1) {w1.hide().getEl().remove();}
						r.object.question_id = r.object.id;
						r.object.test_id = "";
						var w = MODx.load({
                            xtype: 'usertest-window-show_tests_in_variant_set',
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
                        w.setValues({variant_set_id:r.object.id});
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
            width: 100,
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
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_item_create'),
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
Ext.reg('usertest-grid-variantsets', UserTest.grid.VariantSets);

UserTest.window.CreateVariantSet = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-variantsets-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_variantsets_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/variantsets/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateVariantSet.superclass.constructor.call(this, config);
	
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariantSet == "1") {
			MODx.loadRTE(config.id + '-description');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-description');
		}
    },this);
};
Ext.extend(UserTest.window.CreateVariantSet, MODx.Window, {

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
Ext.reg('usertest-variantsets-window-create', UserTest.window.CreateVariantSet);


UserTest.window.UpdateVariantSet = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-variantsets-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_variantsets_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/variantsets/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateVariantSet.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinVariantSet == "1") {
			MODx.loadRTE(config.id + '-description');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-description');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateVariantSet, MODx.Window, {

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
            height: 150,
            anchor: '99%'
        }];
    },
    loadDropZones: function () {
    }

});
Ext.reg('usertest-variantsets-window-update', UserTest.window.UpdateVariantSet);

UserTest.grid.Tests = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-tests';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/test/getlist',
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
            getRowClass: function (rec) {
                return !rec.data.active
                    ? 'usertest-grid-row-disabled'
                    : '';
            }
        },
        paging: true,
        remoteSort: true,
        autoHeight: true,
    });
    UserTest.grid.Tests.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Tests, MODx.grid.Grid, {
    windows: {},

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        // находим dom-элемент
		var w = Ext.getCmp('usertest-test-window-create');
		// если есть, скрываем и удаляем
		if (w) {w.hide().getEl().remove();}
		var w = MODx.load({
            xtype: 'usertest-test-window-create',
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
        w.setValues({active: true, count_questions: 0, count_questions_on_page: 0, time_test: 0, type: 1, count_test_answer: 0, use_block_q_number: 1});
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
                action: 'mgr/test/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        // находим dom-элемент
						var w = Ext.getCmp('usertest-test-window-update');
						// если есть, скрываем и удаляем
						if (w) {w.hide().getEl().remove();}
						var w = MODx.load({
                            xtype: 'usertest-test-window-update',
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
                ? _('usertest_tests_remove')
                : _('usertest_test_remove'),
            text: ids.length > 1
                ? _('usertest_tests_remove_confirm')
                : _('usertest_test_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/test/remove',
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

    disableItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/test/disable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },

    enableItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/test/enable',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        })
    },
	
	editTestQuestionLink: function (btn, e, row) {
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
                action: 'mgr/test/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w1 = Ext.getCmp('usertest-test_question_link');
						if (w1) {w1.hide().getEl().remove();}
						r.object.test_id = r.object.id;
						var w = MODx.load({
                            xtype: 'usertest-test_question_link',
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
                        w.setValues({test_id:r.object.id});
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
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
                action: 'mgr/test/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
						// находим dom-элемент
						var w = Ext.getCmp('usertest-test_variant_link');
						// если есть, скрываем и удаляем
						if (w) {w.hide().getEl().remove();}
                        var w = MODx.load({
                            xtype: 'usertest-test_variant_link',
                            id: Ext.id(),
                            record: r,
							/* listeners: {
                                success: {
                                    fn: function () {
                                        this.refresh();
                                    }, scope: this
                                }
                            } */
                        });
                        w.reset();
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
	
	addGroup: function (btn, e) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
		var w = Ext.getCmp('usertest-grouptestlink-window-create');
		if (w) {w.hide().getEl().remove();}
        var w = MODx.load({
            xtype: 'usertest-grouptestlink-window-create',
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
        w.setValues({test_ids: Ext.util.JSON.encode(ids)});
        w.show(e.target);
    },
	
	copyTest: function (btn, e) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: _('usertest_test_copy'),
            text: _('usertest_test_copy_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/test/copy',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function (param) {
						this.refresh();
                    }, scope: this
                }
            }
        });
		return true;
    },
    getFields: function () {
        return ['id', 'name', 'description', 'active', 'groups', 'actions'];
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
            header: _('usertest_item_active'),
            dataIndex: 'active',
            renderer: UserTest.utils.renderBoolean,
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_groups'),
            dataIndex: 'groups',
            sortable: false,
            width: 150,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 120,
            id: 'actions'
        }];
    },

    getTopBar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_test_create'),
            handler: this.createItem,
            scope: this
        }, '->', {
			xtype: 'group-combo',
			id: config.id + '-search-field-group',
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
		group = Ext.getCmp(this.config.id + '-search-field-group');
		if(group){
			this.getStore().baseParams.group = group.getValue();
		}
		this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
		group = Ext.getCmp(this.config.id + '-search-field-group');
		if(group){
			this.getStore().baseParams.group = "";
			group.setValue("");
		}
		this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-tests', UserTest.grid.Tests);



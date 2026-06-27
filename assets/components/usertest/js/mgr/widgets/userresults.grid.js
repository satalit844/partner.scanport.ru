UserTest.grid.UserResults = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-userresults';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/userresult/getlist'
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
    UserTest.grid.UserResults.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.UserResults, MODx.grid.Grid, {
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
		var test_name = this.menu.record.test_name;
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/userresult/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-userresult-window-update',
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
						r.object.test_name = test_name;
						//console.info(this.config,r.object);
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
                ? _('usertest_userresults_remove')
                : _('usertest_userresult_remove'),
            text: ids.length > 1
                ? _('usertest_userresults_remove_confirm')
                : _('usertest_userresult_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/userresult/remove',
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
	
	showAnswers: function (btn, e, row) {
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
                action: 'mgr/userresult/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-useranswers-window',
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
						//console.info(r.object);
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
	
	showCategorys: function (btn, e, row) {
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
                action: 'mgr/userresult/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-usercategorys-window',
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
						//console.info(r.object);
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
	
    getFields: function () {
        return ['id', 'test_id', "test_name", "user_id", "reg_user_name", "user_name", 'user_email', 'date', 'test_point', 'max_point', 'test_time', 'variant_id', 'variant', 'status_id', 'status', 'comment', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 50
        }, {
            header: _('usertest_test_id'),
            dataIndex: 'test_id',
            sortable: true,
            width: 50,
        }, {
			header: _('usertest_userresult_test_name'),
            dataIndex: 'test_name',
            sortable: true,
            width: 100,
        }, {
			header: _('usertest_userresult_reg_user_name'),
			dataIndex: 'reg_user_name',
			sortable: true,
			width: 70,
			renderer: UserTest.utils.userLink,
		},{
			header: _('usertest_userresult_user_name'),
            dataIndex: 'user_name',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_userresult_user_email'),
            dataIndex: 'user_email',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_userresult_date'),
            dataIndex: 'date',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_point'),
            dataIndex: 'test_point',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_max_point'),
            dataIndex: 'max_point',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_userresult_test_time'),
            dataIndex: 'test_time',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_userresult_variant_id'),
            dataIndex: 'variant_id',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_userresult_variant'),
            dataIndex: 'variant',
            sortable: true,
            width: 100,
        }, {
			header: _('usertest_userresult_status'),
            dataIndex: 'status',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_comment'),
            dataIndex: 'comment',
            sortable: true,
            width: 100,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
    },
	
	_exportExcel: function (tf) {
		test = Ext.getCmp(this.config.id + '-search-field-test').getValue();
		status = Ext.getCmp(this.config.id + '-search-field-status').getValue();
		user_name = Ext.getCmp(this.config.id + '-search-field-user').getValue();
		date1 = Ext.getCmp(this.config.id + '-xdatetime1').getValue();
		date2 = Ext.getCmp(this.config.id + '-xdatetime2').getValue();
		url ='/assets/components/usertest/save_test_result.php?test=' + test + '&status=' + status + '&user_name=' + user_name + '&date1=' + date1 + '&date2='+date2;
		window.open(url, '_blank');
	},
	
    getTopBar: function (config) {
        return [ {
				xtype: 'button',
				id: config.id + '-excel',
				text: '<i class="icon icon-file-excel-o"></i>',
				listeners: {
					click: {fn: this._exportExcel, scope: this}
				}
		},
		'->',
		{
			xtype: 'usertest-combo-result-status',
			id: config.id + '-search-field-status',
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
			xtype: 'textfield',
			name: 'test',
			width: 220,
			id: config.id + '-search-field-test',
			emptyText: _('usertest_userresult_test'),
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		}, {
			xtype: 'textfield',
			name: 'test',
			width: 200,
			id: config.id + '-search-field-user',
			emptyText: _('usertest_userresult_user'),
			listeners: {
				render: {
					fn: function (tf) {
						tf.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
							this._doSearch();
						}, this);
					}, scope: this
				}
			}
		}, {
			html: "от",
			//cls: 'panel-desc',
		}, {
			xtype : 'xdatetime'
			//,fieldLabel : "sdfv"
			,id: config.id + '-xdatetime1'
			,allowBlank : false
			,dateWidth : 120
			,timeWidth : 120
		}, {
			html: "до",
			//cls: 'panel-desc',
		}, {
			xtype : 'xdatetime'
			//,fieldLabel : "sdfv"
			,id: config.id + '-xdatetime2'
			,allowBlank : false
			,dateWidth : 120
			,timeWidth : 120
		}, {
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
		
		var status_id = Ext.getCmp(this.config.id + '-search-field-status');
		if(status_id) this.getStore().baseParams.status_id = status_id.getValue();
		
		var test = Ext.getCmp(this.config.id + '-search-field-test');
		if(test) this.getStore().baseParams.test = test.getValue();
		
		var user = Ext.getCmp(this.config.id + '-search-field-user');
		if(user) this.getStore().baseParams.user = user.getValue();
		
		var date1 = Ext.getCmp(this.config.id + '-xdatetime1');
		if(date1) this.getStore().baseParams.date1 = date1.getValue();
		
		var date2 = Ext.getCmp(this.config.id + '-xdatetime2');
		if(date2) this.getStore().baseParams.date2 = date2.getValue();
		
		this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
		
		this.getStore().baseParams.status_id = '';
		var status_id = Ext.getCmp(this.config.id + '-search-field-status');
		if(status_id) status_id.setValue('');
		
		this.getStore().baseParams.test = '';
		var test = Ext.getCmp(this.config.id + '-search-field-test');
		if(test) test.setValue('');
		
		this.getStore().baseParams.user = '';
		var user = Ext.getCmp(this.config.id + '-search-field-user');
		if(user) user.setValue('');
		
		this.getStore().baseParams.date1 = '';
		var date1 = Ext.getCmp(this.config.id + '-xdatetime1');
		if(date1) date1.setValue('');
		
		this.getStore().baseParams.date2 = '';
		var date2 = Ext.getCmp(this.config.id + '-xdatetime2');
		if(date2) date2.setValue('');
		
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-userresults', UserTest.grid.UserResults);

UserTest.window.UpdateUserResults = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-userresult-window-update';
    }
	
    Ext.applyIf(config, {
        title: _('usertest_userresult_update'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/userresult/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateUserResults.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateUserResults, MODx.Window, {

    getFields: function (config) {
		//console.info(config);
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
			xtype: 'displayfield',
			fieldLabel: _('usertest_test'),
			name: 'test_name',
			id: config.id + '-test_name',
			anchor: '99%',
		}, {
			xtype: 'displayfield',
			fieldLabel: _('usertest_userresult_user_name'),
			name: 'user_name',
			id: config.id + '-user_name',
			anchor: '99%',
		}, {
			xtype: 'textfield',
			fieldLabel: _('usertest_point'),
			name: 'test_point',
			id: config.id + '-test_point',
			anchor: '99%',
		},{	
			xtype: 'usertest-variant-combo',
			fieldLabel: _('usertest_userresult_variant'),
			//name: 'org_id',
			test_id: config.record.object.test_id,
			id: config.id + '-' + 'variant',
			anchor: '99%'	
		}, {
            xtype: 'textarea',
            fieldLabel: _('usertest_comment'),
            name: 'comment',
            id: config.id + '-comment',
            height: 150,
            anchor: '99%'
		},{	
			xtype: 'usertest-combo-result-status',
			fieldLabel: _('usertest_userresult_status'),
			//name: 'org_id',
			id: config.id + '-' + 'type_file',
			anchor: '99%'
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-userresult-window-update', UserTest.window.UpdateUserResults);

UserTest.combo.ResultStatus = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['status_id', 'label']
			,data: [
				[1 , "Начат"],
				[2 , "Завершен"],
				[3 , "Проверка"],
			]
		})
		,emptyText: _('usertest_userresult_status')
		,displayField: 'label'
		,valueField: 'status_id'
		,hiddenName: 'status_id'
		,mode: 'local'
		,triggerAction: 'all'
		,editable: false
		,selectOnFocus: false
		,preventRender: true
		,forceSelection: true
		,enableKeyEvents: true
	});
	UserTest.combo.ResultStatus.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.ResultStatus,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-result-status',UserTest.combo.ResultStatus);

UserTest.combo.Variant = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/variant/getlist',
			test_id: config.test_id,
			category_id: 0
        },
		hideTrigger: false,
		fields: ['id' , 'result'],
		displayField: 'result',
		valueField: 'id',
		hiddenName:'variant_id',
		hiddenValue: '',
    });
    UserTest.combo.Variant.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Variant ,UserTest.combo.Dadata);
Ext.reg('usertest-variant-combo',UserTest.combo.Variant);
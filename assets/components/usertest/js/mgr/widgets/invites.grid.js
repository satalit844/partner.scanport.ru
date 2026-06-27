UserTest.grid.Invites = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-invites';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/invite/getlist',
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
		ddInvite: 'dd',
		enableDragDrop: false,
		listeners: {
			render: {fn: this._initDD, scope: this}
		}
    });
    UserTest.grid.Invites.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Invites, MODx.grid.Grid, {
    windows: {},

	_initDD: function(grid) {
		new Ext.dd.DropTarget(grid.el, {
			ddInvite : 'dd',
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
							action: 'mgr/invite/sort'
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

    imprortItem: function (btn, e) {
		var w = MODx.load({
            xtype: 'usertest-invite-window-imprort',//usertest-invite-window-clear
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
        w.setValues({url_scheme:"http"});
        w.show(e.target);
    },
	
	clearItem: function (btn, e) {
		var w = MODx.load({
            xtype: 'usertest-invite-window-clear',//usertest-invite-window-clear
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
        w.setValues({days:"30"});
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
                action: 'mgr/invite/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-invite-window-update',
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
	disableItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/invite/disable',
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
                action: 'mgr/invite/enable',
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
	
    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('usertest_invites_remove')
                : _('usertest_invite_remove'),
            text: ids.length > 1
                ? _('usertest_invites_remove_confirm')
                : _('usertest_invite_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/invite/remove',
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
        return ['id', 'test_id', 'user_auth_code', 'user_email', 'user_name', 'user_pass', 'date', 'date_expired', 'url', 'result_id', 'active', 'send_email_if_empty_test', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
			header: _('usertest_test_id'),
            dataIndex: 'test_id',
            sortable: true,
            width: 70,
        }, {
            header: _('usertest_user_auth_code'),
            dataIndex: 'user_auth_code',
            sortable: true,
            width: 100,
        }, {
            header: _('usertest_userresult_user_email'),
            dataIndex: 'user_email',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_userresult_user_name'),
            dataIndex: 'user_name',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_user_pass'),
            dataIndex: 'user_pass',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_userresult_date'),
            dataIndex: 'date',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_invite_date_expired1'),
            dataIndex: 'date_expired',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_invite_url'),
            dataIndex: 'url',
            sortable: false,
            width: 200,
        }, {
			header: _('usertest_result_id'),
            dataIndex: 'result_id',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_item_active'),
            dataIndex: 'active',
            renderer: UserTest.utils.renderBoolean,
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_send_email_if_empty_test'),
            dataIndex: 'send_email_if_empty_test',
            renderer: UserTest.utils.renderBoolean,
            sortable: true,
            width: 70,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 150,
            id: 'actions'
        }];
    },

	_exportExcel: function (tf) {
		test = Ext.getCmp(this.config.id + '-search-field-test').getValue();
		date1 = Ext.getCmp(this.config.id + '-xdatetime1').getValue();
		date2 = Ext.getCmp(this.config.id + '-xdatetime2').getValue();
		url ='/assets/components/usertest/export_user_invites.php?test=' + test + '&date1=' + date1 + '&date2='+date2;
		window.open(url, '_blank');
	},
	
    getTopBar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_invite_imprort'),
            handler: this.imprortItem,
            scope: this
        },{
			text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_invite_export'),
            handler: this._exportExcel,
            scope: this
		},{
			text: '<i class="icon icon-trash-o action-red"></i>&nbsp;' + _('usertest_invite_clear'),
            handler: this.clearItem,
            scope: this
        }, '->', {
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
		
		var test = Ext.getCmp(this.config.id + '-search-field-test');
		if(test) this.getStore().baseParams.test = test.getValue();
		
		var date1 = Ext.getCmp(this.config.id + '-xdatetime1');
		if(date1) this.getStore().baseParams.date1 = date1.getValue();
		
		var date2 = Ext.getCmp(this.config.id + '-xdatetime2');
		if(date2) this.getStore().baseParams.date2 = date2.getValue();
		
		this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
		
		this.getStore().baseParams.test = '';
		var test = Ext.getCmp(this.config.id + '-search-field-test');
		if(test) test.setValue('');
		
		this.getStore().baseParams.date1 = '';
		var date1 = Ext.getCmp(this.config.id + '-xdatetime1');
		if(date1) date1.setValue('');
		
		this.getStore().baseParams.date2 = '';
		var date2 = Ext.getCmp(this.config.id + '-xdatetime2');
		if(date2) date2.setValue('');
		
        this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-invites', UserTest.grid.Invites);

UserTest.window.ImportInvite = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-invite-window-imprort';//usertest-invite-window-create
    }
    Ext.applyIf(config, {
        title: _('usertest_invite_imprort'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        //action: 'mgr/invite/imprort1',
        fields: this.getFields(config),
		buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { this.hide(); }
        },{
            text: _('usertest_invite_imprort')
            ,cls: 'primary-button'
            ,scope: this
            ,handler: function() { this.importInvites();}
        }]
    });
    UserTest.window.ImportInvite.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.ImportInvite, MODx.Window, {

    getFields: function (config) {
		//console.info('config getFields',config);
        return [{
            xtype: 'textfield',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_test_page_id'),
			name: 'test_page_id',
			id: config.id + '-' + 'test_page_id',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_auth_page_id'),
			name: 'auth_page_id',
			id: config.id + '-' + 'auth_page_id',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_url_scheme'),
			name: 'url_scheme',
			id: config.id + '-' + 'url_scheme',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'xdatetime',
			fieldLabel: _('usertest_invite_date_expired'),
			name: 'date_expired',
			id: config.id + '-' + 'date_expired',
			anchor: '99%',
			//allowBlank: false,
		},{
			xtype: 'xcheckbox',
            boxLabel: _('usertest_send_email_if_empty_test'),
            name: 'send_email_if_empty_test',
            id: config.id + '-send_email_if_empty_test',
		},{
			xtype: 'modx-combo-browser',
			fieldLabel: _('usertest_invite_excel_file'),
			name: 'excel_file',
			id: config.id + '-excel_file',
			hideFiles: true,
			source: MODx.config.default_media_source,
			hideSourceCombo: true,
			anchor: '99%'
        }];
    },
	
	importInvites: function () {
        //this.hide();
		//console.info('importInvites',this.config);
		var topic = '/usertest/';
		var register = 'mgr';
		this.console = MODx.load({
		   xtype: 'modx-console'
		   ,register: register
		   ,topic: topic
		   ,show_filename: 0
		   ,listeners: {
			 'shutdown': {fn:function() {
				 Ext.getCmp('usertest-grid-invites').refresh();
			 },scope:this}
		   }
		});
		this.console.show(Ext.getBody());
		
		var test_id = Ext.getCmp(this.config.id + '-test_id').getValue();
		var test_page_id = Ext.getCmp(this.config.id + '-test_page_id').getValue();
		var auth_page_id = Ext.getCmp(this.config.id + '-auth_page_id').getValue();
		var url_scheme = Ext.getCmp(this.config.id + '-url_scheme').getValue();
		var excel_file = Ext.getCmp(this.config.id + '-excel_file').getValue();
		var date_expired = Ext.getCmp(this.config.id + '-date_expired').getValue();
		var send_email_if_empty_test = Ext.getCmp(this.config.id + '-send_email_if_empty_test').getValue();
		
		MODx.Ajax.request({
			url: this.config.url
			,params: {
				action: 'mgr/invite/import'
				,register: register
				,topic: topic
				,test_id: test_id
				,test_page_id: test_page_id
				,auth_page_id: auth_page_id
				,url_scheme: url_scheme
				,excel_file: excel_file
				,date_expired: date_expired
				,send_email_if_empty_test: send_email_if_empty_test
			}
			,listeners: {
				'success':{fn:function() {
					this.console.fireEvent('complete');
				},scope:this}
			}
		});
		this.hide();
    },
	
    loadDropZones: function () {
    }

});
Ext.reg('usertest-invite-window-imprort', UserTest.window.ImportInvite);

UserTest.window.UpdateInvite = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-invite-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_invite_update'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/invite/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateInvite.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateInvite, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'textfield',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'textfield',
            fieldLabel: _('usertest_userresult_user_email'),
            name: 'user_email',
            id: config.id + '-user_email',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'textfield',
            fieldLabel: _('usertest_userresult_user_name'),
            name: 'user_name',
            id: config.id + '-user_name',
            anchor: '99%',
		},{
			xtype: 'textfield',
            fieldLabel: _('usertest_user_pass'),
            name: 'user_pass',
            id: config.id + '-user_pass',
            anchor: '99%',
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_test_page_id'),
			name: 'test_page_id',
			id: config.id + '-' + 'test_page_id',
			anchor: '99%',
			allowBlank: false, 
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_auth_page_id'),
			name: 'auth_page_id',
			id: config.id + '-' + 'auth_page_id',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_user_auth_code'),
			name: 'user_auth_code',
			id: config.id + '-' + 'user_auth_code',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_url_scheme'),
			name: 'url_scheme',
			id: config.id + '-' + 'url_scheme',
			anchor: '99%',
			allowBlank: false,
		},{
			xtype: 'textfield',
			fieldLabel: _('usertest_invite_url'),
			name: 'url',
			id: config.id + '-' + 'url',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'xdatetime',
			fieldLabel: _('usertest_invite_date_expired'),
			name: 'date_expired',
			id: config.id + '-' + 'date_expired',
			anchor: '99%',
			//allowBlank: false,
		},{
            xtype: 'xcheckbox',
            boxLabel: _('usertest_send_email_if_empty_test'),
            name: 'send_email_if_empty_test',
            id: config.id + '-send_email_if_empty_test',
		},{
            xtype: 'xcheckbox',
            boxLabel: _('usertest_item_active'),
            name: 'active',
            id: config.id + '-active',
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-invite-window-update', UserTest.window.UpdateInvite);

UserTest.window.ClearInvite = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-invite-window-clear';
    }
    Ext.applyIf(config, {
        title: _('usertest_invite_clear'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        //action: 'mgr/invite/clear1',
        fields: this.getFields(config),
		buttons: [{
            text: config.cancelBtnText || _('cancel')
            ,scope: this
            ,handler: function() { this.hide(); }
        },{
            text: _('usertest_invite_clear')
            ,cls: 'primary-button'
            ,scope: this
            ,handler: function() { this.ClearInvites();}
        }]
    });
    UserTest.window.ClearInvite.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.ClearInvite, MODx.Window, {

    getFields: function (config) {
		//console.info('config getFields',config);
        return [{
            xtype: 'textfield',
            fieldLabel: _('usertest_clear_invites_days'),
            name: 'days',
            id: config.id + '-days',
            anchor: '99%',
            allowBlank: false,
        }];
    },
	
	ClearInvites: function () {
		
		var days = Ext.getCmp(this.config.id + '-days').getValue();
		MODx.Ajax.request({
			url: this.config.url
			,params: {
				action: 'mgr/invite/clear'
				,days: days
			}
			,listeners: {
				'success':{fn:function() {
					Ext.getCmp('usertest-grid-invites').refresh();
					this.hide();
				},scope:this}
			}
		});
    },
	
    loadDropZones: function () {
    }

});
Ext.reg('usertest-invite-window-clear', UserTest.window.ClearInvite);
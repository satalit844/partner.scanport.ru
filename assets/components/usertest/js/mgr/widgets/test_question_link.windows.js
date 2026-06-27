UserTest.window.TestQuestionLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_question_link';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_question_link'),
        width: 1200,
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
				//title: _('usertest_test_questions'),
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-test_question_link',
					id: config.id + '-grid-test_question_link',
					cls: 'main-wrapper',
					test_id: config.record.object.test_id,
					question_id: config.record.object.question_id
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
    UserTest.window.TestQuestionLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.TestQuestionLink, MODx.Window, {});
Ext.reg('usertest-test_question_link', UserTest.window.TestQuestionLink);

UserTest.grid.TestQuestionLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-test_question_link';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/test_question_link/getlist',
			test_id: config.test_id,
			question_id: config.question_id
        },
		save_action: 'mgr/test_question_link/autosave',
		autosave: true,
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
		ddGroup: 'dd',
		enableDragDrop: true,
		listeners: {
			render: {fn: this._initDD, scope: this}
		}
    });
    UserTest.grid.TestQuestionLink.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.TestQuestionLink, MODx.grid.Grid, {
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
							action: 'mgr/test_question_link/sort'
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
        test_id = this.config.test_id;
		//name = this.config.name;
		//use_category = this.config.use_category;
		//parent = this.config.parent;
		var w = MODx.load({
            xtype: 'usertest-question-window-create',
            id: Ext.id(),
			//use_category:use_category,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
		DefaultQuestionsValidate = false;
		if(UserTest.config.DefaultQuestionsValidate == "1") DefaultQuestionsValidate = true;
        w.setValues({type: 1,type_file: 0,test_id:test_id,question_type:1,validate: DefaultQuestionsValidate});
        w.show(e.target);
    },
	
	addItem: function (btn, e) {
        test_id = this.config.test_id;
		question_id = this.config.question_id;
		test_question_link_id = this.config.id;
		//name = this.config.name;
		//use_category = this.config.use_category;
		//parent = this.config.parent;
		if(test_id){
			var w = MODx.load({
				xtype: 'usertest-test_question_link-select',
				id: Ext.id(),
				test_id:test_id,
				test_question_link_id:test_question_link_id,
				listeners: {
					success: {
						fn: function () {
							this.refresh();
						}, scope: this
					}
				}
			});
			w.reset();
			w.setValues({test_id:test_id,question_id:question_id});
			w.show(e.target);
		}else{
			var w = MODx.load({
				xtype: 'usertest-test_question_link-add',
				id: Ext.id(),
				//use_category:use_category,
				listeners: {
					success: {
						fn: function () {
							this.refresh();
						}, scope: this
					}
				}
			});
			w.reset();
			w.setValues({test_id:test_id,question_id:question_id});
			w.show(e.target);
		}
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
                action: 'mgr/test_question_link/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-test_question_link-update',
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
                action: 'mgr/test_question_link/remove',
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
	
	editQuestions: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var question_id = this.menu.record.question_id;

        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/question/get',
                id: question_id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w1 = Ext.getCmp('usertest-question-window-update');
						if (w1) {w1.hide().getEl().remove();}
						r.object.test_id = r.object.id;
						var w = MODx.load({
                            xtype: 'usertest-question-window-update',
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
	editAnswers: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        
		var question_id = this.menu.record.question_id;
		//var type = this.menu.record.type;
		//console.info(type);
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/question/get',
                id: question_id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        //console.info(r);
						var type = r.object.type;
						switch(type){
							case 1: case 2: case 3: case 6: case 10: case 12:
								var w = MODx.load({
									xtype: 'usertest-answers-window',
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
							break;
							case 4:
								MODx.msg.alert('Сообщение!','Этот тип вопроса не требует задания ответов.',function() {
								  //MODx.clearCache();
								},MODx);
							break;
							case 5:
								//console.info(r);
								var extended = r.object.extended;
								var q = [];var a = [];
								if(extended){
									extended = Ext.util.JSON.decode(extended);
									q = extended.q;
									a = extended.a;
									r.object.type_point = extended.type_point;
									r.object.point = extended.point;
								}
								//q[0] = "test";
								for (var i = 0; i < 10; i++) {
								   r.object["q[" +i +"]"] = q[i];
								   r.object["a[" +i +"]"] = a[i];
								}
								
								//console.info(extended);
								var w = MODx.load({
									xtype: 'usertest-answers-type5-window',
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
							break;
							case 7: case 8: case 9: case 11:
								//console.info(r);
								var w = MODx.load({
									xtype: 'usertest-questions-with-parent-window',
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
							break;
						}
                    }, scope: this
                }
            }
        });
    },
    getFields: function () { 
		return ['id','q_id', 'type', 'category_id', 'category_name', 'test_id', 'test_name', 'question_id', 'question_name', 'menuindex', 'actions'];
    },

    getColumns: function (config) {
        var Columns1 =[{
            header: _('usertest_item_id'),
            dataIndex: 'q_id',
            sortable: true,
            width: 70
        }, {
			header: _('usertest_menuindex'),
            dataIndex: 'menuindex',
            sortable: false,
            width: 70,
			editor: {xtype: 'textfield'},
        }, {
			header: _('usertest_test'),
            dataIndex: 'test_name',
            sortable: true,
            width: 200,
        }, {
			header: _('usertest_question_type'),
            dataIndex: 'type',
            sortable: true,
            width: 150,
			renderer: UserTest.utils.question_type,
        }, {
			header: _('usertest_category_name'),
			dataIndex: 'category_name',
			sortable: false,
			width: 100,
		},{
            header: _('usertest_question'),
            dataIndex: 'question_name',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
		return Columns1;
    },

    getTopBar: function (config) {
        var TopBar1 = [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_item_create'),
            handler: this.addItem,
            scope: this
        }, '->', {
			xtype: 'usertest-combo-question-type',
			id: config.id + '-search-field-type',
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
		if(config.test_id){
			TopBar1.splice(1, 0, {
					text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_item_create2'),
					handler: this.createItem,
					scope: this
				});
		};
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
        if(tf){
			this.getStore().baseParams.query = tf.getValue();
		}
		type = Ext.getCmp(this.config.id + '-search-field-type');
		if(type){
			this.getStore().baseParams.type = type.getValue();
		}
		category = Ext.getCmp(this.config.id + '-search-field-category');
		if(category){
			this.getStore().baseParams.category = category.getValue();
		}
		this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
		type = Ext.getCmp(this.config.id + '-search-field-type');
		if(type){
			this.getStore().baseParams.type = "";
		}
		category = Ext.getCmp(this.config.id + '-search-field-category');
		if(category){
			this.getStore().baseParams.category = "";
		}
		this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-test_question_link', UserTest.grid.TestQuestionLink);

UserTest.window.AddTestQuestionLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_question_link-add';
    }
    Ext.applyIf(config, {
        title: _('usertest_item_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test_question_link/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.AddTestQuestionLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.AddTestQuestionLink, MODx.Window, {

    getFields: function (config) {
        var Fields1 = [{
			xtype: 'usertest-test-combo',
			fieldLabel: _('usertest_test'),
			//name: 'org_id',
			id: config.id + '-' + 'test_id',
			anchor: '99%'
		},{
			xtype: 'usertest-question-combo',
			fieldLabel: _('usertest_question'),
			//name: 'org_id',
			id: config.id + '-' + 'question_id',
			anchor: '99%'
        }];
		return Fields1;
    },
    loadDropZones: function () {
    }

});
Ext.reg('usertest-test_question_link-add', UserTest.window.AddTestQuestionLink);


UserTest.window.UpdateTestQuestionLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_question_link-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_item_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test_question_link/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateTestQuestionLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateTestQuestionLink, MODx.Window, {

    getFields: function (config) {
        var Fields2 = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
			xtype: 'usertest-test-combo',
			fieldLabel: _('usertest_test'),
			//name: 'org_id',
			id: config.id + '-' + 'test_id',
			anchor: '99%'
		},{
			xtype: 'usertest-question-combo',
			fieldLabel: _('usertest_question'),
			//name: 'org_id',
			id: config.id + '-' + 'question_id',
			anchor: '99%'
        }];

		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-test_question_link-update', UserTest.window.UpdateTestQuestionLink);

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
Ext.reg('usertest-test-combo',UserTest.combo.Test);

UserTest.combo.Question = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/question/getlist',

        },
		hideTrigger: false,
		fields: ['id' , 'question'],
		displayField: 'question',
		valueField: 'id',
		hiddenName:'question_id',
		hiddenValue: '',
    });
    UserTest.combo.Question.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Question ,UserTest.combo.Dadata);
Ext.reg('usertest-question-combo',UserTest.combo.Question);


UserTest.window.SelectTestQuestionLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test_question_link-select';
    }
    Ext.applyIf(config, {
        title: _('usertest_item_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test_question_link/create',
        fields: this.getFields(config),
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
    UserTest.window.SelectTestQuestionLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.SelectTestQuestionLink, MODx.Window, {

    getFields: function (config) {
        var Fields1 = [{
			xtype: 'usertest-test-combo',
			fieldLabel: _('usertest_test'),
			//name: 'org_id',
			id: config.id + '-' + 'test_id',
			anchor: '99%'
		},{
			layout: 'anchor',
			items: [{
				xtype: 'usertest-grid-select-questions',
				id: config.id + '-usertest-grid-select-questions',
				cls: 'main-wrapper',
				test_id: config.test_id,
				test_question_link_id: config.test_question_link_id,
			}]
        }];
		return Fields1;
    },
    loadDropZones: function () {
    }

});
Ext.reg('usertest-test_question_link-select', UserTest.window.SelectTestQuestionLink);

UserTest.grid.SelectQuestions = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-select-questions';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/question/getlistselect',
			//test_id: config.test_id
        },
		pageSize: 20,
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
        //remoteSort: true,
        //autoHeight: true,
		//ddGroup: 'dd',
		//enableDragDrop: true,
		/* listeners: {
			render: {fn: this._initDD, scope: this}
		} */
    });
    UserTest.grid.SelectQuestions.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.SelectQuestions, MODx.grid.Grid, {
    windows: {},
	
	
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },
	
	createItem: function (btn, e) {
        test_id = this.config.test_id;
		//name = this.config.name;
		//use_category = this.config.use_category;
		var w = MODx.load({
            xtype: 'usertest-question-window-create',
            id: Ext.id(),
			//use_category:use_category,
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
						test_question_link_ext = Ext.getCmp(test_question_link_id);
						test_question_link_ext.refresh();
                    }, scope: this
                }
            }
        });
        w.reset();
		DefaultQuestionsValidate = false;
		if(UserTest.config.DefaultQuestionsValidate == "1") DefaultQuestionsValidate = true;
        //w.setValues({test_id: test_id,name: name,type: 1,type_file: 0});
		w.setValues({test_id: test_id,type: 1,type_file: 0,validate: DefaultQuestionsValidate});
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
		use_category = this.config.use_category;
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/question/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-question-window-update',
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

    removeItem: function () {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: ids.length > 1
                ? _('usertest_questions_remove')
                : _('usertest_question_remove'),
            text: ids.length > 1
                ? _('usertest_questions_remove_confirm')
                : _('usertest_question_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/question/remove',
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
	
	editAnswers: function (btn, e, row) {
        if (typeof(row) != 'undefined') {
            this.menu.record = row.data;
        }
        else if (!this.menu.record) {
            return false;
        }
        var id = this.menu.record.id;
		
		//var type = this.menu.record.type;
		//console.info(type);
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/question/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        //console.info(r);
						var type = r.object.type;
						switch(type){
							case 1: case 2: case 3: case 6: case 10:
								var w = MODx.load({
									xtype: 'usertest-answers-window',
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
							break;
							case 4:
								MODx.msg.alert('Сообщение!','Этот тип вопроса не требует задания ответов.',function() {
								  //MODx.clearCache();
								},MODx);
							break;
							case 5:
								//console.info(r);
								var extended = r.object.extended;
								var q = [];var a = [];
								if(extended){
									extended = Ext.util.JSON.decode(extended);
									q = extended.q;
									a = extended.a;
									r.object.type_point = extended.type_point;
									r.object.point = extended.point;
								}
								//q[0] = "test";
								for (var i = 0; i < 10; i++) {
								   r.object["q[" +i +"]"] = q[i];
								   r.object["a[" +i +"]"] = a[i];
								}
								
								//console.info(extended);
								var w = MODx.load({
									xtype: 'usertest-answers-type5-window',
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
							break;
							case 7: case 8: case 9: case 11:
								//console.info(r);
								var w = MODx.load({
									xtype: 'usertest-questions-with-parent-window',
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
							break;
						}
                    }, scope: this
                }
            }
        });
    },
	
	
    getFields: function () { 
		//return ['id', 'test_id', 'menuindex', 'type', 'category_id', 'category_name', 'question', 'type_file', 'file', 'extended', 'max_point', 'actions'];
		return ['id', 'type', 'category_id', 'category_name', 'question', 'type_file', 'file', 'extended', 'max_point', 'actions'];
    },

    getColumns: function (config) {
        var Columns1 =[{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
			header: _('usertest_question_type'),
            dataIndex: 'type',
            sortable: true,
            width: 150,
			renderer: UserTest.utils.question_type,
        }, {
			header: _('usertest_category_name'),
			dataIndex: 'category_name',
			sortable: false,
			width: 100,
		},{
            header: _('usertest_question'),
            dataIndex: 'question',
            sortable: false,
            width: 300,
        }, {
			header: _('usertest_type_file'),
            dataIndex: 'type_file',
            sortable: false,
            width: 100,
			renderer: UserTest.utils.file_type,
        }, {
			header: _('usertest_file'),
            dataIndex: 'file',
            sortable: false,
            width: 150,
        }, {
			header: _('usertest_max_point'),
            dataIndex: 'max_point',
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
		return Columns1;
    },

	insertQuestion: function (btn, e) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
		var test_id = this.config.test_id;
		var test_question_link_id = this.config.test_question_link_id;
        MODx.msg.confirm({
            title: _('usertest_question_select'),
            text: _('usertest_question_select_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/question/insert',
                ids: Ext.util.JSON.encode(ids),
				test_id: test_id,
            },
            listeners: {
                success: {
                    fn: function (param) {
						test_question_link_ext = Ext.getCmp(test_question_link_id);
						test_question_link_ext.refresh();
                    }, scope: this
                }
            }
        });
		return true;
    },
	
	copyQuestion: function (btn, e) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
		var test_id = this.config.test_id;
		var test_question_link_id = this.config.test_question_link_id;
        MODx.msg.confirm({
            title: _('usertest_question_copy'),
            text: _('usertest_question_copy_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/question/copy',
                ids: Ext.util.JSON.encode(ids),
				test_id: test_id,
            },
            listeners: {
                success: {
                    fn: function (param) {
						test_question_link_ext = Ext.getCmp(test_question_link_id);
						test_question_link_ext.refresh();
                        this._doSearch(); 
                    }, scope: this
                }
            }
        });
		return true;
    },
	
    getTopBar: function (config) {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_question_create'),
            handler: this.createItem,
            scope: this
        }, '->', {
			xtype: 'usertest-combo-question-type',
			id: config.id + '-search-field-type',
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
			xtype: 'textfield',
			width: 100,
			id: config.id + '-search-q_ids',
			emptyText: _('usertest_question_search_ids'),
			listeners: {
				'change': {
					fn: function() { 
						this._doSearch(); 
					},scope:this}
			}
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
        q_ids = Ext.getCmp(this.config.id + '-search-q_ids');
		if(q_ids){
			this.getStore().baseParams.q_ids = q_ids.getValue();
		}
		type = Ext.getCmp(this.config.id + '-search-field-type');
		if(type){
			this.getStore().baseParams.type = type.getValue();
		}
		category = Ext.getCmp(this.config.id + '-search-field-category');
		if(category){
			this.getStore().baseParams.category = category.getValue();
		}
		this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        q_ids = Ext.getCmp(this.config.id + '-search-q_ids');
		if(q_ids){
			this.getStore().baseParams.q_ids = "";
		}
		type = Ext.getCmp(this.config.id + '-search-field-type');
		if(type){
			this.getStore().baseParams.type = "";
		}
		category = Ext.getCmp(this.config.id + '-search-field-category');
		if(category){
			this.getStore().baseParams.category = "";
		}
		this.getBottomToolbar().changePage(1);
    },
});
Ext.reg('usertest-grid-select-questions', UserTest.grid.SelectQuestions);
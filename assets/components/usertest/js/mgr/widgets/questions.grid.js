UserTest.grid.Questions = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-questions';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/question/getlist',
			//test_id: config.test_id
        },
		save_action: 'mgr/question/autosave',
		autosave: true,
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
    UserTest.grid.Questions.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Questions, MODx.grid.Grid, {
    windows: {},
	
	/* _initDD: function(grid) {
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
							action: 'mgr/question/sort'
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
	}, */
	
    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();

        var row = grid.getStore().getAt(rowIndex);
        var menu = UserTest.utils.getMenu(row.data['actions'], this, ids);

        this.addContextMenuItem(menu);
    },

    createItem: function (btn, e) {
        //test_id = this.config.test_id;
		//name = this.config.name;
		use_category = this.config.use_category;
		var w = MODx.load({
            xtype: 'usertest-question-window-create',
            id: Ext.id(),
			use_category:use_category,
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
        //w.setValues({test_id: test_id,name: name,type: 1,type_file: 0});
		w.setValues({type: 1,type_file: 0,validate: DefaultQuestionsValidate});
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
                action: 'mgr/question/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w1 = Ext.getCmp('usertest-test_question_link');
						if (w1) {w1.hide().getEl().remove();}
						r.object.question_id = r.object.id;
						r.object.test_id = "";
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
                        w.setValues({question_id:r.object.id});
                        w.show(e.target);
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

	copyQuestion: function (btn, e) {
        var ids = this._getSelectedIds();
        if (!ids.length) {
            return false;
        }
        MODx.msg.confirm({
            title: _('usertest_question_copy'),
            text: _('usertest_question_copy_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/question/copy',
                ids: Ext.util.JSON.encode(ids),
            },
            listeners: {
                success: {
                    fn: function (param) {
						
						q_ids = Ext.getCmp(this.config.id + '-search-q_ids');
						if(q_ids){
							//console.info(param);
							q_ids.setValue(param.object.q_copy_ids);
						}
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
Ext.reg('usertest-grid-questions', UserTest.grid.Questions);

UserTest.window.CreateQuestion = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-question-window-create';
    }
	config.use_category = true;
    Ext.applyIf(config, {
        title: _('usertest_question_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/create',
        fields:  {
			xtype: 'modx-tabs'
			//,border: true
			,activeTab: config.activeTab || 0
			,bodyStyle: { background: 'transparent'}
			,deferredRender: false
			,autoHeight: true
			,stateful: true
			,stateId: 'usertest-question-window-create'
			,stateEvents: ['tabchange']
			//,getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};}
			,items: this.getTabs(config)
		},
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateQuestion.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinQuestions == "1") {
			MODx.loadRTE(config.id + '-question');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinQuestions == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-question');
		}
    },this);
};
Ext.extend(UserTest.window.CreateQuestion, MODx.Window, {

    getTabs: function(config) {
		var tabs = [{
			title: _('usertest_test_tab_main')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			,items: this.getFields1(config)
		},{
			title: _('usertest_test_tab_add')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			,items: this.getFields2(config)
		}];
		return tabs;
	},
	getFields1: function (config) {
        var Fields1 = [{
			xtype: 'hidden',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'usertest-combo-question-type',
			fieldLabel: _('usertest_question_type'),
			//name: 'org_id',
			id: config.id + '-' + 'question_type',
			anchor: '99%'
		},{
            xtype: 'textarea',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            height: 150,
            anchor: '99%'
        }];
		return Fields1;
    },
	getFields2: function (config) {
        var Fields2 = [{
			xtype: 'category-combo',
			fieldLabel: _('usertest_category_name'),
			//name: 'org_id',
			id: config.id + '-' + 'category',
			anchor: '99%'
		},{	
			xtype: 'textfield',
			fieldLabel: _('usertest_max_point'),
			name: 'max_point',
			id: config.id + '-max_point',
			anchor: '99%',
		},{	
			xtype: 'xcheckbox',
            boxLabel: _('usertest_random_answer'),
            name: 'random_answer',
            id: config.id + '-random_answer',
		}, {
			xtype: 'xcheckbox',
            boxLabel: _('usertest_question_validate'),
            name: 'validate',
            id: config.id + '-validate',
		}, {
			xtype: 'usertest-combo-file-type',
			fieldLabel: _('usertest_type_file'),
			//name: 'org_id',
			id: config.id + '-' + 'type_file',
			anchor: '99%'
		},{
			xtype: 'modx-combo-browser',
			fieldLabel: _('usertest_file'),
			name: 'file',
			id: config.id + '-file',
			hideFiles: true,
			source: MODx.config.default_media_source,
			hideSourceCombo: true,
			anchor: '99%'
        }];
		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-question-window-create', UserTest.window.CreateQuestion);


UserTest.window.UpdateQuestion = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-question-window-update';
    }
	config.use_category = true;
    Ext.applyIf(config, {
        title: _('usertest_question_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/update',
        fields: {
			xtype: 'modx-tabs'
			//,border: true
			,activeTab: config.activeTab || 0
			,bodyStyle: { background: 'transparent'}
			,deferredRender: false
			,autoHeight: true
			,stateful: true
			,stateId: 'usertest-question-window-create'
			,stateEvents: ['tabchange']
			//,getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};}
			,items: this.getTabs(config)
		},
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateQuestion.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinQuestions == "1") {
			MODx.loadRTE(config.id + '-question');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinQuestions == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-question');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateQuestion, MODx.Window, {

	getTabs: function(config) {
		var tabs = [{
			title: _('usertest_test_tab_main')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			,items: this.getFields1(config)
		},{
			title: _('usertest_test_tab_add')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			,items: this.getFields2(config)
		}];
		return tabs;
	},
	getFields1: function (config) {
        var Fields1 = [{
			xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
			xtype: 'hidden',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'usertest-combo-question-type',
			fieldLabel: _('usertest_question_type'),
			//name: 'org_id',
			id: config.id + '-' + 'question_type',
			anchor: '99%'
		},{
            xtype: 'textarea',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            height: 150,
            anchor: '99%'
        }];
		return Fields1;
    },
	getFields2: function (config) {
        var Fields2 = [{
			xtype: 'category-combo',
			fieldLabel: _('usertest_category_name'),
			//name: 'org_id',
			id: config.id + '-' + 'category',
			anchor: '99%'
		},{	
			xtype: 'textfield',
			fieldLabel: _('usertest_max_point'),
			name: 'max_point',
			id: config.id + '-max_point',
			anchor: '99%',
		},{	
			xtype: 'xcheckbox',
            boxLabel: _('usertest_random_answer'),
            name: 'random_answer',
            id: config.id + '-random_answer',
		}, {
			xtype: 'xcheckbox',
            boxLabel: _('usertest_question_validate'),
            name: 'validate',
            id: config.id + '-validate',
		}, {
			xtype: 'usertest-combo-file-type',
			fieldLabel: _('usertest_type_file'),
			//name: 'org_id',
			id: config.id + '-' + 'type_file',
			anchor: '99%'
		},{
			xtype: 'modx-combo-browser',
			fieldLabel: _('usertest_file'),
			name: 'file',
			id: config.id + '-file',
			hideFiles: true,
			source: MODx.config.default_media_source,
			hideSourceCombo: true,
			anchor: '99%'
        }];
		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-question-window-update', UserTest.window.UpdateQuestion);

UserTest.combo.TypeQuestion = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['type', 'name']
			,data: [
				[1 , _('usertest_type_questions_radiobutton')],
				[2 , _('usertest_type_questions_checkbox')],
				[3 , _('usertest_type_questions_simple_text')],
				[4 , _('usertest_type_questions_open_question')],
				[5 , _('usertest_type_questions_comparison_simple')],
				[10 , _('usertest_type_questions_combined_radiobutton')],
				[6 , _('usertest_type_questions_combined_option')],
				[7 , _('usertest_type_questions_table_checkbox')],
				[8 , _('usertest_type_questions_table_input_text')],
				//[11 , _('usertest_type_questions_table_procent')],
				[9 , _('usertest_type_questions_select_in_text')],
				[12 , _('usertest_type_questions_opros_san')],
			]
		})
		//,emptyText: _('ms2_combo_select')
		,displayField: 'name'
		,valueField: 'type'
		,hiddenName: 'type'
		,mode: 'local'
		,triggerAction: 'all'
		,editable: false
		,selectOnFocus: false
		,preventRender: true
		,forceSelection: true
		,enableKeyEvents: true
	});
	UserTest.combo.TypeQuestion.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.TypeQuestion,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-question-type',UserTest.combo.TypeQuestion);

UserTest.combo.TypeFile = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['type_file', 'name']
			,data: [
				[0 , _('usertest_type_file_no_file')],
				[1 , _('usertest_type_file_picture')],
				[2 , _('usertest_type_file_video')],
				[3 , _('usertest_type_file_audio')]
			]
		})
		//,emptyText: _('ms2_combo_select')
		,displayField: 'name'
		,valueField: 'type_file'
		,hiddenName: 'type_file'
		,mode: 'local'
		,triggerAction: 'all'
		,editable: false
		,selectOnFocus: false
		,preventRender: true
		,forceSelection: true
		,enableKeyEvents: true
	});
	UserTest.combo.TypeFile.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.TypeFile,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-file-type',UserTest.combo.TypeFile);

UserTest.window.CloneQuestion = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-question-window-clone';
    }
    Ext.applyIf(config, {
        title: _('usertest_question_clone'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/clone',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CloneQuestion.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.CloneQuestion, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'displayfield',
			fieldLabel: _('usertest_test'),
			name: 'name',
			id: config.id + '-name',
			anchor: '99%',
			allowBlank: false,
		}, {
			xtype: 'question-combo',
			fieldLabel: _('usertest_question'),
			//name: 'org_id',
			id: config.id + '-' + 'question',
			anchor: '99%'
        }];
    },
//['id', 'test_id', 'menuindex', 'type', 'question', 'type_file', 'file', 'extended', 'actions'];
    loadDropZones: function () {
    }

});
Ext.reg('usertest-question-window-clone', UserTest.window.CloneQuestion);

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
Ext.reg('question-combo',UserTest.combo.Question);

UserTest.combo.Category = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/category/getlist',

        },
		hideTrigger: false,
		fields: ['id' , 'name'],
		displayField: 'name',
		valueField: 'id',
		hiddenName:'category_id',
		hiddenValue: '',
    });
    UserTest.combo.Category.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Category ,UserTest.combo.Dadata);
Ext.reg('category-combo',UserTest.combo.Category);
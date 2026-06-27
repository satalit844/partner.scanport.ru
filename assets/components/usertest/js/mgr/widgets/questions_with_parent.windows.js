UserTest.window.QuestionsWithParent = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-questions-with-parent-window';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_questions'),
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
					xtype: 'usertest-grid-questions-with-parent',
					id: config.id + '-grid-questions-with-parent',
					cls: 'main-wrapper',
					//test_id: config.record.object.test_id,
					parent: config.record.object.id,
					use_category: config.record.object.use_category
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
    UserTest.window.QuestionsWithParent.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.QuestionsWithParent, MODx.Window, {});
Ext.reg('usertest-questions-with-parent-window', UserTest.window.QuestionsWithParent);

UserTest.grid.QuestionsWithParent = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-questions-with-parent';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/question/getlist',
			//test_id: config.test_id,
			parent: config.parent,
        },
		save_action: 'mgr/question/autosave',
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
    UserTest.grid.QuestionsWithParent.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.QuestionsWithParent, MODx.grid.Grid, {
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
	},
	
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
		parent = this.config.parent;
		var w = MODx.load({
            xtype: 'usertest-question-with-parent-window-create',
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
        w.setValues({type: 1,type_file: 0,parent:parent,question_type:1});
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
                            xtype: 'usertest-question-with-parent-window-update',
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
						if(type < 4 || type == 6){
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
						}else{
							switch(type){
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
								case 7: case 8: case 9:
									//console.info(r);
									var w = MODx.load({
										xtype: 'usertest-questions-window-with-parent',
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
						}
                    }, scope: this
                }
            }
        });
    },
	
    getFields: function () { 
		return ['id', 'parent', 'type', 'category_id', 'category_name', 'question', 'type_file', 'file', 'extended', 'actions'];
    },

    getColumns: function (config) {
        var Columns1 =[{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
			header: _('usertest_parent'),
            dataIndex: 'parent',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_menuindex'),
            dataIndex: 'menuindex',
            sortable: false,
            width: 70,
			editor: {xtype: 'textfield'},
        }, {
            header: _('usertest_question'),
            dataIndex: 'question',
            sortable: false,
            width: 300,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 100,
            id: 'actions'
        }];
		if(config.use_category){
			/* Columns1.splice(4, 0, {
					header: _('usertest_category_name'),
					dataIndex: 'category_name',
					sortable: false,
					width: 100,
				}); */
		}
		return Columns1;
    },

    getTopBar: function () {
        return [{
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_question_create'),
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
Ext.reg('usertest-grid-questions-with-parent', UserTest.grid.QuestionsWithParent);

UserTest.window.CreateQuestionWithParent = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-question-with-parent-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_question_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateQuestionWithParent.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinChildQuestions == "1") {
			MODx.loadRTE(config.id + '-question');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinChildQuestions == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-question');
		}
    },this);
};
Ext.extend(UserTest.window.CreateQuestionWithParent, MODx.Window, {

    getFields: function (config) {
        var Fields1 = [{
			xtype: 'hidden',
            fieldLabel: _('usertest_parent'),
            name: 'parent',
            id: config.id + '-parent',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'hidden',
            fieldLabel: _('usertest_question_type'),
            name: 'question_type',
            id: config.id + '-question_type',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            height: 150,
            anchor: '99%'
        }];
		if(config.use_category){
			/* Fields1.splice(3, 0, {
					xtype: 'category-combo',
					fieldLabel: _('usertest_category_name'),
					//name: 'org_id',
					id: config.id + '-' + 'category',
					anchor: '99%'
				}); */
		}
		return Fields1;
    },
//['id', 'test_id', 'menuindex', 'type', 'question', 'type_file', 'file', 'extended', 'actions'];
    loadDropZones: function () {
    }

});
Ext.reg('usertest-question-with-parent-window-create', UserTest.window.CreateQuestionWithParent);


UserTest.window.UpdateQuestionWithParent = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-question-with-parent-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_question_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateQuestionWithParent.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinChildQuestions == "1") {
			MODx.loadRTE(config.id + '-question');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinChildQuestions == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-question');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateQuestionWithParent, MODx.Window, {

    getFields: function (config) {
        var Fields2 = [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
			xtype: 'hidden',
            fieldLabel: _('usertest_parent'),
            name: 'parent',
            id: config.id + '-parent',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'hidden',
            fieldLabel: _('usertest_question_type'),
            name: 'question_type',
            id: config.id + '-question_type',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            height: 150,
            anchor: '99%'
        }];
		if(config.use_category){
			/* Fields2.splice(4, 0, {
					xtype: 'category-combo',
					fieldLabel: _('usertest_category_name'),
					//name: 'org_id',
					id: config.id + '-' + 'category',
					anchor: '99%'
				}); */
		}
		return Fields2;
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-question-with-parent-window-update', UserTest.window.UpdateQuestionWithParent);

UserTest.combo.TypeQuestionWithParent = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['type', 'name']
			,data: [
				[1 , _('usertest_type_questions_radiobutton')],
				[2 , _('usertest_type_questions_checkbox')],
				[4 , _('usertest_type_questions_open_question')],
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
	UserTest.combo.TypeQuestionWithParent.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.TypeQuestionWithParent,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-question-with-parent-type',UserTest.combo.TypeQuestionWithParent);

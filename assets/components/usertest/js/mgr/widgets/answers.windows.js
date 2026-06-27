UserTest.window.Answers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-answers-window';
    }
	//console.info(config.record.object);
    Ext.applyIf(config, {
        title: _('usertest_answers'),
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
				xtype: 'displayfield',
				fieldLabel: _('usertest_question'),
				name: 'question',
				id: config.id + '-question',
				anchor: '99%',
				allowBlank: false,
			}, {
				//title: _('usertest_test_questions'),
				layout: 'anchor',
				items: [{
					xtype: 'usertest-grid-answers',
					cls: 'main-wrapper',
					id: config.id + '-grid-answers',
					question_id: config.record.object.id,
					question: config.record.object.question
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
    UserTest.window.Answers.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.Answers, MODx.Window, {});
Ext.reg('usertest-answers-window', UserTest.window.Answers);

UserTest.grid.Answers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-answers';
    }
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/answer/getlist',
			question_id: config.question_id
        },
		save_action: 'mgr/answer/autosave',
		autosave: true,
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
    UserTest.grid.Answers.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.Answers, MODx.grid.Grid, {
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
							action: 'mgr/answer/sort'
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
        question_id = this.config.question_id;
		question = this.config.question;
		//console.info(this.config);
		var w = Ext.getCmp('usertest-answer-window-create');
		if (w) {w.hide().getEl().remove();}
		var w = MODx.load({
            xtype: 'usertest-answer-window-create',
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
        w.setValues({question_id: question_id, question: question, type_file: 0});
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
                action: 'mgr/answer/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
						var w = Ext.getCmp('usertest-answer-window-update');
						if (w) {w.hide().getEl().remove();}
                        var w = MODx.load({
                            xtype: 'usertest-answer-window-update',
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
						r.object.question = this.config.question;
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
                ? _('usertest_answers_remove')
                : _('usertest_answer_remove'),
            text: ids.length > 1
                ? _('usertest_answers_remove_confirm')
                : _('usertest_answer_remove_confirm'),
            url: this.config.url,
            params: {
                action: 'mgr/answer/remove',
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
        return ['id', 'question_id', 'menuindex', 'answer', 'type_file', 'file', 'point', 'right', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_item_id'),
            dataIndex: 'id',
            sortable: true,
            width: 70
        }, {
            header: _('usertest_question_id'),
            dataIndex: 'question_id',
            sortable: true,
            width: 70,
        }, {
			header: _('usertest_menuindex'),
            dataIndex: 'menuindex',
            sortable: false,
            width: 70,
			editor: {xtype: 'textfield'},
        }, {
            header: _('usertest_answer'),
            dataIndex: 'answer',
            sortable: false,
            width: 200,
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
			header: _('usertest_point'),
            dataIndex: 'point',
            sortable: false,
            width: 100,
        }, {
			header: _('usertest_answer_right'),
            dataIndex: 'right',
            renderer: UserTest.utils.renderBoolean,
            sortable: true,
            width: 70,
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
            text: '<i class="icon icon-plus"></i>&nbsp;' + _('usertest_answer_create'),
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
Ext.reg('usertest-grid-answers', UserTest.grid.Answers);

UserTest.window.CreateAnswer = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-answer-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_answer_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/answer/create',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateAnswer.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinAnswers == "1") {
			MODx.loadRTE(config.id + '-answer');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinAnswers == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-answer');
		}
    },this);
};
Ext.extend(UserTest.window.CreateAnswer, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            fieldLabel: _('usertest_question_id'),
            name: 'question_id',
            id: config.id + '-question_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'displayfield',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_answer'),
            name: 'answer',
            id: config.id + '-answer',
            anchor: '99%'
		},{	
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
		}, {
            xtype: 'textfield',
            fieldLabel: _('usertest_point'),
            name: 'point',
            id: config.id + '-point',
            anchor: '99%'
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_answer_right'),
            name: 'right',
            id: config.id + '-right',
            checked: false,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-answer-window-create', UserTest.window.CreateAnswer);


UserTest.window.UpdateAnswer = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-answer-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_answer_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/answer/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateAnswer.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinAnswers == "1") {
			MODx.loadRTE(config.id + '-answer');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinAnswers == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-answer');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateAnswer, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            fieldLabel: _('usertest_question_id'),
			ReadOnly: true,
            name: 'question_id',
            id: config.id + '-question_id',
            anchor: '99%',
            allowBlank: false,
        }, {
			xtype: 'displayfield',
            fieldLabel: _('usertest_question'),
			ReadOnly: true,
            name: 'question',
            id: config.id + '-question',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_answer'),
            name: 'answer',
            id: config.id + '-answer',
            anchor: '99%'
		},{	
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
		}, {
            xtype: 'textfield',
            fieldLabel: _('usertest_point'),
            name: 'point',
            id: config.id + '-point',
            anchor: '99%'
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_answer_right'),
            name: 'right',
            id: config.id + '-right',
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-answer-window-update', UserTest.window.UpdateAnswer);

UserTest.window.AnswerType5 = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-answers-type5-window';
    }
    Ext.applyIf(config, {
        title: _('usertest_question_update'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/question/update_type5',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.AnswerType5.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.AnswerType5, MODx.Window, {

    getFields: function (config) {
        //console.info(config);
		var q1 = [];var a1 = [];
		for (var i = 0; i < 10; i++) {
		   q1[i] = {xtype: 'textfield', name: 'q[' + i +']', id: config.id + '-q' + i, anchor: '99%'};
		   a1[i] = {xtype: 'textfield', name: 'a[' + i +']', id: config.id + '-a' + i, anchor: '99%'};
		}
		//console.info(q1);
		return [{
            xtype: 'hidden',
            fieldLabel: _('usertest_question_id'),
            name: 'id',
            id: config.id + '-id',
            anchor: '99%',
            allowBlank: false,
        }, {
            xtype: 'hidden',
            fieldLabel: _('usertest_test_id'),
            name: 'test_id',
            id: config.id + '-test_id',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'numberfield',
            fieldLabel: _('usertest_point'),
            name: 'point',
            id: config.id + '-point',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'radiogroup',
			id: config.id + '-radiogroup',
			labelWidth: 60,
			fieldLabel: _('usertest_answer_type5_point'),
			name: 'type_point',
			items: [
				{
					name: 'type_point',
					inputValue: '0',
					boxLabel: _('usertest_answer_type5_type_point1'),
				},
				{
					name: 'type_point',
					inputValue: '1',
					boxLabel: _('usertest_answer_type5_type_point2'),
				}
			]
		},{
			layout:'column',
			border: false,
			fieldLabel: _('usertest_answer_type5'),
			anchor: '100%',
			items: [{
				columnWidth: .5,
				layout: 'form',
				defaults: { msgTarget: 'under' },
				border:false,
				items: q1
			},{
				columnWidth: .5,
				layout: 'form',
				defaults: { msgTarget: 'under' },
				border:false,
				items: a1
			}]
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-answers-type5-window', UserTest.window.AnswerType5);
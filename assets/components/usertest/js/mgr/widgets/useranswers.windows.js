UserTest.window.UserAnswers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-useranswers-window';
    }
    //console.info(config.record.object);
    Ext.applyIf(config, {
        title: _('usertest_answers'),
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
                fieldLabel: _('usertest_test_id'),
                name: 'test_id',
                id: config.id + '-test_id',
                anchor: '99%',
                allowBlank: false,
            }, {
                //title: _('usertest_test_questions'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-useranswers',
                    cls: 'main-wrapper',
                    result_id: config.record.object.id
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
Ext.extend(UserTest.window.UserAnswers, MODx.Window, {});
Ext.reg('usertest-useranswers-window', UserTest.window.UserAnswers);

UserTest.grid.UserAnswers = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grid-useranswers';
    }
    //console.info(config);
    Ext.applyIf(config, {
        url: UserTest.config.connector_url,
        fields: this.getFields(config),
        columns: this.getColumns(config),
        tbar: this.getTopBar(config),
        sm: new Ext.grid.CheckboxSelectionModel(),
        baseParams: {
            action: 'mgr/useranswer/getlist',
            result_id: config.result_id
        },
        pageSize: 10,
        listeners: {
            /* rowDblClick: function (grid, rowIndex, e) {
                var row = grid.store.getAt(rowIndex);
                this.updateItem(grid, e, row);
            } */
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
    UserTest.grid.UserAnswers.superclass.constructor.call(this, config);

    // Clear selection on grid refresh
    this.store.on('load', function () {
        if (this._getSelectedIds().length) {
            this.getSelectionModel().clearSelections();
        }
    }, this);
};
Ext.extend(UserTest.grid.UserAnswers, MODx.grid.Grid, {
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
        var question = this.menu.record.user_question;
        MODx.Ajax.request({
            url: this.config.url,
            params: {
                action: 'mgr/useranswer/get',
                id: id
            },
            listeners: {
                success: {
                    fn: function (r) {
                        var w = MODx.load({
                            xtype: 'usertest-useranswer-window-update',
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
                        r.object.question = question;
                        //console.info(this.config,r.object);
                        w.setValues(r.object);
                        w.show(e.target);
                    }, scope: this
                }
            }
        });
    },
    
    getFields: function () {
        return ['id', 'result_id', 'question_id', 'user_question', 'user_menuindex', 'answer_id', 'answer_ids', 'answer', 'point', 'user_question_max_point', 'comment', 'time', 'actions'];
    },

    getColumns: function () {
        return [{
            header: _('usertest_question_id'),
            dataIndex: 'question_id',
            sortable: true,
            width: 200,
        }, {
            header: _('usertest_menuindex'),
            dataIndex: 'user_menuindex',
            sortable: false,
            width: 70,
        }, {
            header: _('usertest_question'),
            dataIndex: 'user_question',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_answer'),
            dataIndex: 'answer',
            sortable: false,
            width: 200,
        }, {
            header: _('usertest_point'),
            dataIndex: 'point',
            sortable: false,
            width: 100,
        }, {
            header: _('usertest_max_point'),
            dataIndex: 'user_question_max_point',
            sortable: false,
            width: 100,
        }, {
            header: _('usertest_comment'),
            dataIndex: 'comment',
            sortable: true,
            width: 150,
        }, {
            header: _('usertest_answer_time'),
            dataIndex: 'time',
            sortable: true,
            width: 80,
        }, {
            header: _('usertest_grid_actions'),
            dataIndex: 'actions',
            renderer: UserTest.utils.renderActions,
            sortable: false,
            width: 50,
            id: 'actions'
        }];
    },

    getTopBar: function () {
        return ['->', {
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
Ext.reg('usertest-grid-useranswers', UserTest.grid.UserAnswers);

UserTest.window.UpdateUserAnswer = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-useranswer-window-update';
    }
    
    Ext.applyIf(config, {
        title: _('usertest_useranswer_update'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/useranswer/update',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.UpdateUserAnswer.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.UpdateUserAnswer, MODx.Window, {

    getFields: function (config) {
        //console.info(config);
        return [{
            xtype: 'hidden',
            name: 'id',
            id: config.id + '-id',
        }, {
            xtype: 'hidden',
            name: 'result_id',
            id: config.id + '-result_id',
        }, {
            xtype: 'textarea',
            fieldLabel: _('usertest_question'),
            name: 'question',
            id: config.id + '-question',
            height: 150,
            readOnly: true,
            anchor: '99%'
        },{	
            xtype: 'textarea',
            fieldLabel: _('usertest_answer'),
            name: 'answer',
            id: config.id + '-answer',
            height: 150,
            readOnly: true,
            anchor: '99%'
        },{	
            xtype: 'textfield',
            fieldLabel: _('usertest_point'),
            name: 'point',
            id: config.id + '-point',
            anchor: '99%',
        },{	
            xtype: 'textarea',
            fieldLabel: _('usertest_comment'),
            name: 'comment',
            id: config.id + '-comment',
            height: 150,
            anchor: '99%'
        },{	
            xtype: 'textfield',
            fieldLabel: _('usertest_answer_time'),
            name: 'time',
            id: config.id + '-time',
            anchor: '99%'
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-useranswer-window-update', UserTest.window.UpdateUserAnswer);
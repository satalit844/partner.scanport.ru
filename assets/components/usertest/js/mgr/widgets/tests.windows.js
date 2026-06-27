UserTest.window.CreateTest = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test-window-create';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_create'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test/create',
        //fields: this.getFields(config),
		fields: {
			xtype: 'modx-tabs'
			//,border: true
			,activeTab: config.activeTab || 0
			,bodyStyle: { background: 'transparent'}
			,deferredRender: false
			,autoHeight: true
			,stateful: true
			,stateId: 'usertest-test-window-create'
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
    UserTest.window.CreateTest.superclass.constructor.call(this, config);
	
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinTest == "1") {
			MODx.loadRTE(config.id + '-description');
			MODx.loadRTE(config.id + '-customer');
			MODx.loadRTE(config.id + '-appeal');
			MODx.loadRTE(config.id + '-instruction');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinTest == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-description');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-customer');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-appeal');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-instruction');
		}
    },this);
};
Ext.extend(UserTest.window.CreateTest, MODx.Window, {

	getTabs: function(config) {
		var tabs = [{
			title: _('usertest_test_tab_main')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			//,defaults: {msgTarget: 'under',border: false}
			,items: this.getOrderFields1(config)
		},{
			title: _('usertest_test_tab_add')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			//,defaults: {msgTarget: 'under',border: false}
			,items: this.getOrderFields2(config)
		}];
		return tabs;
	},
	getOrderFields2: function (config) {
        return [{
            xtype: 'textarea',
            fieldLabel: _('usertest_test_customer'),
            name: 'customer',
            id: config.id + '-customer',
            height: 150,
            anchor: '99%'
        }, {
			xtype: 'textarea',
            fieldLabel: _('usertest_test_appeal'),
            name: 'appeal',
            id: config.id + '-appeal',
            height: 150,
            anchor: '99%'
		}, {
			xtype: 'textarea',
            fieldLabel: _('usertest_test_instruction'),
            name: 'instruction',
            id: config.id + '-instruction',
            height: 150,
            anchor: '99%'
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_test_use_block_q_number'),
            name: 'use_block_q_number',
            id: config.id + '-use_block_q_number',
            checked: true,
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_test_ask_user_data'),
            name: 'ask_user_data',
            id: config.id + '-ask_user_data',
            checked: false,
		}, {
			xtype : 'xdatetime'
			,fieldLabel : _('usertest_test_pub_date')
			,name: 'pub_date'
			,id: config.id + '-pub_date'
			,allowBlank : true
			,dateWidth : 120
			,timeWidth : 120	
		}, {
			xtype : 'xdatetime'
			,fieldLabel : _('usertest_test_unpub_date')
			,name: 'unpub_date'
			,id: config.id + '-unpub_date'
			,allowBlank : true
			,dateWidth : 120
			,timeWidth : 120
        }];
    },
    getOrderFields1: function (config) {
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
        }, {
			xtype: 'usertest-combo-test-type',
            fieldLabel: _('usertest_test_type'),
            //name: 'type',
            id: config.id + '-type',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'usertest-combo-test-type2',
            fieldLabel: _('usertest_test_type2'),
            //name: 'type',
            id: config.id + '-test_type',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_test_answer'),
            name: 'count_test_answer',
            id: config.id + '-count_test_answer',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_questions'),
            name: 'count_questions',
            id: config.id + '-count_questions',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_questions_on_page'),
            name: 'count_questions_on_page',
            id: config.id + '-count_questions_on_page',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_time_test'),
            name: 'time_test',
            id: config.id + '-time_test',
            anchor: '99%',
			allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_use_category'),
            name: 'use_category',
            id: config.id + '-use_category',
            checked: false,
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_item_active'),
            name: 'active',
            id: config.id + '-active',
            checked: true,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-test-window-create', UserTest.window.CreateTest);


UserTest.window.UpdateTest = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-test-window-update';
    }
    Ext.applyIf(config, {
        title: _('usertest_test_update'),
        width: 900,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/test/update',
        //fields: this.getFields(config),
		fields: {
			xtype: 'modx-tabs'
			,border: true
			,activeTab: config.activeTab || 0
			,bodyStyle: { background: 'transparent'}
			,deferredRender: false
			,autoHeight: true
			,stateful: true
			,stateId: 'usertest-test-window-update'
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
    UserTest.window.UpdateTest.superclass.constructor.call(this, config);
	this.on('activate',function(w,e) {
        if (MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinTest == "1") {
			MODx.loadRTE(config.id + '-description');
			MODx.loadRTE(config.id + '-customer');
			MODx.loadRTE(config.id + '-appeal');
			MODx.loadRTE(config.id + '-instruction');
		}
    },this);
    this.on('deactivate',function(w,e) {
        if (typeof(tinyMCE) != "undefined" && MODx.loadRTE && UserTest.config.useRTE == "1" && UserTest.config.useRTEinTest == "1"){
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-description');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-customer');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-appeal');
			tinyMCE.execCommand('mceRemoveControl',true,config.id + '-instruction');
		}
    },this);
};
Ext.extend(UserTest.window.UpdateTest, MODx.Window, {

getTabs: function(config) {
		var tabs = [{
			title: _('usertest_test_tab_main')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			//,defaults: {msgTarget: 'under',border: false}
			,items: this.getOrderFields1(config)
		},{
			title: _('usertest_test_tab_add')
			,layout: 'form'
			,hideMode: 'offsets'
			,bodyStyle: 'padding:5px 0;'
			//,defaults: {msgTarget: 'under',border: false}
			,items: this.getOrderFields2(config)
		}];
		return tabs;
	},
	getOrderFields2: function (config) {
        return [{
            xtype: 'textarea',
            fieldLabel: _('usertest_test_customer'),
            name: 'customer',
            id: config.id + '-customer',
            height: 150,
            anchor: '99%'
        }, {
			xtype: 'textarea',
            fieldLabel: _('usertest_test_appeal'),
            name: 'appeal',
            id: config.id + '-appeal',
            height: 150,
            anchor: '99%'
		}, {
			xtype: 'textarea',
            fieldLabel: _('usertest_test_instruction'),
            name: 'instruction',
            id: config.id + '-instruction',
            height: 150,
            anchor: '99%'
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_test_use_block_q_number'),
            name: 'use_block_q_number',
            id: config.id + '-use_block_q_number',
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_test_ask_user_data'),
            name: 'ask_user_data',
            id: config.id + '-ask_user_data',
            checked: false,
		}, {
			xtype : 'xdatetime'
			,fieldLabel : _('usertest_test_pub_date')
			,name: 'pub_date'
			,id: config.id + '-pub_date'
			,allowBlank : true
			,dateWidth : 120
			,timeWidth : 120	
		}, {
			xtype : 'xdatetime'
			,fieldLabel : _('usertest_test_unpub_date')
			,name: 'unpub_date'
			,id: config.id + '-unpub_date'
			,allowBlank : true
			,dateWidth : 120
			,timeWidth : 120
        }];
    },
    getOrderFields1: function (config) {
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
        }, {
			xtype: 'usertest-combo-test-type',
            fieldLabel: _('usertest_test_type'),
            //name: 'type',
            id: config.id + '-type',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'usertest-combo-test-type2',
            fieldLabel: _('usertest_test_type2'),
            //name: 'type',
            id: config.id + '-test_type',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_test_answer'),
            name: 'count_test_answer',
            id: config.id + '-count_test_answer',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_questions'),
            name: 'count_questions',
            id: config.id + '-count_questions',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_questions_on_page'),
            name: 'count_questions_on_page',
            id: config.id + '-count_questions_on_page',
            anchor: '99%',
			allowBlank: false,
        }, {
			xtype: 'textfield',
            fieldLabel: _('usertest_test_count_time_test'),
            name: 'time_test',
            id: config.id + '-time_test',
            anchor: '99%',
			allowBlank: false,
        }, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_use_category'),
            name: 'use_category',
            id: config.id + '-use_category',
		}, {
            xtype: 'xcheckbox',
            boxLabel: _('usertest_item_active'),
            name: 'active',
            id: config.id + '-active',
        }];
    },
    loadDropZones: function () {
    }

});
Ext.reg('usertest-test-window-update', UserTest.window.UpdateTest);

UserTest.window.CreateGroupTestLink = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-grouptestlink-window-create';//usertest-grouplink-window-create
    }
    Ext.applyIf(config, {
        title: _('usertest_grouplink_create'),
        width: 550,
        autoHeight: true,
        url: UserTest.config.connector_url,
        action: 'mgr/grouplink/addgroup',
        fields: this.getFields(config),
        keys: [{
            key: Ext.EventObject.ENTER, shift: true, fn: function () {
                this.submit()
            }, scope: this
        }]
    });
    UserTest.window.CreateGroupTestLink.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.window.CreateGroupTestLink, MODx.Window, {

    getFields: function (config) {
        return [{
            xtype: 'textfield',
            fieldLabel: _('usertest_test_ids'),
            name: 'test_ids',
            id: config.id + '-test_ids',
            anchor: '99%',
            allowBlank: false,
		},{
			xtype: 'group-combo',
			fieldLabel: _('usertest_group'),
			//name: 'org_id',
			id: config.id + '-' + 'group_id',
			anchor: '99%',
			allowBlank: false,
        }];
    },

    loadDropZones: function () {
    }

});
Ext.reg('usertest-grouptestlink-window-create', UserTest.window.CreateGroupTestLink);

UserTest.combo.Group = function(config) {
    config = config || {};
    Ext.applyIf(config,{
		baseParams:{
            action: 'mgr/group/getlist',

        },
		hideTrigger: false,
		fields: ['id' , 'name'],
		displayField: 'name',
		valueField: 'id',
		hiddenName:'group_id',
		hiddenValue: '',
    });
    UserTest.combo.Group.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.Group ,UserTest.combo.Dadata);
Ext.reg('group-combo',UserTest.combo.Group);

UserTest.combo.TestType = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['type', 'name']
			,data: [
				[1 , "Автоматически"],
				[2 , "Проверяется преподователем"]
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
	UserTest.combo.TestType.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.TestType,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-test-type',UserTest.combo.TestType);

UserTest.combo.TestType2 = function(config) {
	config = config || {};
	Ext.applyIf(config,{
		store: new Ext.data.SimpleStore({
			fields: ['test_type', 'name']
			,data: [
				[1 , "Стандарт"],
				[2 , "Опросник САН"]
			]
		})
		//,emptyText: _('ms2_combo_select')
		,displayField: 'name'
		,valueField: 'test_type'
		,hiddenName: 'test_type'
		,mode: 'local'
		,triggerAction: 'all'
		,editable: false
		,selectOnFocus: false
		,preventRender: true
		,forceSelection: true
		,enableKeyEvents: true
	});
	UserTest.combo.TestType2.superclass.constructor.call(this,config);
};
Ext.extend(UserTest.combo.TestType2,MODx.combo.ComboBox, {});
Ext.reg('usertest-combo-test-type2',UserTest.combo.TestType2);
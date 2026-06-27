UserTest.panel.Export = function (config) {
    config = config || {};
    if (!config.id) {
        config.id = 'usertest-export-panel';
    }
    Ext.applyIf(config, {
        baseCls: 'modx-formpanel',
        url: UserTest.config.connector_url,
        config: config,
        layout: 'form',
		baseParams: {
			action: 'mgr/prices/export'
		},

		items: [{
			xtype: 'textfield',
			name: 'test',
			width: 220,
			id: config.id + '-field-test',
			fieldLabel: _('usertest_test_export'),
            description: _('usertest_test_export_desc')
		},{
            xtype: 'button',
            text: _('usertest_export'),
            //fieldLabel: _('usertest_export'),
            name: 'start-export',
            id: config.id + '-start-export',
            cls: 'primary-button',
			listeners: {
				click: {fn: this._startexport, scope: this}
			}
		},{
			xtype: 'modx-combo-browser',
			name: 'excel_file',
			width: 220,
			id: config.id + '-excel_file',
			fieldLabel: _('usertest_test_export_file'),
            description: _('usertest_test_export_file_desc')
		},{
            xtype: 'button',
            text: _('usertest_import'),
            //fieldLabel: _('usertest_export'),
            name: 'start-import',
            id: config.id + '-start-import',
            cls: 'primary-button',
			listeners: {
				click: {fn: this._startimport, scope: this}
			}
        }]
    });
    UserTest.panel.Export.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.panel.Export, MODx.FormPanel, {
	
	_startexport: function() {
        test = Ext.getCmp(this.config.id + '-field-test').getValue();
		url ='/assets/components/usertest/export_test.php?test=' + test;
		window.open(url, '_blank');
    },
	
	_startimport: function () {
		var excel_file = Ext.getCmp(this.config.id + '-excel_file').getValue();
		
		if(excel_file){
			var topic = '/usertest/';
			var register = 'mgr';
			this.console1 = MODx.load({
			   xtype: 'modx-console'
			   ,register: register
			   ,topic: topic
			   ,show_filename: 0
			   ,listeners: {
				 'shutdown': {fn:function() {
					 //Ext.getCmp('usertest-grid-invites').refresh();
				 },scope:this}
			   }
			});
			this.console1.show(Ext.getBody());
			
			
			
			MODx.Ajax.request({
				url: this.config.url
				,params: {
					action: 'mgr/import/import'
					,register: register
					,topic: topic
					,excel_file: excel_file
				}
				,listeners: {
					'success':{fn:function() {
						this.console1.fireEvent('complete');
					},scope:this}
				}
			});
		}else{
			MODx.msg.alert('Warning!',_('usertest_test_empty_export_file'),function() {
			  //MODx.clearCache();
			},MODx);
		}
    },
});
Ext.reg('usertest-export-panel', UserTest.panel.Export);


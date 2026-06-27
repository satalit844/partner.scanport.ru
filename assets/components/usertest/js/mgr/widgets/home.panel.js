UserTest.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        /*
         stateful: true,
         stateId: 'usertest-panel-home',
         stateEvents: ['tabchange'],
         getState:function() {return {activeTab:this.items.indexOf(this.getActiveTab())};},
         */
        hideMode: 'offsets',
        items: [{
            html: '<h2>' + _('usertest') + '</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'modx-tabs',
            defaults: {border: false, autoHeight: true},
            border: true,
            hideMode: 'offsets',
            items: [{
                title: _('usertest_tests'),
                layout: 'anchor',
                items: [{
					html: _('usertest_intro_msg'),
                    cls: 'panel-desc',
                }, {
                    xtype: 'usertest-grid-tests',
                    cls: 'main-wrapper',
                }]
			},{
                title: _('usertest_groups'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-groups',
                    cls: 'main-wrapper',
                }]
            },{
				title: _('usertest_questions'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-questions',
                    cls: 'main-wrapper',
                }]
            },{
				title: _('usertest_variantsets'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-variantsets',
                    cls: 'main-wrapper',
                }]
            },{
				title: _('usertest_category'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-categorys',
                    cls: 'main-wrapper',
                }]
            },{
                title: _('usertest_results'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-userresults',
                    cls: 'main-wrapper',
                }]
			},{
				title: _('usertest_invite'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-grid-invites',
                    cls: 'main-wrapper',
                }]
			},{
				title: _('usertest_export_import'),
                layout: 'anchor',
                items: [{
                    xtype: 'usertest-export-panel',
                    cls: 'main-wrapper',
                }]
            }
			]
        }]
    });
    UserTest.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.panel.Home, MODx.Panel);
Ext.reg('usertest-panel-home', UserTest.panel.Home);

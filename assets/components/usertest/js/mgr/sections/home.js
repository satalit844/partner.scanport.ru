UserTest.page.Home = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        components: [{
            xtype: 'usertest-panel-home',
            renderTo: 'usertest-panel-home-div'
        }]
    });
    UserTest.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(UserTest.page.Home, MODx.Component);
Ext.reg('usertest-page-home', UserTest.page.Home);
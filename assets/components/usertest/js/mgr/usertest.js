var UserTest = function (config) {
    config = config || {};
    UserTest.superclass.constructor.call(this, config);
};
Ext.extend(UserTest, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, config: {}, view: {}, utils: {}
});
Ext.reg('usertest', UserTest);

UserTest = new UserTest();
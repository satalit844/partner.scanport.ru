Training.panel.Home = function(config) {
    config = config || {};

    Ext.apply(config, {
        id: 'training-panel-home',
        baseCls: 'modx-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            html: '<h2>Курсы</h2>',
            cls: '',
            style: {margin: '15px 0'}
        }, {
            xtype: 'training-grid-courses',
            cls: 'main-wrapper',
            preventRender: true
        }, {
            html: '<h2 style="margin-top:24px;">Руководители и сотрудники</h2><div style="color:#666;line-height:1.6;margin:0 0 12px;">Здесь задаём, какие сотрудники относятся к директору. Эта связь потом используется на фронте, чтобы директор мог назначать курсы себе и своим подчинённым.</div>',
            cls: '',
            style: {margin: '15px 0 0'}
        }, {
            xtype: 'training-grid-manager-links',
            cls: 'main-wrapper',
            preventRender: true
        }]
    });

    Training.panel.Home.superclass.constructor.call(this, config);
};

Ext.extend(Training.panel.Home, MODx.Panel);
Ext.reg('training-panel-home', Training.panel.Home);

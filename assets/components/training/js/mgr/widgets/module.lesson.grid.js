Training.grid.ModuleLessons = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    this.courseId = config.courseId || Training.config.course_id;
    this.sm = new Ext.grid.CheckboxSelectionModel();

    Ext.applyIf(config, {
        id: 'training-grid-module-lessons',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/module/lessons/getlist', module_id: this.moduleId},
        sm: this.sm,
        fields: ['id','module_id','title','sort_order','videos_count','slides_count','is_default','is_active'],
        paging: true,
        remoteSort: true,
        sortBy: 'sort_order',
        sortDir: 'ASC',
        autoHeight: true,
        anchor: '100%',
        columns: [
            this.sm,
            {header: 'ID', dataIndex: 'id', width: 50},
            {header: 'Урок', dataIndex: 'title', width: 320},
            {header: 'Порядок', dataIndex: 'sort_order', width: 70},
            {header: 'Видео', dataIndex: 'videos_count', width: 70},
            {header: 'Слайды', dataIndex: 'slides_count', width: 70},
            {header: 'По умолчанию', dataIndex: 'is_default', width: 90, renderer: Training.utils.renderBoolean},
            {header: 'Активен', dataIndex: 'is_active', width: 80, renderer: Training.utils.renderBoolean}
        ],
        tbar: [{
            text: 'Добавить урок',
            handler: this.createLesson,
            scope: this
        },{
            text: 'Открыть урок',
            handler: this.openSelectedLesson,
            scope: this
        },{
            text: 'Изменить',
            handler: this.updateLesson,
            scope: this
        },{
            text: 'Удалить',
            handler: this.removeLesson,
            scope: this
        },'->',{
            text: 'Обновить',
            handler: function(){ this.refresh(); },
            scope: this
        }],
        listeners: {
            rowdblclick: {fn: function(grid, rowIndex){ var rec = grid.store.getAt(rowIndex); if (rec) { this.openLesson(rec); } }, scope: this},
            rowcontextmenu: {fn: function(grid, rowIndex, e){ var rec = grid.store.getAt(rowIndex); if (!rec) { return; } grid.getSelectionModel().selectRow(rowIndex); this.menu.record = rec; this.getMenu(); if (this.menu) { this.menu.showAt(e.getXY()); } e.stopEvent(); }, scope: this},
            cellclick: {fn: function(grid, rowIndex, colIndex){ var rec = grid.store.getAt(rowIndex); var dataIndex = grid.getColumnModel().getDataIndex(colIndex); if (!rec) { return; } if (dataIndex === 'is_active') { this.quickUpdate(rec, {is_active: rec.get('is_active') ? 0 : 1}); } else if (dataIndex === 'is_default' && !rec.get('is_default')) { this.quickUpdate(rec, {is_default: 1}); } }, scope: this}
        }
    });

    Training.grid.ModuleLessons.superclass.constructor.call(this, config);
};

Ext.extend(Training.grid.ModuleLessons, MODx.grid.Grid, {
    getSelectedRecord: function() {
        var sm = this.getSelectionModel(), selections = sm ? sm.getSelections() : [];
        return selections && selections.length ? selections[0] : (this.menu && this.menu.record ? this.menu.record : null);
    },

    openLesson: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { return false; }
        var lessonId = Training.utils.getRecordValue(rec, 'id');
        if (!lessonId) { return false; }
        window.location.href = Training.utils.buildUrl({course_id: this.courseId, module_id: this.moduleId, lesson_id: lessonId});
    },

    openSelectedLesson: function() {
        return this.openLesson();
    },

    quickUpdate: function(rec, patch) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { return false; }
        var data = Ext.apply({}, rec.data || rec);
        data.action = 'mgr/module/lesson/update';
        data.id = parseInt(data.id, 10) || 0;
        data.module_id = this.moduleId;
        data.title = data.title || 'Урок';
        data.sort_order = parseInt(data.sort_order, 10) || 1;
        data.is_default = data.is_default ? 1 : 0;
        data.is_active = data.is_active ? 1 : 0;
        Ext.apply(data, patch || {});
        MODx.Ajax.request({url: Training.config.connector_url, params: data, listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}});
        return true;
    },

    createLesson: function() {
        var w = MODx.load({
            xtype: 'training-window-module-lesson',
            title: 'Добавить урок',
            baseParams: {action: 'mgr/module/lesson/create'},
            moduleId: this.moduleId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        });
        w.setValues({module_id: this.moduleId, is_active: 1, is_default: 0});
        w.show();
    },

    updateLesson: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { MODx.msg.alert('Внимание', 'Выбери урок'); return false; }
        var w = MODx.load({
            xtype: 'training-window-module-lesson',
            title: 'Изменить урок',
            baseParams: {action: 'mgr/module/lesson/update'},
            moduleId: this.moduleId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        });
        w.setValues(rec.data || rec);
        w.show();
    },

    removeLesson: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { MODx.msg.alert('Внимание', 'Выбери урок'); return false; }
        MODx.msg.confirm({
            title: 'Удаление',
            text: 'Удалить урок со всеми видео и слайдами?',
            url: Training.config.connector_url,
            params: {action: 'mgr/module/lesson/remove', id: Training.utils.getRecordValue(rec, 'id')},
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        });
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSelectedRecord();
        if (!rec) { return false; }
        this.addContextMenuItem([{
            text: 'Перейти на урок',
            handler: function(){ this.openLesson(rec); },
            scope: this
        },{
            text: 'Изменить',
            handler: function(){ this.updateLesson(rec); },
            scope: this
        },{
            text: 'Удалить',
            handler: function(){ this.removeLesson(rec); },
            scope: this
        },'-',{
            text: Training.utils.getRecordValue(rec, 'is_active') ? 'Отключить' : 'Включить',
            handler: function(){ this.quickUpdate(rec, {is_active: Training.utils.getRecordValue(rec, 'is_active') ? 0 : 1}); },
            scope: this
        },{
            text: 'Сделать по умолчанию',
            handler: function(){ this.quickUpdate(rec, {is_default: 1}); },
            scope: this
        }]);
        return true;
    }
});
Ext.reg('training-grid-module-lessons', Training.grid.ModuleLessons);

Training.window.ModuleLesson = function(config) {
    config = config || {};
    this.moduleId = config.moduleId || Training.config.module_id;
    Ext.applyIf(config, {
        width: 700,
        autoHeight: true,
        url: Training.config.connector_url,
        fields: [
            {xtype:'hidden',name:'id'},
            {xtype:'hidden',name:'module_id',value:this.moduleId},
            {xtype:'textfield',fieldLabel:'Название урока',name:'title',anchor:'100%',allowBlank:false},
            {xtype:'numberfield',fieldLabel:'Порядок',name:'sort_order',anchor:'100%'},
            {xtype:'textarea',fieldLabel:'Описание',name:'description',anchor:'100%'},
            {xtype:'xcheckbox',boxLabel:'По умолчанию',hideLabel:true,name:'is_default',inputValue:1},
            {xtype:'xcheckbox',boxLabel:'Активен',hideLabel:true,name:'is_active',inputValue:1,checked:true}
        ]
    });
    Training.window.ModuleLesson.superclass.constructor.call(this, config);
};
Ext.extend(Training.window.ModuleLesson, MODx.Window);
Ext.reg('training-window-module-lesson', Training.window.ModuleLesson);

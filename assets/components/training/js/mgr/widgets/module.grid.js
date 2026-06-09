Training.grid.CourseModules = function(config) {
    config = config || {};
    this.courseId = config.courseId || Training.config.course_id;

    Ext.applyIf(config, {
        id: 'training-grid-course-modules',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/course/modules/getlist',
            course_id: this.courseId
        },
        fields: ['id','course_id','resource_id','title','menuindex','published','is_active','lessons_count','videos_count','slides_count'],
        paging: true,
        remoteSort: true,
        sortBy: 'menuindex',
        sortDir: 'ASC',
        autoHeight: true,
        anchor: '100%',
        columns: [
            {header: 'ID', dataIndex: 'id', width: 60},
            {header: 'Resource ID', dataIndex: 'resource_id', width: 80},
            {header: 'Модуль', dataIndex: 'title', width: 320},
            {header: 'Порядок', dataIndex: 'menuindex', width: 80},
            {header: 'Опубликован', dataIndex: 'published', width: 90, renderer: Training.utils.renderBoolean},
            {header: 'Активен', dataIndex: 'is_active', width: 90, renderer: Training.utils.renderBoolean},
            {header: 'Уроков', dataIndex: 'lessons_count', width: 70},
            {header: 'Видео', dataIndex: 'videos_count', width: 70},
            {header: 'Слайды', dataIndex: 'slides_count', width: 70}
        ],
        tbar: [{
            text: 'Назад к курсам',
            handler: function() {
                window.location.href = Training.utils.buildUrl({}, ['course_id', 'module_id', 'lesson_id']);
            }
        }, {
            text: 'Обновить',
            handler: function() { this.refresh(); },
            scope: this
        }, {
            text: 'Пересинхронизировать курс',
            handler: this.syncCourse,
            scope: this
        }],
        listeners: {
            rowdblclick: {fn: function(grid, rowIndex) {
                var rec = grid.store.getAt(rowIndex);
                if (rec) { this.openModule(rec); }
            }, scope: this},
            rowcontextmenu: {fn: function(grid, rowIndex, e) {
                var rec = grid.store.getAt(rowIndex);
                if (!rec) { return; }
                grid.getSelectionModel().selectRow(rowIndex);
                this.menu.record = rec;
                this.getMenu();
                if (this.menu) { this.menu.showAt(e.getXY()); }
                e.stopEvent();
            }, scope: this},
            cellclick: {fn: function(grid, rowIndex, columnIndex) {
                var rec = grid.store.getAt(rowIndex), cm = grid.getColumnModel(), dataIndex = cm.getDataIndex(columnIndex);
                if (!rec) { return; }
                if (dataIndex === 'is_active') {
                    if (Training.utils.toBool(rec.data.is_active)) { this.disableModule(rec); } else { this.enableModule(rec); }
                }
            }, scope: this}
        }
    });

    Training.grid.CourseModules.superclass.constructor.call(this, config);
};

Ext.extend(Training.grid.CourseModules, MODx.grid.Grid, {
    syncCourse: function() {
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/course/sync'},
            listeners: {success: {fn: function() { this.refresh(); }, scope: this}}
        });
    },

    openModule: function(rec) {
        var id = Training.utils.getRecordValue(rec, 'id');
        var courseId = Training.utils.getRecordValue(rec, 'course_id') || this.courseId;
        if (!id) { return false; }
        window.location.href = Training.utils.buildUrl({course_id: courseId, module_id: id}, ['lesson_id']);
    },

    enableModule: function(rec) {
        MODx.Ajax.request({url: Training.config.connector_url, params: {action: 'mgr/module/enable', id: Training.utils.getRecordValue(rec, 'id')}, listeners: {success: {fn: function(){this.refresh();}, scope: this}}});
    },

    disableModule: function(rec) {
        MODx.Ajax.request({url: Training.config.connector_url, params: {action: 'mgr/module/disable', id: Training.utils.getRecordValue(rec, 'id')}, listeners: {success: {fn: function(){this.refresh();}, scope: this}}});
    },

    openResource: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : null;
        var resourceId = rec ? Training.utils.getRecordValue(rec, 'resource_id') : 0;
        if (parseInt(resourceId, 10) > 0) { MODx.loadPage('resource/update', 'id=' + resourceId); }
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : null;
        if (!rec) { return false; }
        this.addContextMenuItem([{
            text: 'Открыть модуль',
            handler: function(){ this.openModule(rec); },
            scope: this
        },{
            text: 'Открыть ресурс',
            handler: this.openResource,
            scope: this
        }]);
        return true;
    }
});
Ext.reg('training-grid-course-modules', Training.grid.CourseModules);

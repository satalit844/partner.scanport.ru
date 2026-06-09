Training.grid.LessonVideos = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    this.moduleId = config.moduleId || Training.config.module_id;
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.currentVideoId = 0;
    this.activeRecord = null;

    Ext.applyIf(config, {
        id: 'training-grid-lesson-videos',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/lesson/videos/getlist', lesson_id: this.lessonId},
        sm: this.sm,
        fields: ['id','lesson_id','title','description','sort_order','source_video','duration_seconds','video_status','is_default','is_active','qualities_count','slides_count'],
        paging: true,
        remoteSort: true,
        sortBy: 'sort_order',
        sortDir: 'ASC',
        autoHeight: true,
        anchor: '100%',
        multi_select: true,
        columns: [
            this.sm,
            {header: 'ID', dataIndex: 'id', width: 50},
            {header: 'Видео урока', dataIndex: 'title', width: 260},
            {header: 'Порядок', dataIndex: 'sort_order', width: 70},
            {header: 'Исходное видео', dataIndex: 'source_video', width: 300, renderer: Training.utils.renderFileLink},
            {header: 'Статус', dataIndex: 'video_status', width: 80},
            {header: 'Длительность', dataIndex: 'duration_seconds', width: 90, renderer: Training.utils.renderSeconds},
            {header: 'Качества', dataIndex: 'qualities_count', width: 70},
            {header: 'Слайды', dataIndex: 'slides_count', width: 70},
            {header: 'По умолчанию', dataIndex: 'is_default', width: 90, renderer: Training.utils.renderBoolean},
            {header: 'Активен', dataIndex: 'is_active', width: 80, renderer: Training.utils.renderBoolean}
        ],
        tbar: [{
            text: 'Добавить видео',
            handler: this.createVideo,
            scope: this
        },{
            text: 'Открыть',
            handler: this.focusSelected,
            scope: this
        },{
            text: 'Изменить',
            handler: function(){ this.updateVideo(this.getSelectedRecord()); },
            scope: this
        },{
            text: 'Удалить',
            handler: this.removeVideo,
            scope: this
        },'->',{
            text: 'Обновить',
            handler: function(){ this.refresh(); },
            scope: this
        }],
        listeners: {
            rowclick: {fn: function(grid, rowIndex){
                var rec = grid.store.getAt(rowIndex);
                if (rec) {
                    this.setActiveRecord(rec, rowIndex, false);
                    this.selectVideo(rec);
                }
            }, scope: this},
            rowdblclick: {fn: function(grid, rowIndex){
                var rec = grid.store.getAt(rowIndex);
                if (rec) {
                    this.setActiveRecord(rec, rowIndex, false);
                    this.updateVideo(rec);
                }
            }, scope: this},
            rowcontextmenu: {fn: function(grid, rowIndex, e){
                var rec = grid.store.getAt(rowIndex);
                if (!rec) { return; }
                this.setActiveRecord(rec, rowIndex, false);
                this.selectVideo(rec);
                this.menu.record = rec;
                this.getMenu();
                if (this.menu) { this.menu.showAt(e.getXY()); }
                e.stopEvent();
            }, scope: this},
            cellclick: {fn: function(grid, rowIndex, colIndex){
                var rec = grid.store.getAt(rowIndex);
                var dataIndex = grid.getColumnModel().getDataIndex(colIndex);
                if (!rec) { return; }
                this.setActiveRecord(rec, rowIndex, false);
                this.selectVideo(rec);
                if (dataIndex === 'is_active') {
                    this.quickUpdate(rec, {is_active: rec.get('is_active') ? 0 : 1});
                } else if (dataIndex === 'is_default' && !rec.get('is_default')) {
                    this.quickUpdate(rec, {is_default: 1});
                }
            }, scope: this}
        }
    });
    Training.grid.LessonVideos.superclass.constructor.call(this, config);

    var sm = this.getSelectionModel();
    if (sm) {
        sm.on('rowselect', function(sel, rowIndex, rec){
            this.setActiveRecord(rec, rowIndex, true);
            this.selectVideo(rec);
        }, this);
        sm.on('selectionchange', function(){
            if (!this.getSelectedRecord()) {
                this.activeRecord = null;
            }
        }, this);
    }

    this.getStore().on('load', function(store){
        this.activeRecord = null;
        var rec = null;
        if (this.currentVideoId > 0) {
            store.each(function(item){
                if (!rec && parseInt(item.get('id'), 10) === parseInt(this.currentVideoId, 10)) {
                    rec = item;
                }
            }, this);
        }
        if (!rec && store.getCount()) {
            rec = store.getAt(0);
        }
        if (rec) {
            this.setActiveRecord(rec, store.indexOf(rec), false);
            this.selectVideo(rec);
        } else {
            this.selectVideo(null);
        }
    }, this);
};
Ext.extend(Training.grid.LessonVideos, MODx.grid.Grid, {
    setActiveRecord: function(rec, rowIndex, preserve) {
        this.activeRecord = rec || null;
        if (!rec) { return; }
        var sm = this.getSelectionModel();
        if (sm && typeof rowIndex === 'number' && rowIndex >= 0 && !sm.isSelected(rowIndex)) {
            sm.selectRow(rowIndex, preserve === true);
        }
    },

    getSelectedVideoIds: function() {
        var ids = [];
        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        if (sm && sm.getSelections) {
            Ext.each(sm.getSelections(), function(rec) {
                var id = parseInt(Training.utils.getRecordValue(rec, 'id'), 10) || 0;
                if (id) { ids.push(id); }
            });
        }
        if (!ids.length && this.activeRecord) {
            var activeId = parseInt(Training.utils.getRecordValue(this.activeRecord, 'id'), 10) || 0;
            if (activeId) { ids.push(activeId); }
        }
        if (!ids.length && this.menu && this.menu.record) {
            var menuId = parseInt(Training.utils.getRecordValue(this.menu.record, 'id'), 10) || 0;
            if (menuId) { ids.push(menuId); }
        }
        return ids;
    },

    getSelectedRecord: function() {
        if (this.activeRecord) { return this.activeRecord; }
        var sm = this.getSelectionModel(), selections = sm ? sm.getSelections() : [];
        if (selections && selections.length) { return selections[0]; }
        return (this.menu && this.menu.record) ? this.menu.record : null;
    },

    selectVideo: function(rec) {
        var id = rec ? (parseInt(Training.utils.getRecordValue(rec, 'id'), 10) || 0) : 0;
        this.currentVideoId = id;
        var qualityGrid = Ext.getCmp('training-grid-lesson-qualities');
        if (qualityGrid && typeof qualityGrid.setVideoContext === 'function') {
            qualityGrid.setVideoContext(id, rec ? (rec.data || rec) : null);
        }
        var slideGrid = Ext.getCmp('training-grid-lesson-slides');
        if (slideGrid && typeof slideGrid.setVideoContext === 'function') {
            slideGrid.setVideoContext(id, rec ? (rec.data || rec) : null);
        }
    },

    focusSelected: function() {
        var rec = this.getSelectedRecord();
        if (rec) { this.selectVideo(rec); }
    },

    openVideoWindow: function(cfg, values) {
        cfg = cfg || {};
        values = values || {};
        cfg.id = Ext.id(null, 'training-window-lesson-video-');
        var w = MODx.load(cfg);
        w.show();
        Ext.defer(function(){
            if (w.fp && w.fp.getForm) {
                var form = w.fp.getForm();
                form.reset();
                form.setValues(values);
                if (typeof Training.utils.setCheckboxValue === 'function') {
                    Training.utils.setCheckboxValue(form, 'is_default', values.is_default);
                    Training.utils.setCheckboxValue(form, 'is_active', values.is_active);
                }
                w.baseParams = w.baseParams || {};
                if (cfg.baseParams && cfg.baseParams.action) {
                    w.baseParams.action = cfg.baseParams.action;
                }
            }
        }, 20);
        return w;
    },

    quickUpdate: function(rec, patch) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { return false; }
        var data = Ext.apply({}, rec.data || rec);
        data.action = 'mgr/lesson/video/update';
        data.id = parseInt(data.id, 10) || 0;
        data.lesson_id = this.lessonId;
        data.title = data.title || 'Видео';
        data.sort_order = parseInt(data.sort_order, 10) || 1;
        data.is_default = data.is_default ? 1 : 0;
        data.is_active = data.is_active ? 1 : 0;
        Ext.apply(data, patch || {});
        MODx.Ajax.request({url: Training.config.connector_url, params: data, listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}});
        return true;
    },

    createVideo: function() {
        var nextSort = 1;
        this.getStore().each(function(rec){
            nextSort = Math.max(nextSort, (parseInt(rec.get('sort_order'), 10) || 0) + 1);
        });
        this.openVideoWindow({
            xtype: 'training-window-lesson-video',
            title: 'Добавить видео урока',
            baseParams: {action: 'mgr/lesson/video/create'},
            lessonId: this.lessonId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, {lesson_id: this.lessonId, sort_order: nextSort, is_active: 1, is_default: 0});
    },

    updateVideo: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { MODx.msg.alert('Внимание', 'Выбери видео урока'); return false; }
        this.openVideoWindow({
            xtype: 'training-window-lesson-video',
            title: 'Изменить видео урока',
            baseParams: {action: 'mgr/lesson/video/update'},
            lessonId: this.lessonId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, Ext.apply({}, rec.data || rec));
    },

    removeVideo: function() {
        var ids = this.getSelectedVideoIds();
        if (!ids.length) { MODx.msg.alert('Внимание', 'Выбери видео урока'); return false; }
        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные видео урока вместе с качествами и слайдами?' : 'Удалить видео урока вместе с качествами и слайдами?',
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/video/remove', ids: ids.join(',')},
            listeners: {success: {fn: function(){ this.currentVideoId = 0; this.activeRecord = null; this.refresh(); }, scope: this}}
        });
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSelectedRecord();
        if (!rec) { return false; }
        this.addContextMenuItem([{
            text: 'Открыть',
            handler: function(){ this.selectVideo(rec); },
            scope: this
        },{
            text: 'Изменить',
            handler: function(){ this.updateVideo(rec); },
            scope: this
        },{
            text: 'Удалить',
            handler: function(){ this.removeVideo(); },
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
Ext.reg('training-grid-lesson-videos', Training.grid.LessonVideos);

Training.window.LessonVideo = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    Ext.applyIf(config, {
        width: 760,
        autoHeight: true,
        url: Training.config.connector_url,
        fields: [
            {xtype:'hidden',name:'id'},
            {xtype:'hidden',name:'lesson_id',value:this.lessonId},
            {xtype:'textfield',fieldLabel:'Название',name:'title',anchor:'100%',allowBlank:false},
            {xtype:'numberfield',fieldLabel:'Порядок',name:'sort_order',anchor:'100%'},
            {xtype:'button',text:'Выбрать исходное видео',style:'margin-bottom:10px;',handler:function(btn){var win=btn.findParentByType('modx-window')||btn.findParentByType('window');var form=win&&win.fp?win.fp.getForm():null;var field=form?form.findField('source_video'):null;Training.utils.openPathBrowser(field,{source:Training.config.media_source||3,allowedFileTypes:'mkv,mp4,mov,avi,webm,m3u8'});}},
            {xtype:'textfield',fieldLabel:'Исходное видео',name:'source_video',anchor:'100%'},
            {xtype:'textarea',fieldLabel:'Описание',name:'description',anchor:'100%'},
            {xtype:'xcheckbox',boxLabel:'По умолчанию',hideLabel:true,name:'is_default',inputValue:1},
            {xtype:'xcheckbox',boxLabel:'Активно',hideLabel:true,name:'is_active',inputValue:1,checked:true}
        ]
    });
    Training.window.LessonVideo.superclass.constructor.call(this, config);
};
Ext.extend(Training.window.LessonVideo, MODx.Window);
Ext.reg('training-window-lesson-video', Training.window.LessonVideo);

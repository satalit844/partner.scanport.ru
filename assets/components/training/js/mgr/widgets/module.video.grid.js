Training.grid.LessonQualities = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    this.lessonVideoId = config.lessonVideoId || 0;
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.activeRecord = null;

    Ext.applyIf(config, {
        id: 'training-grid-lesson-qualities',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/lesson/qualities/getlist', lesson_video_id: this.lessonVideoId},
        sm: this.sm,
        fields: ['id','lesson_video_id','quality','mime','file_path','width','height','bitrate','filesize','is_default','is_active'],
        paging: true,
        remoteSort: false,
        autoHeight: true,
        anchor: '100%',
        multi_select: true,
        columns: [
            this.sm,
            {header: 'ID', dataIndex: 'id', width: 50},
            {header: 'Качество', dataIndex: 'quality', width: 80},
            {header: 'MIME', dataIndex: 'mime', width: 120},
            {header: 'Файл', dataIndex: 'file_path', width: 320, renderer: Training.utils.renderFileLink},
            {header: 'Размер', dataIndex: 'filesize', width: 100, renderer: Training.utils.renderBytes},
            {header: 'Разрешение', dataIndex: 'width', width: 100, renderer: function(v,m,rec){ return (rec.data.width||0) + '×' + (rec.data.height||0); }},
            {header: 'Битрейт', dataIndex: 'bitrate', width: 80},
            {header: 'По умолчанию', dataIndex: 'is_default', width: 90, renderer: Training.utils.renderBoolean},
            {header: 'Активен', dataIndex: 'is_active', width: 80, renderer: Training.utils.renderBoolean}
        ],
        tbar: [{
            text: 'Сгенерировать из исходника',
            handler: this.transcodeFromSource,
            scope: this
        },'-',{
            text: 'Добавить качество',
            handler: this.createQuality,
            scope: this
        },{
            text: 'Изменить',
            handler: function(){ this.updateQuality(this.getSelectedRecord()); },
            scope: this
        },{
            text: 'Удалить',
            handler: this.removeQuality,
            scope: this
        },'->',{
            text: 'Обновить',
            handler: function(){ this.refresh(); },
            scope: this
        }],
        listeners: {
            rowclick: {fn: function(grid, rowIndex){
                var rec = grid.store.getAt(rowIndex);
                if (rec) { this.setActiveRecord(rec, rowIndex, false); }
            }, scope: this},
            rowdblclick: {fn: function(grid, rowIndex){ var rec = grid.store.getAt(rowIndex); if (rec) { this.setActiveRecord(rec, rowIndex, false); this.updateQuality(rec); } }, scope: this},
            rowcontextmenu: {fn: function(grid, rowIndex, e){
                var rec = grid.store.getAt(rowIndex); if (!rec) { return; }
                this.setActiveRecord(rec, rowIndex, false);
                this.menu.record = rec;
                this.getMenu();
                if (this.menu) { this.menu.showAt(e.getXY()); }
                e.stopEvent();
            }, scope: this},
            cellclick: {fn: function(grid, rowIndex, colIndex){
                var rec = grid.store.getAt(rowIndex); var dataIndex = grid.getColumnModel().getDataIndex(colIndex); if (!rec) { return; }
                this.setActiveRecord(rec, rowIndex, false);
                if (dataIndex === 'is_active') { this.quickUpdate(rec, {is_active: rec.get('is_active') ? 0 : 1}); }
                else if (dataIndex === 'is_default' && !rec.get('is_default')) { this.quickUpdate(rec, {is_default: 1}); }
            }, scope: this}
        }
    });

    Training.grid.LessonQualities.superclass.constructor.call(this, config);
    var sm = this.getSelectionModel();
    if (sm) {
        sm.on('rowselect', function(sel, rowIndex, rec){ this.setActiveRecord(rec, rowIndex, true); }, this);
        sm.on('selectionchange', function(){ if (!this.getSelectedRecord()) { this.activeRecord = null; } }, this);
    }
};
Ext.extend(Training.grid.LessonQualities, MODx.grid.Grid, {
    setActiveRecord: function(rec, rowIndex, preserve) {
        this.activeRecord = rec || null;
        if (!rec) { return; }
        var sm = this.getSelectionModel();
        if (sm && typeof rowIndex === 'number' && rowIndex >= 0 && !sm.isSelected(rowIndex)) {
            sm.selectRow(rowIndex, preserve === true);
        }
    },

    setVideoContext: function(videoId) {
        this.lessonVideoId = parseInt(videoId, 10) || 0;
        this.activeRecord = null;
        this.menu.record = null;
        this.baseParams.lesson_video_id = this.lessonVideoId;
        var store = this.getStore();
        store.baseParams.lesson_video_id = this.lessonVideoId;
        store.removeAll();
        if (this.lessonVideoId > 0) {
            store.load({params: {start: 0, limit: this.pageSize || 20}});
        }
    },

    ensureSelectedVideo: function() {
        if (this.lessonVideoId > 0) { return true; }
        MODx.msg.alert('Внимание', 'Сначала выбери видео урока в верхней таблице');
        return false;
    },

    getSelectedQualityIds: function() {
        var ids = [];
        var sm = this.getSelectionModel ? this.getSelectionModel() : this.sm;
        if (sm && sm.getSelections) {
            Ext.each(sm.getSelections(), function(rec){
                var id = parseInt(Training.utils.getRecordValue(rec, 'id'), 10) || 0;
                if (id) { ids.push(id); }
            });
        }
        if (!ids.length && this.activeRecord) {
            var activeId = parseInt(Training.utils.getRecordValue(this.activeRecord, 'id'), 10) || 0;
            if (activeId) { ids.push(activeId); }
        }
        if (!ids.length && this.menu && this.menu.record) {
            var id = parseInt(Training.utils.getRecordValue(this.menu.record, 'id'), 10) || 0;
            if (id) { ids.push(id); }
        }
        return ids;
    },

    getSelectedRecord: function() {
        if (this.activeRecord) { return this.activeRecord; }
        var sm = this.getSelectionModel(), selections = sm ? sm.getSelections() : [];
        if (selections && selections.length) { return selections[0]; }
        return (this.menu && this.menu.record) ? this.menu.record : null;
    },

    openQualityWindow: function(cfg, values) {
        cfg = cfg || {};
        values = values || {};
        cfg.id = Ext.id(null, 'training-window-lesson-quality-');
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
        data.action = 'mgr/lesson/quality/update';
        data.id = parseInt(data.id, 10) || 0;
        data.lesson_video_id = this.lessonVideoId;
        data.is_default = data.is_default ? 1 : 0;
        data.is_active = data.is_active ? 1 : 0;
        Ext.apply(data, patch || {});
        MODx.Ajax.request({url: Training.config.connector_url, params: data, listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}});
        return true;
    },

    transcodeFromSource: function() {
        if (!this.ensureSelectedVideo()) { return false; }
        this.getEl().mask('Генерируем качества...');
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/video/transcode', lesson_video_id: this.lessonVideoId},
            listeners: {success: {fn: function(){
                this.getEl().unmask();
                this.refresh();
                var videos = Ext.getCmp('training-grid-lesson-videos');
                if (videos) { videos.refresh(); }
            }, scope: this}, failure: {fn: function(){ this.getEl().unmask(); }, scope: this}}
        });
    },

    createQuality: function() {
        if (!this.ensureSelectedVideo()) { return false; }
        this.openQualityWindow({
            xtype: 'training-window-lesson-quality',
            title: 'Добавить качество',
            baseParams: {action: 'mgr/lesson/quality/create'},
            lessonVideoId: this.lessonVideoId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, {lesson_video_id: this.lessonVideoId, mime: 'video/mp4', is_active: 1, is_default: 0});
    },

    updateQuality: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { MODx.msg.alert('Внимание', 'Выбери качество'); return false; }
        this.openQualityWindow({
            xtype: 'training-window-lesson-quality',
            title: 'Изменить качество',
            baseParams: {action: 'mgr/lesson/quality/update'},
            lessonVideoId: this.lessonVideoId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, Ext.apply({}, rec.data || rec));
    },

    removeQuality: function() {
        var ids = this.getSelectedQualityIds();
        if (!ids.length) { MODx.msg.alert('Внимание', 'Выбери качество'); return false; }
        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные качества видео?' : 'Удалить качество видео?',
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/quality/remove', ids: ids.join(',')},
            listeners: {success: {fn: function(){ this.activeRecord = null; this.refresh(); }, scope: this}}
        });
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSelectedRecord();
        if (!rec) { return false; }
        this.addContextMenuItem([{
            text: 'Изменить',
            handler: function(){ this.updateQuality(rec); },
            scope: this
        },{
            text: 'Удалить',
            handler: function(){ this.removeQuality(); },
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
Ext.reg('training-grid-lesson-qualities', Training.grid.LessonQualities);

Training.window.LessonQuality = function(config) {
    config = config || {};
    this.lessonVideoId = config.lessonVideoId || 0;
    Ext.applyIf(config, {
        width: 760,
        autoHeight: true,
        url: Training.config.connector_url,
        fields: [
            {xtype:'hidden',name:'id'},
            {xtype:'hidden',name:'lesson_video_id',value:this.lessonVideoId},
            {xtype:'textfield',fieldLabel:'Качество',name:'quality',anchor:'100%',allowBlank:false},
            {xtype:'textfield',fieldLabel:'MIME',name:'mime',anchor:'100%'},
            {xtype:'button',text:'Выбрать файл качества',style:'margin-bottom:10px;',handler:function(btn){var win=btn.findParentByType('modx-window')||btn.findParentByType('window');var form=win&&win.fp?win.fp.getForm():null;var field=form?form.findField('file_path'):null;Training.utils.openPathBrowser(field,{source:Training.config.media_source||3,allowedFileTypes:'mp4,m3u8,webm,mov'});}},
            {xtype:'textfield',fieldLabel:'Файл',name:'file_path',anchor:'100%',allowBlank:false},
            {xtype:'numberfield',fieldLabel:'Ширина',name:'width',anchor:'100%'},
            {xtype:'numberfield',fieldLabel:'Высота',name:'height',anchor:'100%'},
            {xtype:'numberfield',fieldLabel:'Битрейт',name:'bitrate',anchor:'100%'},
            {xtype:'numberfield',fieldLabel:'Размер файла',name:'filesize',anchor:'100%'},
            {xtype:'xcheckbox',boxLabel:'По умолчанию',hideLabel:true,name:'is_default',inputValue:1},
            {xtype:'xcheckbox',boxLabel:'Активно',hideLabel:true,name:'is_active',inputValue:1,checked:true}
        ]
    });
    Training.window.LessonQuality.superclass.constructor.call(this, config);
};
Ext.extend(Training.window.LessonQuality, MODx.Window);
Ext.reg('training-window-lesson-quality', Training.window.LessonQuality);

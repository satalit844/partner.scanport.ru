Training.grid.LessonSlides = function(config) {
    config = config || {};
    this.lessonId = config.lessonId || Training.config.lesson_id;
    this.lessonVideoId = config.lessonVideoId || 0;
    this.sm = new Ext.grid.CheckboxSelectionModel();
    this.activeRecord = null;

    Ext.applyIf(config, {
        id: 'training-grid-lesson-slides',
        url: Training.config.connector_url,
        baseParams: {action: 'mgr/lesson/slides/getlist', lesson_video_id: this.lessonVideoId},
        sm: this.sm,
        fields: ['id','lesson_video_id','slide_no','image','timecode_ms','timecode_human','is_active'],
        paging: true,
        remoteSort: false,
        autoHeight: true,
        anchor: '100%',
        multi_select: true,
        columns: [
            this.sm,
            {header: 'ID', dataIndex: 'id', width: 50},
            {header: 'Слайд', dataIndex: 'slide_no', width: 70},
            {header: 'Изображение', dataIndex: 'image', width: 300, renderer: Training.utils.renderFileLink},
            {header: 'Превью', dataIndex: 'image', width: 100, renderer: Training.utils.renderSlidePreview},
            {header: 'Таймкод', dataIndex: 'timecode_human', width: 80},
            {header: 'Активен', dataIndex: 'is_active', width: 80, renderer: Training.utils.renderBoolean}
        ],
        tbar: [{
            text: 'Импортировать из презентации',
            handler: this.importSlides,
            scope: this
        },{
            text: 'Расставить таймкоды',
            handler: this.autoTimecodes,
            scope: this
        },'-',{
            text: 'Добавить слайд',
            handler: this.createSlide,
            scope: this
        },{
            text: 'Изменить',
            handler: function(){ this.updateSlide(this.getSelectedRecord()); },
            scope: this
        },{
            text: 'Удалить',
            handler: this.removeSlide,
            scope: this
        },'->',{
            text: 'Обновить',
            handler: function(){ this.refresh(); },
            scope: this
        }],
        listeners: {
            rowclick: {fn: function(grid, rowIndex){ var rec = grid.store.getAt(rowIndex); if (rec) { this.setActiveRecord(rec, rowIndex, false); } }, scope: this},
            rowdblclick: {fn: function(grid, rowIndex){ var rec = grid.store.getAt(rowIndex); if (rec) { this.setActiveRecord(rec, rowIndex, false); this.updateSlide(rec); } }, scope: this},
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
            }, scope: this}
        }
    });

    Training.grid.LessonSlides.superclass.constructor.call(this, config);
    var sm = this.getSelectionModel();
    if (sm) {
        sm.on('rowselect', function(sel, rowIndex, rec){ this.setActiveRecord(rec, rowIndex, true); }, this);
        sm.on('selectionchange', function(){ if (!this.getSelectedRecord()) { this.activeRecord = null; } }, this);
    }
};
Ext.extend(Training.grid.LessonSlides, MODx.grid.Grid, {
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

    getSelectedSlideIds: function() {
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

    openSlideWindow: function(cfg, values) {
        cfg = cfg || {};
        values = values || {};
        cfg.id = Ext.id(null, 'training-window-lesson-slide-');
        var w = MODx.load(cfg);
        w.show();
        Ext.defer(function(){
            if (w.fp && w.fp.getForm) {
                var form = w.fp.getForm();
                form.reset();
                form.setValues(values);
                if (typeof Training.utils.setCheckboxValue === 'function') {
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
        data.action = 'mgr/lesson/slide/update';
        data.id = parseInt(data.id, 10) || 0;
        data.lesson_video_id = this.lessonVideoId;
        data.is_active = data.is_active ? 1 : 0;
        Ext.apply(data, patch || {});
        MODx.Ajax.request({url: Training.config.connector_url, params: data, listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}});
        return true;
    },

    importSlides: function() {
        if (!this.ensureSelectedVideo()) { return false; }
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/slide/import', lesson_id: this.lessonId, lesson_video_id: this.lessonVideoId},
            listeners: {success: {fn: function(){ this.refresh(); var videos = Ext.getCmp('training-grid-lesson-videos'); if (videos) { videos.refresh(); } }, scope: this}}
        });
    },

    autoTimecodes: function() {
        if (!this.ensureSelectedVideo()) { return false; }
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/slide/autotimecodes', lesson_video_id: this.lessonVideoId},
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        });
    },

    createSlide: function() {
        if (!this.ensureSelectedVideo()) { return false; }
        this.openSlideWindow({
            xtype: 'training-window-lesson-slide',
            title: 'Добавить слайд',
            baseParams: {action: 'mgr/lesson/slide/create'},
            lessonVideoId: this.lessonVideoId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, {lesson_video_id: this.lessonVideoId, is_active: 1});
    },

    updateSlide: function(rec) {
        rec = rec || this.getSelectedRecord();
        if (!rec) { MODx.msg.alert('Внимание', 'Выбери слайд'); return false; }
        this.openSlideWindow({
            xtype: 'training-window-lesson-slide',
            title: 'Изменить слайд',
            baseParams: {action: 'mgr/lesson/slide/update'},
            lessonVideoId: this.lessonVideoId,
            listeners: {success: {fn: function(){ this.refresh(); }, scope: this}}
        }, Ext.apply({}, rec.data || rec));
    },

    removeSlide: function() {
        var ids = this.getSelectedSlideIds();
        if (!ids.length) { MODx.msg.alert('Внимание', 'Выбери слайд'); return false; }
        MODx.msg.confirm({
            title: 'Удаление',
            text: ids.length > 1 ? 'Удалить выбранные слайды?' : 'Удалить слайд?',
            url: Training.config.connector_url,
            params: {action: 'mgr/lesson/slide/remove', ids: ids.join(',')},
            listeners: {success: {fn: function(){ this.activeRecord = null; this.refresh(); var videos = Ext.getCmp('training-grid-lesson-videos'); if (videos) { videos.refresh(); } }, scope: this}}
        });
    },

    getMenu: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : this.getSelectedRecord();
        if (!rec) { return false; }
        this.addContextMenuItem([{
            text: 'Изменить',
            handler: function(){ this.updateSlide(rec); },
            scope: this
        },{
            text: 'Удалить',
            handler: function(){ this.removeSlide(); },
            scope: this
        },'-',{
            text: Training.utils.getRecordValue(rec, 'is_active') ? 'Отключить' : 'Включить',
            handler: function(){ this.quickUpdate(rec, {is_active: Training.utils.getRecordValue(rec, 'is_active') ? 0 : 1}); },
            scope: this
        }]);
        return true;
    }
});
Ext.reg('training-grid-lesson-slides', Training.grid.LessonSlides);

Training.window.LessonSlide = function(config) {
    config = config || {};
    this.lessonVideoId = config.lessonVideoId || 0;
    Ext.applyIf(config, {
        width: 760,
        autoHeight: true,
        url: Training.config.connector_url,
        fields: [
            {xtype:'hidden',name:'id'},
            {xtype:'hidden',name:'lesson_video_id',value:this.lessonVideoId},
            {xtype:'numberfield',fieldLabel:'Номер слайда',name:'slide_no',anchor:'100%',allowBlank:false},
            {xtype:'button',text:'Выбрать изображение',style:'margin-bottom:10px;',handler:function(btn){var win=btn.findParentByType('modx-window')||btn.findParentByType('window');var form=win&&win.fp?win.fp.getForm():null;var field=form?form.findField('image'):null;Training.utils.openPathBrowser(field,{source:Training.config.media_source||3,allowedFileTypes:'jpg,jpeg,png,webp,gif'});}},
            {xtype:'textfield',fieldLabel:'Изображение',name:'image',anchor:'100%',allowBlank:false},
            {xtype:'numberfield',fieldLabel:'Таймкод (мс)',name:'timecode_ms',anchor:'100%'},
            {xtype:'xcheckbox',boxLabel:'Активен',hideLabel:true,name:'is_active',inputValue:1,checked:true}
        ]
    });
    Training.window.LessonSlide.superclass.constructor.call(this, config);
};
Ext.extend(Training.window.LessonSlide, MODx.Window);
Ext.reg('training-window-lesson-slide', Training.window.LessonSlide);

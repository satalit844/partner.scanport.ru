Training.grid.Courses = function(config) {
    config = config || {};

    Ext.applyIf(config, {
        id: 'training-grid-courses',
        url: Training.config.connector_url,
        baseParams: {
            action: 'mgr/course/getlist'
        },
        fields: ['id', 'resource_id', 'pagetitle', 'resource_published', 'modules_count', 'is_active'],
        paging: true,
        remoteSort: true,
        sortBy: 'id',
        sortDir: 'ASC',
        autoHeight: true,
        anchor: '100%',
        columns: [{
            header: 'ID',
            dataIndex: 'id',
            width: 50,
            sortable: true
        }, {
            header: 'Курс',
            dataIndex: 'pagetitle',
            width: 300,
            sortable: true
        }, {
            header: 'Resource ID',
            dataIndex: 'resource_id',
            width: 80,
            sortable: true
        }, {
            header: 'Модулей',
            dataIndex: 'modules_count',
            width: 80,
            sortable: true
        }, {
            header: 'Опубликован',
            dataIndex: 'resource_published',
            width: 90,
            renderer: Training.utils.renderBoolean
        }, {
            header: 'Активен',
            dataIndex: 'is_active',
            width: 90,
            renderer: Training.utils.renderBoolean
        }],
        listeners: {
            rowdblclick: {
                fn: function(grid, rowIndex) {
                    var rec = grid.store.getAt(rowIndex);
                    if (rec) {
                        this.openCourse(rec.data.id);
                    }
                },
                scope: this
            },
            cellclick: {
                fn: function(grid, rowIndex, columnIndex) {
                    var rec = grid.store.getAt(rowIndex);
                    var cm = grid.getColumnModel();
                    var dataIndex = cm.getDataIndex(columnIndex);

                    if (!rec) {
                        return;
                    }

                    if (dataIndex === 'is_active') {
                        if (Training.utils.toBool(rec.data.is_active)) {
                            this.disableCourse(rec);
                        } else {
                            this.enableCourse(rec);
                        }
                    }
                },
                scope: this
            }
        },
        tbar: [{
            text: 'Синхронизировать',
            handler: this.syncCourses,
            scope: this
        }, '-', {
            text: 'Проверить медиа-мусор',
            handler: this.previewMediaCleanup,
            scope: this
        }]
    });

    Training.grid.Courses.superclass.constructor.call(this, config);
};

Ext.extend(Training.grid.Courses, MODx.grid.Grid, {
    openCourse: function(id) {
        var url = new URL(window.location.href);
        url.searchParams.set('course_id', id);
        window.location.href = url.toString();
    },

    syncCourses: function() {
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/sync'
            },
            listeners: {
                success: {
                    fn: function() {
                        this.refresh();
                    },
                    scope: this
                }
            }
        });
    },

    previewMediaCleanup: function() {
        var grid = this;
        grid.getEl().mask('Проверка медиамусора...');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/media/cleanup',
                dry_run: 1,
                max_list: 20
            },
            listeners: {
                success: {
                    fn: function(response) {
                        grid.getEl().unmask();
                        var object = response && response.object ? response.object : {};
                        var count = parseInt(object.orphans_count || 0, 10) || 0;

                        if (count <= 0) {
                            MODx.msg.alert('Очистка медиамусора', 'Осиротевшие файлы не найдены.');
                            return;
                        }

                        var html = grid.buildMediaCleanupPreviewHtml(object);
                        Ext.Msg.show({
                            title: 'Очистка медиамусора',
                            msg: html,
                            buttons: Ext.Msg.YESNO,
                            icon: Ext.MessageBox.WARNING,
                            minWidth: 620,
                            fn: function(btn) {
                                if (btn === 'yes') {
                                    grid.runMediaCleanup();
                                }
                            }
                        });
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        grid.getEl().unmask();
                        MODx.msg.alert('Ошибка', (response && response.message) ? response.message : 'Не удалось выполнить проверку медиамусора');
                    },
                    scope: this
                }
            }
        });
    },

    runMediaCleanup: function() {
        var grid = this;
        grid.getEl().mask('Удаление медиамусора...');

        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/media/cleanup',
                dry_run: 0,
                max_list: 20
            },
            listeners: {
                success: {
                    fn: function(response) {
                        grid.getEl().unmask();
                        var object = response && response.object ? response.object : {};
                        var message = 'Удалено файлов: <b>' + (parseInt(object.deleted_count || 0, 10) || 0) + '</b>' +
                            '<br>Освобождено: <b>' + Training.utils.formatBytes(object.deleted_bytes || 0) + '</b>';

                        if ((parseInt(object.failed_count || 0, 10) || 0) > 0) {
                            message += '<br>Не удалось удалить: <b>' + (parseInt(object.failed_count || 0, 10) || 0) + '</b>';
                        }

                        MODx.msg.alert('Очистка медиамусора', message);
                    },
                    scope: this
                },
                failure: {
                    fn: function(response) {
                        grid.getEl().unmask();
                        MODx.msg.alert('Ошибка', (response && response.message) ? response.message : 'Не удалось удалить медиамусор');
                    },
                    scope: this
                }
            }
        });
    },

    buildMediaCleanupPreviewHtml: function(object) {
        object = object || {};
        var html = '' +
            '<div style="line-height:1.5;">' +
                'Найдено файлов: <b>' + (parseInt(object.orphans_count || 0, 10) || 0) + '</b><br>' +
                'Объём: <b>' + Training.utils.formatBytes(object.orphans_bytes || 0) + '</b>';

        var preview = object.orphans_preview || [];
        if (preview.length) {
            html += '<div style="margin-top:10px;max-height:240px;overflow:auto;border:1px solid #ddd;padding:8px;background:#fff;">';
            for (var i = 0; i < preview.length; i++) {
                html += '<div style="margin-bottom:6px;word-break:break-all;">' + Training.utils.escapeHtml(preview[i].path || '') + '</div>';
            }
            html += '</div>';
        }

        html += '<div style="margin-top:10px;color:#666;">Сначала выполнена проверка. Подтвердите удаление найденных файлов.</div></div>';
        return html;
    },

    enableCourse: function(rec) {
        rec = rec || null;
        if (!rec) {
            return false;
        }
    
        var id = Training.utils.getRecordValue(rec, 'id');
        if (!id) {
            return false;
        }
    
        this.getEl().mask('Включение курса...');
    
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/enable',
                id: id
            },
            listeners: {
                success: {
                    fn: function() {
                        this.getEl().unmask();
                        this.refresh();
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось включить курс');
                    },
                    scope: this
                }
            }
        });
    },
    
    disableCourse: function(rec) {
        rec = rec || null;
        if (!rec) {
            return false;
        }
    
        var id = Training.utils.getRecordValue(rec, 'id');
        if (!id) {
            return false;
        }
    
        this.getEl().mask('Выключение курса...');
    
        MODx.Ajax.request({
            url: Training.config.connector_url,
            params: {
                action: 'mgr/course/disable',
                id: id
            },
            listeners: {
                success: {
                    fn: function() {
                        this.getEl().unmask();
                        this.refresh();
                    },
                    scope: this
                },
                failure: {
                    fn: function(r) {
                        this.getEl().unmask();
                        MODx.msg.alert('Ошибка', (r && r.message) ? r.message : 'Не удалось выключить курс');
                    },
                    scope: this
                }
            }
        });
    },

    openResource: function() {
        var rec = this.menu && this.menu.record ? this.menu.record : null;
        if (!rec) {
            return false;
        }
    
        var resourceId = Training.utils.getRecordValue(rec, 'resource_id');
        if (parseInt(resourceId, 10) > 0) {
            MODx.loadPage('resource/update', 'id=' + resourceId);
        }
    },

    getMenu: function() {
        var m = [];
        var rec = this.menu && this.menu.record ? this.menu.record : null;
        var id, resourceId, isActive;
    
        if (!rec) {
            return false;
        }
    
        id = Training.utils.getRecordValue(rec, 'id');
        resourceId = Training.utils.getRecordValue(rec, 'resource_id');
        isActive = Training.utils.getRecordValue(rec, 'is_active');
    
        m.push({
            text: 'Открыть модули курса',
            handler: function() {
                this.openCourse(id);
            },
            scope: this
        });
    
        if (parseInt(resourceId, 10) > 0) {
            m.push({
                text: 'Открыть ресурс MODX',
                handler: function() {
                    MODx.loadPage('resource/update', 'id=' + resourceId);
                },
                scope: this
            });
        }
    
        m.push('-');
    
        if (Training.utils.toBool(isActive)) {
            m.push({
                text: 'Выключить курс',
                handler: function() {
                    this.disableCourse(rec);
                },
                scope: this
            });
        } else {
            m.push({
                text: 'Включить курс',
                handler: function() {
                    this.enableCourse(rec);
                },
                scope: this
            });
        }
    
        this.addContextMenuItem(m);
    }
});

Ext.reg('training-grid-courses', Training.grid.Courses);

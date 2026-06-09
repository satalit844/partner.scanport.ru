var Training = function(config) {
    config = config || {};
    Training.superclass.constructor.call(this, config);
};
Ext.extend(Training, Ext.Component, {});
Ext.reg('training', Training);

Training = new Training();

Training.config = Training.config || {};
Training.utils = Training.utils || {};
Training.grid = Training.grid || {};
Training.panel = Training.panel || {};
Training.page = Training.page || {};
Training.form = Training.form || {};
Training.window = Training.window || {};
Training.combo = Training.combo || {};

Training.utils.toBool = function(value) {
    return value === true || value === 1 || value === '1' || value === 'true' || value === 'yes';
};

Training.utils.getRecordValue = function(rec, key) {
    if (!rec) {
        return null;
    }

    if (rec.data && typeof rec.data[key] !== 'undefined') {
        return rec.data[key];
    }

    if (typeof rec[key] !== 'undefined') {
        return rec[key];
    }

    return null;
};

Training.utils.renderBoolean = function(value) {
    var active = Training.utils.toBool(value);
    if (active) {
        return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#e6f6ea;color:#1f7a35;font-weight:600;">Да</span>';
    }
    return '<span style="display:inline-block;padding:2px 8px;border-radius:10px;background:#f3f3f3;color:#888;font-weight:600;">Нет</span>';
};

Training.utils.escapeHtml = function(value) {
    value = value || '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
};

Training.utils.normalizeLink = function(value) {
    value = value || '';
    value = String(value).replace(/^\s+|\s+$/g, '');
    if (!value) {
        return '';
    }
    if (/^(https?:)?\/\//i.test(value) || value.indexOf('/') === 0) {
        return value;
    }
    return '/' + value.replace(/^\.?\//, '');
};

Training.utils.renderFileLink = function(value) {
    var url = Training.utils.normalizeLink(value);
    if (!url || url === '—') {
        return '—';
    }
    return '<a href="' + Training.utils.escapeHtml(url) + '" target="_blank">' + Training.utils.escapeHtml(value) + '</a>';
};

Training.utils.renderSlidePreview = function(value) {
    var url = Training.utils.normalizeLink(value);
    if (!url) {
        return '—';
    }
    return '<a href="' + Training.utils.escapeHtml(url) + '" target="_blank"><img src="' + Training.utils.escapeHtml(url) + '" alt="" style="max-width:80px;max-height:48px;border-radius:4px;border:1px solid #ddd;display:block;margin:4px auto;" /></a>';
};

Training.utils.formatSeconds = function(value) {
    var total = parseInt(value, 10) || 0;
    var hours = Math.floor(total / 3600);
    var minutes = Math.floor((total % 3600) / 60);
    var seconds = total % 60;
    if (hours > 0) {
        return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');
    }
    return minutes + ':' + String(seconds).padStart(2, '0');
};

Training.utils.formatBytes = function(value) {
    var bytes = parseInt(value, 10) || 0;
    var units = ['Б', 'КБ', 'МБ', 'ГБ', 'ТБ'];
    var unitIndex = 0;

    if (!bytes) {
        return '0 Б';
    }

    while (bytes >= 1024 && unitIndex < units.length - 1) {
        bytes = bytes / 1024;
        unitIndex++;
    }

    return (Math.round(bytes * 100) / 100) + ' ' + units[unitIndex];
};

Training.utils.renderSeconds = function(value) {
    return Training.utils.formatSeconds(value || 0);
};

Training.utils.renderBytes = function(value) {
    return Training.utils.formatBytes(value || 0);
};

Training.utils.getGridSelectionModel = function(grid) {
    if (!grid) {
        return null;
    }
    if (grid.sm) {
        return grid.sm;
    }
    if (grid.getSelectionModel) {
        return grid.getSelectionModel();
    }
    return null;
};

Training.utils.getSelectedRecords = function(grid) {
    var sm = Training.utils.getGridSelectionModel(grid);
    if (sm && sm.getSelections) {
        return sm.getSelections() || [];
    }
    if (sm && sm.getSelected) {
        var rec = sm.getSelected();
        return rec ? [rec] : [];
    }

    if (grid && typeof grid.getSelectedAsList === 'function') {
        var ids = String(grid.getSelectedAsList() || '').split(',');
        var results = [];
        Ext.each(ids, function(id) {
            id = parseInt(id, 10) || 0;
            if (id && grid.store) {
                var rec = grid.store.getById ? grid.store.getById(id) : null;
                if (!rec && grid.store.each) {
                    grid.store.each(function(item) {
                        if (!rec && parseInt(item.get('id'), 10) === id) {
                            rec = item;
                        }
                    });
                }
                if (rec) {
                    results.push(rec);
                }
            }
        });
        return results;
    }

    return [];
};

Training.utils.getSelectedIds = function(grid) {
    if (grid && typeof grid.getSelectedAsList === 'function') {
        var list = String(grid.getSelectedAsList() || '').replace(/\s+/g, '');
        if (list) {
            return Ext.pluck(Ext.filter(list.split(','), function(item) {
                return !!parseInt(item, 10);
            }), undefined) || list.split(',');
        }
    }

    var ids = [];
    Ext.each(Training.utils.getSelectedRecords(grid), function(rec) {
        var id = parseInt(Training.utils.getRecordValue(rec, 'id'), 10) || 0;
        if (id) {
            ids.push(id);
        }
    });
    return ids;
};

Training.utils.setCheckboxValue = function(form, fieldName, value) {
    if (!form || !form.findField) {
        return;
    }
    var field = form.findField(fieldName);
    if (field && field.setValue) {
        field.setValue(Training.utils.toBool(value) ? 1 : 0);
    }
};

Training.utils.setFormValues = function(form, values) {
    values = values || {};
    if (!form || !form.findField) {
        return;
    }
    for (var key in values) {
        if (!values.hasOwnProperty(key)) {
            continue;
        }
        var field = form.findField(key);
        if (!field || !field.setValue) {
            continue;
        }
        field.setValue(values[key]);
    }
};

Training.utils.applyPathFromRecord = function(win, record, targetFieldName, extra) {
    if (!win || !record) {
        return;
    }
    var form = win.fp ? win.fp.getForm() : (win.getForm ? win.getForm() : null);
    if (!form) {
        return;
    }
    var path = record.data && record.data.path ? record.data.path : '';
    var targetField = form.findField(targetFieldName);
    if (targetField) {
        targetField.setValue(path || '');
    }
    if (extra && typeof extra === 'function') {
        extra(form, record);
    }
};

Training.utils.extractBrowserPath = function(data) {
    if (Ext.isArray(data) && data.length) {
        data = data[0];
    }
    if (!data) {
        return '';
    }
    if (typeof data === 'string') {
        return Training.utils.normalizeLink(data);
    }

    var keys = ['fullRelativeUrl', 'relativeUrl', 'url', 'path', 'value', 'file', 'pathname'];
    for (var i = 0; i < keys.length; i++) {
        if (data[keys[i]]) {
            return Training.utils.normalizeLink(data[keys[i]]);
        }
    }

    if (data.relativePath) {
        return Training.utils.normalizeLink(data.relativePath);
    }

    return '';
};

Training.utils.getResultData = function(action) {
    if (!action) {
        return {};
    }
    if (action.result) {
        return action.result.object || action.result.data || action.result;
    }
    if (action.object || action.data) {
        return action.object || action.data;
    }
    return action;
};

Training.utils.getSelectedRecordsFromSm = function(sm) {
    if (!sm) {
        return [];
    }
    if (sm.getSelections) {
        return sm.getSelections() || [];
    }
    if (sm.getSelected) {
        var rec = sm.getSelected();
        return rec ? [rec] : [];
    }
    return [];
};

Training.utils.getSelectedIds = function(grid) {
    var ids = [];
    Ext.each(Training.utils.getSelectedRecords(grid), function(rec) {
        var id = parseInt(Training.utils.getRecordValue(rec, 'id'), 10) || 0;
        if (id) {
            ids.push(id);
        }
    });
    return ids;
};

Training.utils.getMediaSource = function(source) {
    source = parseInt(source || Training.config.media_source || MODx.config['default_media_source'] || 3, 10) || 3;
    return source;
};

Training.utils.getBrowserTreePath = function(node) {
    if (!node) {
        return '';
    }
    if (node.id == '/') {
        return '';
    }
    if (node.attributes && typeof node.attributes.path !== 'undefined') {
        return node.attributes.path + '/';
    }
    return '';
};



Training.utils._fileBrowser = Training.utils._fileBrowser || null;
Training.utils._fileBrowserConfig = Training.utils._fileBrowserConfig || null;

Training.utils._cleanupBrowserDom = function(browser) {
    if (!browser || !browser.win || !browser.win.getEl) {
        return;
    }

    try {
        var el = browser.win.getEl();
        if (!el || !el.dom) {
            return;
        }

        if (el.dom.querySelectorAll) {
            var inputs = el.dom.querySelectorAll('input[type="file"]');
            for (var i = 0; i < inputs.length; i++) {
                try {
                    inputs[i].value = '';
                } catch (e) {}
            }
        }
    } catch (e) {}
};

Training.utils.destroyFileBrowser = function(browser) {
    browser = browser || Training.utils._fileBrowser || null;
    if (!browser) {
        Training.utils._fileBrowser = null;
        Training.utils._fileBrowserConfig = null;
        return;
    }

    try {
        if (browser.win && browser.win.hide) {
            browser.win.hide();
        }
    } catch (e) {}

    try {
        if (browser.win && browser.win.destroy) {
            browser.win.destroy();
        }
    } catch (e) {}

    try {
        if (browser.destroy) {
            browser.destroy();
        }
    } catch (e) {}

    Training.utils._fileBrowser = null;
    Training.utils._fileBrowserConfig = null;
};

Training.utils._applyBrowserSelection = function(browser, payload, originalData) {
    var config = Training.utils._fileBrowserConfig || null;
    if (!config) {
        return;
    }

    var path = Training.utils.extractBrowserPath(payload);
    if (!path) {
        path = Training.utils.extractBrowserPath(originalData);
    }

    if (path && typeof config.onSelect === 'function') {
        config.onSelect.call(config.scope || browser || this, path, originalData || payload);
    }

    Training.utils._cleanupBrowserDom(browser);

    try {
        if (browser && browser.win && browser.win.hide) {
            browser.win.hide();
        }
    } catch (e) {}
};

Training.utils.openFileBrowser = function(config) {
    config = config || {};

    var normalizedConfig = {
        source: Training.utils.getMediaSource(config.source),
        allowedFileTypes: config.allowedFileTypes || '',
        openTo: config.openTo || '',
        rootId: config.rootId || '/',
        wctx: config.wctx || MODx.config['default_context'] || 'web',
        scope: config.scope || this,
        onSelect: config.onSelect || Ext.emptyFn
    };

    Training.utils._fileBrowserConfig = normalizedConfig;

    var browser = Training.utils._fileBrowser;

    if (!browser || !browser.win) {
        browser = MODx.load({
            xtype: 'modx-browser',
            id: 'training-file-browser',
            multiple: false,
            source: normalizedConfig.source,
            rootVisible: false,
            allowedFileTypes: normalizedConfig.allowedFileTypes,
            wctx: normalizedConfig.wctx,
            openTo: normalizedConfig.openTo,
            rootId: normalizedConfig.rootId,
            hideSourceCombo: true,
            hideFiles: false,
            listeners: {
                select: {
                    fn: function(data) {
                        Training.utils._applyBrowserSelection(browser, data, data);
                    },
                    scope: this
                }
            }
        });

        if (!browser) {
            MODx.msg.alert('Ошибка', 'Не удалось открыть браузер файлов MODX');
            return null;
        }

        Training.utils._fileBrowser = browser;

        if (browser.win) {
            browser.win.closeAction = 'hide';

            if (browser.win.on) {
                browser.win.on('show', function() {
                    Training.utils._cleanupBrowserDom(browser);
                });
                browser.win.on('hide', function() {
                    Training.utils._cleanupBrowserDom(browser);
                });
                browser.win.on('close', function() {
                    Training.utils._cleanupBrowserDom(browser);
                });
            }

            if (browser.win.tree && browser.win.tree.on) {
                browser.win.tree.on('dblclick', function(node) {
                    var path = Training.utils.getBrowserTreePath(node);
                    if (path) {
                        Training.utils._applyBrowserSelection(browser, path, node);
                    }
                });
            }
        }
    }

    try {
        if (browser.config) {
            browser.config.source = normalizedConfig.source;
            browser.config.allowedFileTypes = normalizedConfig.allowedFileTypes;
            browser.config.openTo = normalizedConfig.openTo;
            browser.config.rootId = normalizedConfig.rootId;
            browser.config.wctx = normalizedConfig.wctx;
        }

        if (browser.source !== undefined) {
            browser.source = normalizedConfig.source;
        }
        if (browser.allowedFileTypes !== undefined) {
            browser.allowedFileTypes = normalizedConfig.allowedFileTypes;
        }
        if (browser.openTo !== undefined) {
            browser.openTo = normalizedConfig.openTo;
        }
        if (browser.rootId !== undefined) {
            browser.rootId = normalizedConfig.rootId;
        }
        if (browser.wctx !== undefined) {
            browser.wctx = normalizedConfig.wctx;
        }

        if (browser.win) {
            if (browser.win.show) {
                browser.win.show();
            }
            if (browser.win.toFront) {
                browser.win.toFront();
            }
        } else if (browser.show) {
            browser.show();
        }

        Training.utils._cleanupBrowserDom(browser);
        return browser;
    } catch (e) {
        Training.utils.destroyFileBrowser(browser);
        MODx.msg.alert('Ошибка', 'Не удалось открыть браузер файлов MODX');
        return null;
    }
};

Training.utils.getAllowedFileTypesForField = function(fieldName) {
    fieldName = String(fieldName || '').toLowerCase();
    if (fieldName.indexOf('presentation') !== -1) {
        return 'ppt,pptx,pdf';
    }
    if (fieldName === 'source_video' || fieldName === 'file_path') {
        return 'mkv,mp4,m3u8,mov,avi,webm';
    }
    if (fieldName === 'image') {
        return 'jpg,jpeg,png,webp,gif';
    }
    return '';
};

Training.utils.openPathBrowser = function(field, cfg) {
    cfg = cfg || {};
    if (!field) {
        return false;
    }
    var fieldName = field.name || cfg.fieldName || '';
    return Training.utils.openFileBrowser({
        source: cfg.source || Training.config.media_source || 3,
        allowedFileTypes: cfg.allowedFileTypes || Training.utils.getAllowedFileTypesForField(fieldName),
        openTo: cfg.openTo || '',
        scope: cfg.scope || field,
        onSelect: function(path, data) {
            if (field.setValue) {
                field.setValue(path || '');
            }
            if (typeof cfg.onSelect === 'function') {
                cfg.onSelect.call(cfg.scope || field, path, data, field);
            }
        }
    });
};

Training.utils.buildUrl = function(paramsToSet, paramsToRemove) {
    var url = new URL(window.location.href);
    paramsToSet = paramsToSet || {};
    paramsToRemove = paramsToRemove || [];

    for (var key in paramsToSet) {
        if (paramsToSet.hasOwnProperty(key)) {
            url.searchParams.set(key, paramsToSet[key]);
        }
    }

    Ext.each(paramsToRemove, function(param) {
        url.searchParams.delete(param);
    });

    return url.toString();
};

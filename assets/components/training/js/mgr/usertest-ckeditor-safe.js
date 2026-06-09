(function (w, d) {
    'use strict';

    if (w.__trainingUserTestCkeditorSafeV2) {
        return;
    }

    w.__trainingUserTestCkeditorSafeV2 = true;

    function getRawElement(element) {
        if (!element) {
            return null;
        }

        if (typeof element === 'string') {
            return d.getElementById(element);
        }

        if (element.$) {
            return element.$;
        }

        if (element.dom) {
            return element.dom;
        }

        return element.nodeType ? element : null;
    }

    function getEditorByElement(ckeditor, element) {
        var raw = getRawElement(element);
        var editor = null;

        if (!ckeditor || !raw) {
            return null;
        }

        try {
            if (ckeditor.dom && ckeditor.dom.element) {
                var domEl = ckeditor.dom.element.get(raw);
                if (domEl && typeof domEl.getEditor === 'function') {
                    editor = domEl.getEditor();
                }
            }
        } catch (e) {}

        if (!editor && raw.id && ckeditor.instances && ckeditor.instances[raw.id]) {
            editor = ckeditor.instances[raw.id];
        }

        return editor || null;
    }

    function destroyEditor(editor) {
        if (!editor) {
            return;
        }

        try {
            if (editor.status !== 'destroyed') {
                editor.destroy(true);
            }
        } catch (e) {}

        try {
            if (editor.name && w.CKEDITOR && w.CKEDITOR.instances && w.CKEDITOR.instances[editor.name]) {
                delete w.CKEDITOR.instances[editor.name];
            }
        } catch (e) {}
    }

    function cleanupElement(ckeditor, element) {
        var raw = getRawElement(element);
        var editor = getEditorByElement(ckeditor, element);

        destroyEditor(editor);

        if (raw && raw.id && ckeditor && ckeditor.instances && ckeditor.instances[raw.id]) {
            destroyEditor(ckeditor.instances[raw.id]);
        }
    }

    function isAlreadyAttachedError(error) {
        var text = '';

        try {
            text = String(error && (error.message || error.name || error));
        } catch (e) {}

        return text.indexOf('already attached') !== -1 ||
            text.indexOf('editor-element-conflict') !== -1 ||
            text.indexOf('provided element') !== -1;
    }

    function patchReplace(ckeditor) {
        if (!ckeditor || ckeditor.__trainingSafeReplaceV2Installed || typeof ckeditor.replace !== 'function') {
            return;
        }

        var originalReplace = ckeditor.replace;

        ckeditor.replace = function (element, config) {
            var existing = getEditorByElement(ckeditor, element);

            if (existing) {
                cleanupElement(ckeditor, element);
            }

            try {
                return originalReplace.call(this, element, config);
            } catch (error) {
                if (!isAlreadyAttachedError(error)) {
                    throw error;
                }

                cleanupElement(ckeditor, element);

                try {
                    return originalReplace.call(this, element, config);
                } catch (secondError) {
                    var fallback = getEditorByElement(ckeditor, element);
                    if (fallback) {
                        return fallback;
                    }
                    throw secondError;
                }
            }
        };

        ckeditor.__trainingSafeReplaceV2Installed = true;
    }

    function install() {
        if (!w.CKEDITOR) {
            return;
        }

        patchReplace(w.CKEDITOR);
    }

    install();

    var timer = w.setInterval(function () {
        install();
        if (w.CKEDITOR && w.CKEDITOR.__trainingSafeReplaceV2Installed) {
            w.clearInterval(timer);
        }
    }, 50);

    w.setTimeout(function () {
        if (timer) {
            w.clearInterval(timer);
        }
    }, 10000);
})(window, document);

(function() {
    'use strict';

    function resultData(response) {
        var data = {};
        try {
            data = Ext.decode(response.responseText || '{}') || {};
        } catch (e) {}
        return data.object || data.results || data || {};
    }

    function patchCertificates() {
        if (!window.Ext || !window.Training || !Training.form || !Training.grid
            || !Training.form.CourseCertificate || !Training.grid.CourseCertificates) {
            window.setTimeout(patchCertificates, 25);
            return;
        }

        if (Training.__certificateReissuePatchApplied) {
            return;
        }
        Training.__certificateReissuePatchApplied = true;

        Ext.override(Training.form.CourseCertificate, {
            generateAll: function() {
                this.getEl().mask('Генерируем сертификаты...');
                MODx.Ajax.request({
                    url: Training.config.connector_url,
                    params: {
                        action: 'mgr/course/certificate/generate',
                        course_id: this.courseId,
                        force: 0
                    },
                    listeners: {
                        success: {fn: function(response) {
                            this.getEl().unmask();
                            var grid = Ext.getCmp('training-grid-course-certificates');
                            if (grid) {
                                grid.refresh();
                            }
                            var data = Training.utils.getResultData(response);
                            MODx.msg.alert('Готово', 'Сгенерировано новых сертификатов: ' + (data.generated_count || 0));
                        }, scope: this},
                        failure: {fn: function(response) {
                            this.getEl().unmask();
                            MODx.msg.alert('Ошибка', (response && response.message) ? response.message : 'Не удалось сгенерировать сертификаты');
                        }, scope: this}
                    }
                });
            }
        });

        Ext.override(Training.grid.CourseCertificates, {
            initComponent: function() {
                if (Ext.isArray(this.tbar) && !this.__certificateReissueButtonAdded) {
                    var insertAt = -1;
                    Ext.each(this.tbar, function(item, index) {
                        if (item && item.text === 'Сгенерировать выбранным') {
                            insertAt = index + 1;
                            return false;
                        }
                    });

                    if (insertAt > -1) {
                        this.tbar.splice(insertAt, 0, {
                            text: 'Перегенерировать выбранные',
                            minWidth: 210,
                            handler: function() {
                                var ids = this.getSelectedUserIds();
                                if (!ids.length) {
                                    MODx.msg.alert('Сертификаты', 'Выберите пользователей');
                                    return;
                                }
                                this.reissue(ids);
                            },
                            scope: this
                        });
                    }

                    this.__certificateReissueButtonAdded = true;
                }

                Training.grid.CourseCertificates.superclass.initComponent.call(this);
            },

            generate: function(ids) {
                this.getEl().mask('Генерация сертификатов...');
                Ext.Ajax.request({
                    url: Training.config.connector_url,
                    params: {
                        action: 'mgr/course/certificate/generate',
                        course_id: this.courseId,
                        force: 0,
                        user_ids: ids.join(',')
                    },
                    success: function(response) {
                        this.getEl().unmask();
                        this.refresh();
                        var data = resultData(response);
                        MODx.msg.alert('Готово', 'Сгенерировано новых сертификатов: ' + (data.generated_count || 0));
                    },
                    failure: function(response) {
                        this.getEl().unmask();
                        var data = resultData(response);
                        MODx.msg.alert('Ошибка', data.message || 'Не удалось сгенерировать сертификаты');
                    },
                    scope: this
                });
            },

            reissue: function(ids) {
                this.getEl().mask('Перегенерация сертификатов...');
                Ext.Ajax.request({
                    url: Training.config.connector_url,
                    params: {
                        action: 'mgr/course/certificate/reissue',
                        course_id: this.courseId,
                        user_ids: ids.join(',')
                    },
                    success: function(response) {
                        this.getEl().unmask();
                        this.refresh();
                        var data = resultData(response);
                        MODx.msg.alert('Готово', 'Перегенерировано сертификатов: ' + (data.reissued_count || 0) + '. Пропущено: ' + (data.skipped_count || 0));
                    },
                    failure: function(response) {
                        this.getEl().unmask();
                        var data = resultData(response);
                        MODx.msg.alert('Ошибка', data.message || 'Не удалось перегенерировать сертификаты');
                    },
                    scope: this
                });
            }
        });
    }

    Ext.onReady(patchCertificates);
}());

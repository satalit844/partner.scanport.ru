<?php
switch ($modx->event->name) {
    case "OnMODXInit":
        $map = array(
            'modUser' => array(
                'fields' => array(
                    'user_1c' => 0,
                    'personal_data' => 1,
                    'partner_lic' => 0,
                    'webinar' => '',
                    'partner_events' => '',
                    'news' => '',
                    'software_updates' => '',
                    'api' => 0,
                ),
                'fieldMeta' => array(
                    'api' => array(
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'phptype' => 'boolean',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'default' => 0,
                    ),
                    'user_1c' => array(
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'phptype' => 'boolean',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'default' => 1,
                    ),
                    'personal_data' => array(
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'phptype' => 'boolean',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'default' => 1,
                    ),
                    'partner_lic' => array(
                        'dbtype' => 'tinyint',
                        'precision' => '1',
                        'phptype' => 'boolean',
                        'attributes' => 'unsigned',
                        'null' => false,
                        'default' => 0,
                    ),
                    'webinar' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'partner_events' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'news' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'software_updates' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                ),
            ),
            'modUserProfile' => array(
                'fields' => array(
                    'surname' => '',
                    'patronymic' => '',
                    'user_post' => '',
                    'field_inn' => '',
                    'field_list_inn' => '',
                    'field_company' => '',
                    'field_list_company' => '',
                    'rebeit' => '',
                    'field_ceil' => '',
                    'field_marketing' => '',
                    'field_sold' => '',
                    'field_summ' => '',
                    'field_iudiscount' => '',
                    'field_nfr' => '',
                    'notresident' => '',
                ),
                'fieldMeta' => array(
                    'surname' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'patronymic' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'user_post' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_inn' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_list_inn' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_company' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_list_company' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'rebeit' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_ceil' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_marketing' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_sold' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_summ' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_iudiscount' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'field_nfr' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    'notresident' => array(
                        'dbtype' => 'varchar',
                        'precision' => '255',
                        'phptype' => 'string',
                        'null' => false,
                    ),
                    
                ),
            ),
        );

        foreach ($map as $class => $data) {
            $modx->loadClass($class);

            foreach ($data as $tmp => $fields) {
                if ($tmp == 'fields') {
                    foreach ($fields as $field => $value) {
                        foreach (array('fields', 'fieldMeta', 'indexes') as $key) {
                            if (isset($data[$key][$field])) {
                                $modx->map[$class][$key][$field] = $data[$key][$field];
                            }
                        }
                    }
                } elseif ($tmp == 'composites' || $tmp == 'aggregates') {
                    foreach ($fields as $alias => $relation) {
                        if (!isset($modx->map[$class][$tmp][$alias])) {
                            $modx->map[$class][$tmp][$alias] = $relation;
                        }
                    }
                }
            }
        }
        break;
    
    case "OnUserFormPrerender":
        if (!isset($user) || $user->get('id') < 1) {
            return;
        }

        if (!$modx->getCount('modPlugin', array('name' => 'AjaxManager', 'disabled' => false))) {
            $data['user_1c'] = $user->user_1c ? 'true' : 'false';
            $data['api'] = $user->api ? 'true' : 'false';
            $data['personal_data'] = $user->personal_data ? 'true' : 'false';
            $data['partner_lic'] = $user->partner_lic ? 'true' : 'false';
            $data['webinar'] = htmlspecialchars($user->webinar);
            $data['partner_events'] = htmlspecialchars($user->partner_events);
            $data['news'] = htmlspecialchars($user->news);
            $data['software_updates'] = htmlspecialchars($user->software_updates);
            
            $data['surname'] = htmlspecialchars($user->Profile->surname);
            $data['patronymic'] = htmlspecialchars($user->Profile->patronymic);
            $data['user_post'] = htmlspecialchars($user->Profile->user_post);
            $data['field_inn'] = htmlspecialchars($user->Profile->field_inn);
            $data['field_list_inn'] = htmlspecialchars($user->Profile->field_list_inn);
            $data['field_company'] = htmlspecialchars($user->Profile->field_company);
            $data['field_list_company'] = htmlspecialchars($user->Profile->field_list_company);
            $data['rebeit'] = htmlspecialchars($user->Profile->rebeit);
            $data['field_ceil'] = htmlspecialchars($user->Profile->field_ceil);
            $data['field_marketing'] = htmlspecialchars($user->Profile->field_marketing);
            $data['field_sold'] = htmlspecialchars($user->Profile->field_sold);
            $data['field_summ'] = htmlspecialchars($user->Profile->field_summ);
            $data['field_iudiscount'] = htmlspecialchars($user->Profile->field_iudiscount);
            $data['field_nfr'] = htmlspecialchars($user->Profile->field_nfr);
            $data['notresident'] = htmlspecialchars($user->Profile->notresident);
            
            	
            $modx->controller->addHtml("
                <script type='text/javascript'>
                    Ext.ComponentMgr.onAvailable('modx-user-tabs', function() {
                        this.on('beforerender', function() {
                            var leftCol = this.items.items[0].items.items[0].items.items[0];
                            var rightCol = this.items.items[0].items.items[0].items.items[1];
                            leftCol.items.insert(4, 'modx-user-surname', new Ext.form.TextField({
                                id: 'modx-user-surname',
                                name: 'surname',
                                fieldLabel: 'Фамилия(surname)',
                                description: '[[+surname]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['surname']}',
                            }));
                            leftCol.items.insert(5, 'modx-user-patronymic', new Ext.form.TextField({
                                id: 'modx-user-patronymic',
                                name: 'patronymic',
                                fieldLabel: 'Отчество(patronymic)',
                                description: '[[+patronymic]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['patronymic']}',
                            }));
                            leftCol.items.insert(6, 'modx-user-user_post', new Ext.form.TextField({
                                id: 'modx-user-user_post',
                                name: 'user_post',
                                fieldLabel: 'Должность(user_post)',
                                description: '[[+user_post]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['user_post']}',
                            }));
                            leftCol.items.insert(7, 'modx-user-field_inn', new Ext.form.TextField({
                                id: 'modx-user-field_inn',
                                name: 'field_inn',
                                fieldLabel: 'Выбранный ИНН(field_inn)',
                                description: '[[+field_inn]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_inn']}',
                            }));
                            leftCol.items.insert(8, 'modx-user-field_company', new Ext.form.TextField({
                                id: 'modx-user-field_company',
                                name: 'field_company',
                                fieldLabel: 'Выбранная компания(field_company)',
                                description: '[[+field_company]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_company']}',
                            }));
                            leftCol.items.insert(9, 'modx-user-field_list_inn', new Ext.form.TextField({
                                id: 'modx-user-field_list_inn',
                                name: 'field_list_inn',
                                fieldLabel: 'Список ИНН(field_list_inn)',
                                description: '[[+field_list_inn]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_list_inn']}',
                            }));
                            leftCol.items.insert(10, 'modx-user-field_iudiscount', new Ext.form.TextField({
                                id: 'modx-user-field_iudiscount',
                                name: 'field_iudiscount',
                                fieldLabel: 'Индивидуальная скидка(field_iudiscount)',
                                description: '[[+field_iudiscount]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_iudiscount']}',
                            }));
                            leftCol.items.insert(11, 'modx-user-field_list_company', new Ext.form.TextField({
                                id: 'modx-user-field_list_company',
                                name: 'field_list_company',
                                fieldLabel: 'Список компаний(field_list_company)',
                                description: '[[+field_list_company]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_list_company']}',
                            }));
                            leftCol.items.insert(12, 'modx-user-rebeit', new Ext.form.TextField({
                                id: 'modx-user-rebeit',
                                name: 'rebeit',
                                fieldLabel: 'До какой даты приобрести лицензии(rebeit)',
                                description: '[[+rebeit]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['rebeit']}',
                            }));
                            leftCol.items.insert(13, 'modx-user-field_ceil', new Ext.form.TextField({
                                id: 'modx-user-field_ceil',
                                name: 'field_ceil',
                                fieldLabel: 'Индивидуальная сумма приобретения лицензий(field_ceil)',
                                description: '[[+field_ceil]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_ceil']}',
                            }));
                            leftCol.items.insert(14, 'modx-user-field_marketing', new Ext.form.TextField({
                                id: 'modx-user-field_marketing',
                                name: 'field_marketing',
                                fieldLabel: 'Можно потратить на маркетинг(field_marketing)',
                                description: '[[+field_marketing]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_marketing']}',
                            }));
                            leftCol.items.insert(15, 'modx-user-field_sold', new Ext.form.TextField({
                                id: 'modx-user-field_sold',
                                name: 'field_sold',
                                fieldLabel: 'Уже потратили(field_sold)',
                                description: '[[+field_sold]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_sold']}',
                            }));
                            leftCol.items.insert(16, 'modx-user-field_summ', new Ext.form.TextField({
                                id: 'modx-user-field_summ',
                                name: 'field_summ',
                                fieldLabel: 'Сумма купленных лицензий(field_summ)',
                                description: '[[+field_summ]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_summ']}',
                            }));
                            leftCol.items.insert(17, 'modx-user-field_nfr', new Ext.form.TextField({
                                id: 'modx-user-field_nfr',
                                name: 'field_nfr',
                                fieldLabel: 'NFR-лицензии(field_nfr)',
                                description: '[[+field_так]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['field_nfr']}',
                            }));
                            leftCol.items.insert(18, 'modx-user-notresident', new Ext.form.TextField({
                                id: 'modx-user-notresident',
                                name: 'notresident',
                                fieldLabel: 'Нерезидент(notresident)',
                                description: '[[+notresident]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['notresident']}',
                            }));
                            rightCol.items.insert(5, 'modx-user-user_1c', new Ext.form.Checkbox({
                                id: 'modx-user-user_1c',
                                name: 'user_1c',
                                hideLabel: true,
                                boxLabel: 'Пользователь в 1c',
                                description: 'Является пользователем в 1с для data-mobile',
                                xtype: 'xcheckbox',
                                inputValue: 1,
                                listeners: {
                                    beforerender: function(that) {
                                        that.hiddenField = new Ext.Element(document.createElement('input')).set({
                                            type: 'hidden',
                                            name: that.name,
                                            value: 0,
                                        });
                                    },
                                    afterrender: function(that) {
                                        that.el.insertHtml('beforeBegin', that.hiddenField.dom.outerHTML);
                                    },
                                },
                                checked: {$data['user_1c']},
                            }));
                            rightCol.items.insert(6, 'modx-user-api', new Ext.form.Checkbox({
                                id: 'modx-user-api',
                                name: 'api',
                                hideLabel: true,
                                boxLabel: 'Доступ к API',
                                description: 'Имеет доступ к API',
                                xtype: 'xcheckbox',
                                inputValue: 1,
                                listeners: {
                                    beforerender: function(that) {
                                        that.hiddenField = new Ext.Element(document.createElement('input')).set({
                                            type: 'hidden',
                                            name: that.name,
                                            value: 0,
                                        });
                                    },
                                    afterrender: function(that) {
                                        that.el.insertHtml('beforeBegin', that.hiddenField.dom.outerHTML);
                                    },
                                },
                                checked: {$data['api']},
                            }));
                            rightCol.items.insert(7, 'modx-user-personal_data', new Ext.form.Checkbox({
                                id: 'modx-user-personal_data',
                                name: 'personal_data',
                                hideLabel: true,
                                boxLabel: 'Обработка данных',
                                description: '[[+personal_data]]',
                                xtype: 'xcheckbox',
                                inputValue: 1,
                                listeners: {
                                    beforerender: function(that) {
                                        that.hiddenField = new Ext.Element(document.createElement('input')).set({
                                            type: 'hidden',
                                            name: that.name,
                                            value: 0,
                                        });
                                    },
                                    afterrender: function(that) {
                                        that.el.insertHtml('beforeBegin', that.hiddenField.dom.outerHTML);
                                    },
                                },
                                checked: {$data['personal_data']},
                            }));
                            rightCol.items.insert(8, 'modx-user-partner_lic', new Ext.form.Checkbox({
                                id: 'modx-user-partner_lic',
                                name: 'partner_lic',
                                hideLabel: true,
                                boxLabel: 'Разрешить партнеру покупапть лицензию',
                                description: '[[+partner_lic]]',
                                xtype: 'xcheckbox',
                                inputValue: 1,
                                listeners: {
                                    beforerender: function(that) {
                                        that.hiddenField = new Ext.Element(document.createElement('input')).set({
                                            type: 'hidden',
                                            name: that.name,
                                            value: 0,
                                        });
                                    },
                                    afterrender: function(that) {
                                        that.el.insertHtml('beforeBegin', that.hiddenField.dom.outerHTML);
                                    },
                                },
                                checked: {$data['partner_lic']},
                            }));
                            rightCol.items.insert(0, 'modx-user-webinar', new Ext.form.TextField({
                                id: 'modx-user-webinar',
                                name: 'webinar',
                                fieldLabel: 'Подписка вебинары(webinar)',
                                description: '[[+webinar]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['webinar']}',
                            }));
                            rightCol.items.insert(1, 'modx-user-partner_events', new Ext.form.TextField({
                                id: 'modx-user-partner_events',
                                name: 'partner_events',
                                fieldLabel: 'Подписка партнерские мероприятия(partner_events)',
                                description: '[[+partner_events]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['partner_events']}',
                            }));
                            rightCol.items.insert(2, 'modx-user-news', new Ext.form.TextField({
                                id: 'modx-user-news',
                                name: 'news',
                                fieldLabel: 'Подписка на новости(news)',
                                description: '[[+news]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['news']}',
                            }));
                            rightCol.items.insert(3, 'modx-user-software_updates', new Ext.form.TextField({
                                id: 'modx-user-software_updates',
                                name: 'software_updates',
                                fieldLabel: 'Подписка на обновление ПО(software_updates)',
                                description: '[[+software_updates]]',
                                xtype: 'textfield',
                                anchor: '100%',
                                maxLength: 255,
                                value: '{$data['software_updates']}',
                            }));
                        });
                    });
                </script>
            ");
        }
        break;
}
UserTest.utils.renderBoolean = function (value) {
    return value
        ? String.format('<span class="green">{0}</span>', _('yes'))
        : String.format('<span class="red">{0}</span>', _('no'));
};

UserTest.utils.getMenu = function (actions, grid, selected) {
    var menu = [];
    var cls, icon, title, action;

    var has_delete = false;
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {
            continue;
        }

        var a = actions[i];
        if (!a['menu']) {
            if (a == '-') {
                menu.push('-');
            }
            continue;
        }
        else if (menu.length > 0 && !has_delete && (/^remove/i.test(a['action']) || /^delete/i.test(a['action']))) {
            menu.push('-');
            has_delete = true;
        }

        if (selected.length > 1) {
            if (!a['multiple']) {
                continue;
            }
            else if (typeof(a['multiple']) == 'string') {
                a['title'] = a['multiple'];
            }
        }

        icon = a['icon'] ? a['icon'] : '';
        if (typeof(a['cls']) == 'object') {
            if (typeof(a['cls']['menu']) != 'undefined') {
                icon += ' ' + a['cls']['menu'];
            }
        }
        else {
            cls = a['cls'] ? a['cls'] : '';
        }
        title = a['title'] ? a['title'] : a['title'];
        action = a['action'] ? grid[a['action']] : '';

        menu.push({
            handler: action,
            text: String.format(
                '<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
                cls, icon, title
            ),
            scope: grid
        });
    }

    return menu;
};

UserTest.utils.renderActions = function (value, props, row) {
    var res = [];
    var cls, icon, title, action, item;
    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {
            continue;
        }
        var a = row.data.actions[i];
        if (!a['button']) {
            continue;
        }

        icon = a['icon'] ? a['icon'] : '';
        if (typeof(a['cls']) == 'object') {
            if (typeof(a['cls']['button']) != 'undefined') {
                icon += ' ' + a['cls']['button'];
            }
        }
        else {
            cls = a['cls'] ? a['cls'] : '';
        }
        action = a['action'] ? a['action'] : '';
        title = a['title'] ? a['title'] : '';

        item = String.format(
            '<li class="{0}"><button class="btn btn-default {1}" action="{2}" title="{3}"></button></li>',
            cls, icon, action, title
        );

        res.push(item);
    }

    return String.format(
        '<ul class="usertest-row-actions">{0}</ul>',
        res.join('')
    );
};
UserTest.utils.userLink = function(val,cell,row) {
	if (!row.data['user_id'] || !row.data['reg_user_name'] ) {return '';}
	var action = MODx.action ? MODx.action['security/user/update'] : 'security/user/update';
	var url = 'index.php?a='+action+'&id='+row.data['user_id'];
	//console.info(row);
	return '<a href="' + url + '" target="_blank">' + row.data['reg_user_name'] + '</a>';
};
UserTest.utils.question_type = function(val,cell,row) {
	var type;
	switch(parseInt(row.data['type'])){
		case 1:
			type = _('usertest_type_questions_radiobutton');
			break;
		case 2:
			type = _('usertest_type_questions_checkbox');
			break;
		case 3:
			type = _('usertest_type_questions_simple_text');
			break;
		case 4:
			type = _('usertest_type_questions_open_question');
			break;
		case 5:
			type = _('usertest_type_questions_comparison_simple');
			break;
		case 6:
			type = _('usertest_type_questions_combined_option');
			break;
		case 7:
			type = _('usertest_type_questions_table_checkbox');
			break;
		case 8:
			type = _('usertest_type_questions_table_input_text');
			break;
		case 9:
			type = _('usertest_type_questions_select_in_text');
			break;
		case 10:
			type = _('usertest_type_questions_combined_radiobutton');
			break;
		case 11:
			type = _('usertest_type_questions_table_procent');
			break;
		case 12:
			type = _('usertest_type_questions_opros_san');
			break;
	}
	/* [1 , "Одиночный выбор"],
	[2 , "Множественный выбор"],
	[3 , "Простой текст"],
	[4 , "Открытый вопрос"],
	[5 , "На сопоставление. Простой"] */
	return type;
};
UserTest.utils.file_type = function(val,cell,row) {
	var type;
	switch(row.data['type_file']){
		case 0:
			type = "Без файла";
			break;
		case 1:
			type = "Картинка";
			break;
		case 2:
			type = "Видео";
			break;
		case 3:
			type = "Звук";
			break;
	}
	/* [0 , "Без файла"],
				[1 , "Картинка"],
				[2 , "Видео"],
				[3 , "Звук"] */
	return type;
};
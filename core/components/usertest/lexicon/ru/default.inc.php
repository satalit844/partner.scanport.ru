<?php
include_once 'setting.inc.php';

$_lang['usertest'] = 'UserTest';
$_lang['usertest_menu_desc'] = 'Тесты пользователей.';
$_lang['usertest_intro_msg'] = 'Для вывода теста используйте сниппет UserTest. Например [[!UserTest? &id=`1`]]. Этот пример выведет тест с id = 1.';

$_lang['usertest_tests'] = 'Тесты';
$_lang['usertest_results'] = 'Результаты тестов';
$_lang['usertest_test'] = 'Тест';
$_lang['usertest_groups'] = 'Группы тестов';
$_lang['usertest_group'] = 'Группа тестов';
$_lang['usertest_category'] = 'Категории вопросов';
$_lang['usertest_userresult_category'] = 'Категории';
$_lang['usertest_invite'] = 'Приглашения';

$_lang['usertest_test_create'] = 'Создать тест';
$_lang['usertest_test_update'] = 'Изменить тест';
$_lang['usertest_test_enable'] = 'Включить';
$_lang['usertest_tests_enable'] = 'Включить';
$_lang['usertest_test_disable'] = 'Отключить';
$_lang['usertest_tests_disable'] = 'Отключить';
$_lang['usertest_test_remove'] = 'Удалить тест';
$_lang['usertest_tests_remove'] = 'Удалить тесты';
$_lang['usertest_test_remove_confirm'] = 'Вы уверены, что хотите удалить этот тест?';
$_lang['usertest_tests_remove_confirm'] = 'Вы уверены, что хотите удалить эти тесты?';
$_lang['usertest_test_active'] = 'Включено';
$_lang['usertest_test_questions'] = 'Вопросы';
$_lang['usertest_test_variants'] = 'Управление результатами';

$_lang['usertest_test_tab_main'] = 'Основные';
$_lang['usertest_test_tab_add'] = 'Доп. настройки';

$_lang['usertest_test_customer'] = 'Заказчик';
$_lang['usertest_test_appeal'] = 'Обращение к пользователю';
$_lang['usertest_test_instruction'] = 'Инструкция';
$_lang['usertest_test_use_block_q_number'] = 'Включить блок перехода к вопросу';
$_lang['usertest_test_pub_date'] = 'Дата публикации';
$_lang['usertest_test_unpub_date'] = 'Дата снятия с публикации';
$_lang['usertest_parent'] = 'Вопрос-исходный';

$_lang['usertest_test_count_questions'] = 'Кол-во вопросов в тесте.(Выбираются случайным образом из заданных для теста вопросов. 0 все вопросы подряд.)';
$_lang['usertest_test_count_questions_on_page'] = 'Кол-во вопросов на странице. (0-все вопросы.)';
$_lang['usertest_test_count_time_test'] = 'Время теста в секундах. (0-время не ограниченно.)';
$_lang['usertest_test_type'] = 'Результаты теста расчитываются:';
$_lang['usertest_test_count_test_answer'] = 'Ограничить число попыток сдать тест. (0-неограничено.)';

$_lang['usertest_item_err_name'] = 'Вы должны указать имя теста.';
$_lang['usertest_item_err_ae'] = 'Тест с таким именем уже существует.';
$_lang['usertest_item_err_nf'] = 'Тест не найден.';
$_lang['usertest_item_err_ns'] = 'Тест не указан.';
$_lang['usertest_item_err_remove'] = 'Ошибка при удалении Теста.';
$_lang['usertest_item_err_save'] = 'Ошибка при сохранении Теста.';

//$_lang['usertest_items'] = 'Предметы';
$_lang['usertest_item_id'] = 'Id';
$_lang['usertest_item_name'] = 'Название';
$_lang['usertest_item_description'] = 'Описание';
$_lang['usertest_item_active'] = 'Активно';
$_lang['usertest_use_category'] = 'Включить категории вопросов';
$_lang['usertest_category_name'] = 'Категория';

$_lang['usertest_menuindex'] = 'menuindex';
$_lang['usertest_question_type'] = 'Тип вопроса';
$_lang['usertest_type_file'] = 'Тип файла';
$_lang['usertest_file'] = 'Файл';

$_lang['usertest_test_id'] = 'Id теста';
$_lang['usertest_question'] = 'Вопрос';
$_lang['usertest_question_create'] = 'Добавить вопрос';
$_lang['usertest_question_clone'] = 'Копировать вопрос';

$_lang['usertest_question_update'] = 'Изменить вопрос';
$_lang['usertest_question_remove'] = 'Удалить вопрос';
$_lang['usertest_questions_remove'] = 'Удалить вопросы';
$_lang['usertest_question_remove_confirm'] = 'Вы уверены, что хотите удалить этот вопрос?';
$_lang['usertest_questions_remove_confirm'] = 'Вы уверены, что хотите удалить эти вопросы?';

$_lang['usertest_question_err_nf'] = 'Вопрос не найден.';
$_lang['usertest_question_err_ns'] = 'Вопрос не указан.';
$_lang['usertest_question_err_remove'] = 'Ошибка при удалении Вопроса.';
$_lang['usertest_question_err_save'] = 'Ошибка при сохранении Вопроса.';

$_lang['usertest_answers'] = 'Ответы';
$_lang['usertest_question_id'] = 'Id вопроса';
$_lang['usertest_answer_id'] = 'Id ответа';
$_lang['usertest_answer'] = 'Ответ';
$_lang['usertest_answer_img'] = 'Фото ответа';
$_lang['usertest_point'] = 'Баллы';
$_lang['usertest_max_point'] = 'MAX балл';

$_lang['usertest_answer_right'] = 'Правильный ответ';
$_lang['usertest_answer_type5'] = 'Сопоставления';
$_lang['usertest_answer_type5_point'] = 'Начислять баллы за';
$_lang['usertest_answer_type5_type_point1'] = 'правильный ответ';
$_lang['usertest_answer_type5_type_point2'] = 'каждое совпадение';

$_lang['usertest_answer_create'] = 'Добавить ответ';
$_lang['usertest_answer_update'] = 'Изменить ответ';
$_lang['usertest_answer_remove'] = 'Удалить ответ';
$_lang['usertest_answers_remove'] = 'Удалить ответы';
$_lang['usertest_answer_remove_confirm'] = 'Вы уверены, что хотите удалить этот ответ?';
$_lang['usertest_answers_remove_confirm'] = 'Вы уверены, что хотите удалить эти ответы?';

$_lang['usertest_answer_err_nf'] = 'Ответ не найден.';
$_lang['usertest_answer_err_ns'] = 'Ответ не указан.';
$_lang['usertest_answer_err_remove'] = 'Ошибка при удалении Ответа.';
$_lang['usertest_answer_err_save'] = 'Ошибка при сохранении Ответа.';

$_lang['usertest_passed'] = 'Тест сдан';
$_lang['usertest_start_point'] = 'Мин. кол-во баллов';
$_lang['usertest_end_point'] = 'Мак. кол-во баллов';
$_lang['usertest_result'] = 'Текст результата';

$_lang['usertest_variant'] = 'Варианты результата теста';
$_lang['usertest_variant_create'] = 'Добавить вариант';
$_lang['usertest_variant_update'] = 'Изменить вариант';
$_lang['usertest_variant_remove'] = 'Удалить вариант';
$_lang['usertest_variants_remove'] = 'Удалить варианты';
$_lang['usertest_variant_remove_confirm'] = 'Вы уверены, что хотите удалить этот вариант?';
$_lang['usertest_variants_remove_confirm'] = 'Вы уверены, что хотите удалить эти варианты?';

$_lang['usertest_variant_err_nf'] = 'Вариант не найден.';
$_lang['usertest_variant_err_ns'] = 'Вариант не указан.';
$_lang['usertest_variant_err_remove'] = 'Ошибка при удалении варианта.';
$_lang['usertest_variant_err_save'] = 'Ошибка при сохранении варианта.';

$_lang['usertest_userresult_update'] = 'Изменить результат';
$_lang['usertest_useranswer_update'] = 'Проверить ответ';
$_lang['usertest_comment'] = 'Комментарий преподавателя';

$_lang['usertest_userresult_test_name'] = 'Тест';
$_lang['usertest_userresult_reg_user_name'] = 'Пользователь';
$_lang['usertest_userresult_user_name'] = 'Имя';
$_lang['usertest_userresult_user_email'] = 'Email';
$_lang['usertest_userresult_date'] = 'Дата';
$_lang['usertest_userresult_test_time'] = 'Время теста';
$_lang['usertest_userresult_variant_id'] = 'Id результата';
$_lang['usertest_userresult_variant'] = 'Результат';
$_lang['usertest_userresult_status'] = 'Статус';

$_lang['usertest_userresult_test'] = 'Поиск по имени теста или его id';
$_lang['usertest_userresult_user'] = 'Поиск по имени пользователя';

$_lang['usertest_userresult_remove'] = 'Удалить результат';
$_lang['usertest_userresults_remove'] = 'Удалить результаты';
$_lang['usertest_userresult_remove_confirm'] = 'Вы уверены, что хотите удалить этот результат?';
$_lang['usertest_userresults_remove_confirm'] = 'Вы уверены, что хотите удалить эти результаты?';

$_lang['usertest_userresult_err_nf'] = 'Результат не найден.';
$_lang['usertest_userresult_err_ns'] = 'Результат не указан.';
$_lang['usertest_userresult_err_remove'] = 'Ошибка при удалении результата.';
$_lang['usertest_userresult_err_save'] = 'Ошибка при сохранении результата.';

$_lang['usertest_group_create'] = 'Создать группу';
$_lang['usertest_group_update'] = 'Изменить группу';
$_lang['usertest_group_remove'] = 'Удалить группу';
$_lang['usertest_groups_remove'] = 'Удалить группы';
$_lang['usertest_group_remove_confirm'] = 'Вы уверены, что хотите удалить эту группу?';
$_lang['usertest_groups_remove_confirm'] = 'Вы уверены, что хотите удалить эти группы?';

$_lang['usertest_grouplink_create'] = 'Добавить в группу';
$_lang['usertest_grouplink_remove'] = 'Удалить тест из группы';
$_lang['usertest_grouplinks_remove'] = 'Удалить тесты из группы';
$_lang['usertest_grouplink_remove_confirm'] = 'Вы уверены, что хотите удалить этот тест из группы?';
$_lang['usertest_grouplinks_remove_confirm'] = 'Вы уверены, что хотите удалить эти тесты из группы?';

$_lang['usertest_category_create'] = 'Создать категорию';
$_lang['usertest_category_update'] = 'Изменить категорию';
$_lang['usertest_category_remove'] = 'Удалить категорию';
$_lang['usertest_categorys_remove'] = 'Удалить категории';
$_lang['usertest_category_remove_confirm'] = 'Вы уверены, что хотите удалить эту категорию?';
$_lang['usertest_categorys_remove_confirm'] = 'Вы уверены, что хотите удалить эти категории?';

$_lang['usertest_group_err_ae'] = 'Тест уже в этой группе';

$_lang['usertest_group_id'] = 'Id группы';

$_lang['usertest_grid_search'] = 'Поиск';
$_lang['usertest_grid_actions'] = 'Действия';

$_lang['usertest_invite_imprort'] = 'Импорт';
$_lang['usertest_invite_export'] = 'Экспорт';
$_lang['usertest_invite_update'] = 'Изменить приглашение';
$_lang['usertest_invite_remove'] = 'Удалить приглашение';
$_lang['usertest_invites_remove'] = 'Удалить приглашения';
$_lang['usertest_invite_remove_confirm'] = 'Вы уверены, что хотите удалить это приглашение?';
$_lang['usertest_invites_remove_confirm'] = 'Вы уверены, что хотите удалить эти приглашения?';

$_lang['usertest_user_pass'] = 'Пароль';
$_lang['usertest_invite_url'] = 'Url';
$_lang['usertest_user_auth_code'] = 'Код';
$_lang['usertest_result_id'] = 'Id результата теста';

$_lang['usertest_invite_test_page_id'] = 'Id страницы теста';
$_lang['usertest_invite_auth_page_id'] = 'Id страницы авторизации приглашения. Где сниппет UserTestAuthInvites';
$_lang['usertest_invite_url_scheme'] = 'Формат url. http или https';
$_lang['usertest_invite_excel_file'] = 'Excel файл с email. 1 столбец email, 2 столбец имя пользователя.';
$_lang['usertest_invite_date_expired'] = 'Приглашение действительно до даты';
$_lang['usertest_invite_date_expired1'] = 'Действительно до';

$_lang['usertest_invite_subject'] = 'Вы приглашены на тест [[+test_name]]';
$_lang['usertest_invite_with_empty_test_subject'] = 'Вы пропустили тест [[+test_name]]';
$_lang['usertest_teacher_subject'] = 'Пользователь [[+user_name]] прошел тест [[+test_name]]';

//тип вопроса
$_lang['usertest_type_questions_radiobutton'] = "Одиночный выбор";
$_lang['usertest_type_questions_checkbox'] = "Множественный выбор";
$_lang['usertest_type_questions_simple_text'] = "Простой текст";
$_lang['usertest_type_questions_open_question'] = "Открытый вопрос";
$_lang['usertest_type_questions_comparison_simple'] = "На сопоставление. Простой";
$_lang['usertest_type_questions_combined_option'] = 'Комбинированный множественный выбор';
$_lang['usertest_type_questions_table_checkbox'] = 'Таблица чек-боксов';
$_lang['usertest_type_questions_table_input_text'] = 'Таблица текстовых полей';
$_lang['usertest_type_questions_select_in_text'] = 'Селекты в тексте';
$_lang['usertest_type_questions_combined_radiobutton'] = 'Комбинированный одиночный выбор';
$_lang['usertest_type_questions_table_procent'] = 'Таблица процентов';
$_lang['usertest_type_questions_opros_san'] = 'Опросник САН';

//тип файла
$_lang['usertest_type_file_no_file'] = "Без файла";
$_lang['usertest_type_file_picture'] = "Картинка";
$_lang['usertest_type_file_video'] = "Видео";
$_lang['usertest_type_file_audio'] = "Звук";

//21.02.2018
$_lang['usertest_item_create'] = 'Добавить';
$_lang['usertest_item_create2'] = 'Создать';
$_lang['usertest_item_update'] = 'Изменить';
$_lang['usertest_item_remove'] = 'Удалить запись';
$_lang['usertest_items_remove'] = 'Удалить записи';
$_lang['usertest_item_remove_confirm'] = 'Вы уверены, что хотите удалить эту запись?';
$_lang['usertest_items_remove_confirm'] = 'Вы уверены, что хотите удалить эти записи?';

$_lang['usertest_item_err_nf'] = 'Запись не найдена.';
$_lang['usertest_item_err_ns'] = 'Запись не указана.';
$_lang['usertest_item_err_remove'] = 'Ошибка при удалении записи.';
$_lang['usertest_item_err_save'] = 'Ошибка при сохранении записи.';

$_lang['usertest_variantsets'] = 'Наборы результатов теста';
$_lang['usertest_questions'] = 'База вопросов';

$_lang['usertest_question_select'] = 'Вставить вопрос';
$_lang['usertest_questions_select'] = 'Вставить вопросы';
$_lang['usertest_question_select_confirm'] = 'Вы хотите вставить вопрос?';
$_lang['usertest_question_err_dublicat'] = 'В этом тесте уже есть этот вопрос';


$_lang['usertest_question_copy'] = 'Копировать вопрос';
$_lang['usertest_questions_copy'] = 'Копировать вопросы';
$_lang['usertest_question_edit_link'] = 'Привязанные тесты';
$_lang['usertest_question_copy_confirm'] = 'Вы хотите скопировать вопрос?';

$_lang['usertest_question_search_ids'] = 'По IDs';

//01.03.2019
$_lang['usertest_test_question_link_err_test_id'] = 'Не задан тест!';
$_lang['usertest_test_question_link_err_queston_id'] = 'Не задан вопрос!';
$_lang['usertest_test_question_link_err_variant_id'] = 'Не задан вариант результата теста!';

//02.03.2019
$_lang['usertest_variant_set_id'] = 'ID набора';
$_lang['usertest_variantset'] = 'Набор результатов теста';

$_lang['usertest_variant_id'] = 'ID варианта';
$_lang['usertest_use_custom_point'] = 'Использовать баллы теста';

//26.03.2019
$_lang['usertest_invite_clear'] = 'Очистить';
$_lang['usertest_clear_invites_days'] = 'Введите число дней, старше которых очистить приглашения';

$_lang['usertest_random_answer'] = 'Ответы в случайном порядке';
$_lang['usertest_question_validate'] = 'Ответ обязателен';

$_lang['usertest_send_email_if_empty_test'] = 'Отправить email если тест не начат';
//23.04.2019
$_lang['usertest_export_import'] = 'Экспорт/Импорт';
$_lang['usertest_export'] = 'Экспорт';
$_lang['usertest_test_export'] = 'ID теста';
$_lang['usertest_test_export_desc'] = 'Для какого теста делать экспорт. Если пустое, то для всех тестов.';

$_lang['usertest_import'] = 'Импорт';
$_lang['usertest_test_export_file'] = 'Файл импорта';
$_lang['usertest_test_export_file_desc'] = 'Загрузите файл для импорта тестов.';
$_lang['usertest_test_empty_export_file'] = 'Загрузите файл для импорта тестов.';

//16.05.2019
$_lang['usertest_test_copy'] = 'Копировать тест';
$_lang['usertest_tests_copy'] = 'Копировать тесты';
$_lang['usertest_test_copy_confirm'] = 'Вы хотите скопировать тест?';

//09.06.2019 Опросник САН
$_lang['usertest_test_type'] = 'Тип теста';
//26.06.2019 
$_lang['usertest_test_ask_user_data'] = 'Запрашивать данные пользователя перед тестом';
//08.02.2022
$_lang['usertest_snippet_not_load_service'] = 'Не удалось загрузить сервис UserTest';//'Could not load UserTest service!';
$_lang['usertest_snippet_not_test_id'] = "Нет номера теста!";
$_lang['usertest_snippet_not_found_test'] = "Не найден тест!";
$_lang['usertest_snippet_not_publish_test'] = "Тест еще не опубликован!";
$_lang['usertest_snippet_end_publish_test_time'] = "Закончилось время публикации теста!";
$_lang['usertest_snippet_end_test_count_attempts'] = "Закончилось кол-во попыток пройти тест!";
$_lang['usertest_snippet_end_test'] = "Тест завершён!";
$_lang['usertest_snippet_end_test_renew'] = "Тест завершился. Попробуйте пройти заново.";
$_lang['usertest_snippet_question_not_found'] = "Вопрос не найден. q_id=[[+q_id]]";//array('error'=>"Вопрос не найден. q_id=".$q_id,'reset_url'=>$reset_url)
$_lang['usertest_access_error'] = 'Ошибка доступа!';//array('error'=>'Ошибка доступа!')
$_lang['usertest_snippet_answer_not_found'] = 'Не найден ответы на тест!';
$_lang['usertest_snippet_no_invite_code'] = 'Нет кода приглашения!';
$_lang['usertest_snippet_invite_code_error'] = 'Код приглашения не верный!';
$_lang['usertest_snippet_invite_code_timeout'] = 'Срок приглашения истек!';
$_lang['usertest_snippet_not_found_user'] = 'Не найден пользователь!';
$_lang['usertest_not_remove_haker'] = 'Запрещено удалять вариант Хакер!';

$_lang['usertest_answer_time'] = 'Время, с';

//07.06.2022
$_lang['usertest_email_create_user_subject'] = 'Регистрация на сайте [[+site_name]]';
$_lang['usertest_email_create_user_body'] = 'Для Вас создана учетная запись на сайте [[+site_name]].
Ваш логин: [[+email]]. Пароль: [[+password]]';


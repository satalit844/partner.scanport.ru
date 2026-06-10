<?php
include_once 'setting.inc.php';

$_lang['usertest'] = 'UserTest';
$_lang['usertest_menu_desc'] = 'User tests.';
$_lang['usertest_intro_msg'] = 'To output a test, use the UserTest snippet. For example [[!UserTest? &id=`1`]]. This example will output the test with id = 1.';

$_lang['usertest_tests'] = 'Tests';
$_lang['usertest_results'] = 'Test results';
$_lang['usertest_test'] = 'Test';
$_lang['usertest_groups'] = 'Groups tests';
$_lang['usertest_group'] = 'Group test';
$_lang['usertest_category'] = 'categories questions';
$_lang['usertest_userresult_category'] = 'categories';
$_lang['usertest_invite'] = 'invitations';

$_lang['usertest_test_create'] = 'create a test';
$_lang['usertest_test_update'] = 'change test';
$_lang['usertest_test_enable'] = 'turn on';
$_lang['usertest_tests_enable'] = 'turn on';
$_lang['usertest_test_disable'] = 'disable';
$_lang['usertest_tests_disable'] = 'disable';
$_lang['usertest_test_remove'] = 'delete test';
$_lang['usertest_tests_remove'] = 'delete tests';
$_lang['usertest_test_remove_confirm'] = 'Are you sure you want to delete this test?';
$_lang['usertest_tests_remove_confirm'] = 'Are you sure you want to delete this tests?';
$_lang['usertest_test_active'] = 'included';
$_lang['usertest_test_questions'] = 'questions';
$_lang['usertest_test_variants'] = 'results management';

$_lang['usertest_test_tab_main'] = 'main';
$_lang['usertest_test_tab_add'] = 'additional settings';

$_lang['usertest_test_customer'] = 'Customer';
$_lang['usertest_test_appeal'] = 'Appeal to users';
$_lang['usertest_test_instruction'] = 'Instruction';
$_lang['usertest_test_pub_date'] = 'Publication date';
$_lang['usertest_test_unpub_date'] = 'Date removed from publication';
$_lang['usertest_parent'] = 'Question-original';

$_lang['usertest_test_count_questions'] = 'Number of questions in the test. (Randomly selected from the questions given for the test. 0 all questions in a row.)';
$_lang['usertest_test_count_questions_on_page'] = 'Number of questions per page. (0-all questions.)';
$_lang['usertest_test_count_time_test'] = 'Test time in seconds. (0-time unlimited.)';
$_lang['usertest_test_type'] = 'Test results are calculated:';
$_lang['usertest_test_count_test_answer'] = 'Limit the number of attempts to take the test. (0-unlimited.)';

$_lang['usertest_item_err_name'] = 'You must specify a test name.';
$_lang['usertest_item_err_ae'] = 'A test with this name already exists.';
$_lang['usertest_item_err_nf'] = 'Test not found.';
$_lang['usertest_item_err_ns'] = 'Test not specified.';
$_lang['usertest_item_err_remove'] = 'Error deleting Test.';
$_lang['usertest_item_err_save'] = 'Error saving Test.';

//$_lang['usertest_items'] = 'Items';
$_lang['usertest_item_id'] = 'Id';
$_lang['usertest_item_name'] = 'Name';
$_lang['usertest_item_description'] = 'Description';
$_lang['usertest_item_active'] = 'Actively';
$_lang['usertest_use_category'] = 'Enable question categories';
$_lang['usertest_category_name'] = 'Category';

$_lang['usertest_menuindex'] = 'menuindex';
$_lang['usertest_question_type'] = 'Question type';
$_lang['usertest_type_file'] = 'File type';
$_lang['usertest_file'] = 'File';

$_lang['usertest_test_id'] = 'Test ID';
$_lang['usertest_question'] = 'Question';
$_lang['usertest_question_create'] = 'Add question';
$_lang['usertest_question_clone'] = 'Copy question';

$_lang['usertest_question_update'] = 'Change question';
$_lang['usertest_question_remove'] = 'Delete question';
$_lang['usertest_questions_remove'] = 'Delete questions';
$_lang['usertest_question_remove_confirm'] = 'Are you sure you want to delete this question?';
$_lang['usertest_questions_remove_confirm'] = 'Are you sure you want to delete these questions?';

$_lang['usertest_question_err_nf'] = 'Question not found.';
$_lang['usertest_question_err_ns'] = 'Question not specified.';
$_lang['usertest_question_err_remove'] = 'Error deleting Question.';
$_lang['usertest_question_err_save'] = 'Error saving Question.';

$_lang['usertest_answers'] = 'Answers';
$_lang['usertest_question_id'] = 'Id of question';
$_lang['usertest_answer_id'] = 'Response ID';
$_lang['usertest_answer'] = 'Answer';
$_lang['usertest_answer_img'] = 'Photo response';
$_lang['usertest_point'] = 'Points';
$_lang['usertest_max_point'] = 'MAX score';

$_lang['usertest_answer_right'] = 'Correct answer';
$_lang['usertest_answer_type5'] = 'Mappings';
$_lang['usertest_answer_type5_point'] = 'Give points for';
$_lang['usertest_answer_type5_type_point1'] = 'correct answer';
$_lang['usertest_answer_type5_type_point2'] = 'each match';

$_lang['usertest_answer_create'] = 'Add answer';
$_lang['usertest_answer_update'] = 'Change answer';
$_lang['usertest_answer_remove'] = 'Delete reply';
$_lang['usertest_answers_remove'] = 'Delete replies';
$_lang['usertest_answer_remove_confirm'] = 'Are you sure you want to delete this reply?';
$_lang['usertest_answers_remove_confirm'] = 'Are you sure you want to delete these answers?';

$_lang['usertest_answer_err_nf'] = 'No answer found.';
$_lang['usertest_answer_err_ns'] = 'The answer is not specified.';
$_lang['usertest_answer_err_remove'] = 'Error deleting the Response.';
$_lang['usertest_answer_err_save'] = 'Error saving the Response.';

$_lang['usertest_passed'] = 'Test passed';
$_lang['usertest_start_point'] = 'Min. number of points';
$_lang['usertest_end_point'] = 'Mac. number of points';
$_lang['usertest_result'] = 'Result text';

$_lang['usertest_variant'] = 'Test result options';
$_lang['usertest_variant_create'] = 'Add an option';
$_lang['usertest_variant_update'] = 'Change option';
$_lang['usertest_variant_remove'] = 'Delete Option';
$_lang['usertest_variants_remove'] = 'Delete Options';
$_lang['usertest_variant_remove_confirm'] = 'Are you sure you want to delete this option?';
$_lang['usertest_variants_remove_confirm'] = 'Are you sure you want to delete these options?';

$_lang['usertest_variant_err_nf'] = 'Option not found.';
$_lang['usertest_variant_err_ns'] = 'Option not specified.';
$_lang['usertest_variant_err_remove'] = 'Error deleting an option.';
$_lang['usertest_variant_err_save'] = 'Error saving the option.';

$_lang['usertest_userresult_update'] = 'Change the result';
$_lang['usertest_useranswer_update'] = 'Check the answer';
$_lang['usertest_comment'] = 'Teachers comment';

$_lang['usertest_userresult_test_name'] = 'Test';
$_lang['usertest_userresult_reg_user_name'] = 'User';
$_lang['usertest_userresult_user_name'] = 'Name';
$_lang['usertest_userresult_user_email'] = 'Email';
$_lang['usertest_userresult_date'] = 'Date';
$_lang['usertest_userresult_test_time'] = 'Test time';
$_lang['usertest_userresult_variant_id'] = 'Result Id';
$_lang['usertest_userresult_variant'] = 'Result';
$_lang['usertest_userresult_status'] = 'status';

$_lang['usertest_userresult_test'] = 'Search by test name or test id';
$_lang['usertest_userresult_user'] = 'Search by username';

$_lang['usertest_userresult_remove'] = 'Delete result';
$_lang['usertest_userresults_remove'] = 'Delete results';
$_lang['usertest_userresult_remove_confirm'] = 'Are you sure you want to delete this result?';
$_lang['usertest_userresults_remove_confirm'] = 'Are you sure you want to delete these results?';

$_lang['usertest_userresult_err_nf'] = 'Result not found.';
$_lang['usertest_userresult_err_ns'] ='The result is not specified.' ;
$_lang['usertest_userresult_err_remove'] = 'Error deleting the result.';
$_lang['usertest_userresult_err_save'] = 'Error when saving the result.';

$_lang['usertest_group_create'] = 'Create a Group';
$_lang['usertest_group_update'] = 'Change Group';
$_lang['usertest_group_remove'] = 'Delete a group';
$_lang['usertest_groups_remove'] ='Delete Groups' ;
$_lang['usertest_group_remove_confirm'] = 'Are you sure you want to delete this group?';
$_lang['usertest_groups_remove_confirm'] = 'Are you sure you want to delete these groups?';

$_lang['usertest_grouplink_create'] = 'Add to Group';
$_lang['usertest_grouplink_remove'] = 'Delete a test from a group';
$_lang['usertest_grouplinks_remove'] = 'Delete tests from a group';
$_lang['usertest_grouplink_remove_confirm'] = 'Are you sure you want to remove this test from the group?';
$_lang['usertest_grouplinks_remove_confirm'] = 'Are you sure you want to remove these tests from the group?';

$_lang['usertest_category_create'] = 'Create a category';
$_lang['usertest_category_update'] = 'Change Category';
$_lang['usertest_category_remove'] = 'Delete Category';
$_lang['usertest_categorys_remove'] = 'Delete Categories';
$_lang['usertest_category_remove_confirm'] = 'Are you sure you want to delete this category?';
$_lang['usertest_categorys_remove_confirm'] = 'Are you sure you want to delete these categories?';

$_lang['usertest_group_err_ae'] = 'The test is already in this group';

$_lang['usertest_group_id'] = 'Group Id';

$_lang['usertest_grid_search'] = 'Search';
$_lang['usertest_grid_actions'] = 'Actions';

$_lang['usertest_invite_imprort'] = 'Import';
$_lang['usertest_invite_export'] = 'Export';
$_lang['usertest_invite_update'] = 'Change Invitation';
$_lang['usertest_invite_remove'] ='Delete Invitation' ;
$_lang['usertest_invites_remove'] = 'Delete Invitations';
$_lang['usertest_invite_remove_confirm'] = 'Are you sure you want to delete this invitation?';
$_lang['usertest_invites_remove_confirm'] = 'Are you sure you want to delete these invitations?';

$_lang['usertest_user_pass'] = 'Password';
$_lang['usertest_invite_url'] = 'Url';
$_lang['usertest_user_auth_code'] ='Code' ;
$_lang['usertest_result_id'] = 'Test result Id';

$_lang['usertest_invite_test_page_id'] = 'Test page ID';
$_lang['usertest_invite_auth_page_id'] = 'Id of the invitation authorization page. Where is the snippet UserTestAuthInvites';
$_lang['usertest_invite_url_scheme'] = 'Format url. http or https';
$_lang['usertest_invite_excel_file'] = 'Excel file with email. 1  column email, 2 column username.';
$_lang['usertest_invite_date_expired'] = 'The invitation is valid until the date';
$_lang['usertest_invite_date_expired1'] ='Valid until' ;

$_lang['usertest_invite_subject'] = 'You are invited to the test [[+test_name]]';
$_lang['usertest_invite_with_empty_test_subject'] ='You missed the test  [[+test_name]]';
$_lang['usertest_teacher_subject'] = 'User [[+user_name]] passed the test [[+test_name]]';

//question type
$_lang['usertest_type_questions_radiobutton'] ="Single choice" ;
$_lang['usertest_type_questions_checkbox'] = "Multiple choice";
$_lang['usertest_type_questions_simple_text'] = "Plain text";
$_lang['usertest_type_questions_open_question'] = "Open question";
$_lang['usertest_type_questions_comparison_simple'] ="For comparison. Simple" ;
$_lang['usertest_type_questions_combined_option'] = 'Combined Multiple Choice';
$_lang['usertest_type_questions_table_checkbox'] = 'Check-box table';
$_lang['usertest_type_questions_table_input_text'] ='Table of text fields' ;
$_lang['usertest_type_questions_select_in_text'] = 'Selectors in the text';
$_lang['usertest_type_questions_combined_radiobutton'] = 'Combined Single selection';
$_lang['usertest_type_questions_table_procent'] = 'Table of percentages';
$_lang['usertest_type_questions_opros_san'] = 'The SAN Questionnaire';

//file type
$_lang['usertest_type_file_no_file'] = "Without a file";
$_lang['usertest_type_file_picture'] ="Picture" ;
$_lang['usertest_type_file_video'] = "Video";
$_lang['usertest_type_file_audio'] = "Sound";

//21.02.2018
$_lang['usertest_item_create'] = 'Add';
$_lang['usertest_item_create2'] = 'Create';
$_lang['usertest_item_update'] = 'Change';
$_lang['usertest_item_remove'] ='Delete Entry' ;
$_lang['usertest_items_remove'] = 'Delete Entries';
$_lang['usertest_item_remove_confirm'] = 'Are you sure you want to delete this entry?';
$_lang['usertest_items_remove_confirm'] = 'Are you sure you want to delete these records?';

$_lang['usertest_item_err_nf'] ='Record not found.' ;
$_lang['usertest_item_err_ns'] = 'The entry is not specified.';
$_lang['usertest_item_err_remove'] = 'Error deleting a record.';
$_lang['usertest_item_err_save'] = 'Error while saving the record.';

$_lang['usertest_variantsets'] = 'Test Result Sets';
$_lang['usertest_questions'] = 'Database of questions';

$_lang['usertest_question_select'] = 'Insert a question';
$_lang['usertest_questions_select'] = 'Insert questions';
$_lang['usertest_question_select_confirm'] = 'Do you want to insert a question?';
$_lang['usertest_question_err_dublicat'] = 'This test already has this question';


$_lang['usertest_question_copy'] ='Copy question' ;
$_lang['usertest_questions_copy'] = 'Copy questions';
$_lang['usertest_question_edit_link'] = 'Linked tests';
$_lang['usertest_question_copy_confirm'] = 'Do you want to resolve the question?';
//!!!
$_lang['usertest_question_search_ids'] = 'On IDs';

//01.03.2019
$_lang['usertest_test_question_link_err_test_id'] = 'Test not set!';
$_lang['usertest_test_question_link_err_queston_id'] = 'Question not set';
$_lang['usertest_test_question_link_err_variant_id'] = 'Test result not set';

//02.03.2019
$_lang['usertest_variant_set_id'] = 'ID set';
$_lang['usertest_variantset'] = 'Test result set';

$_lang['usertest_variant_id'] = 'ID option';
$_lang['usertest_use_custom_point'] = 'Use points test';

//26.03.2019
$_lang['usertest_invite_clear'] = 'Clear';
$_lang['usertest_clear_invites_days'] = 'Enter the number of days older than which to clear invitations';

$_lang['usertest_random_answer'] = 'Answers in random order';
$_lang['usertest_question_validate'] = 'Reply required';

$_lang['usertest_send_email_if_empty_test'] ='Send email if test is not started' ;
//23.04.2019
$_lang['usertest_export_import'] = 'Export Import';
$_lang['usertest_export'] = 'Export';
$_lang['usertest_test_export'] = 'ID test';
$_lang['usertest_test_export_desc'] = 'For which test to export. If empty, then for all tests.';

$_lang['usertest_import'] = 'Import';
$_lang['usertest_test_export_file'] = 'File import';
$_lang['usertest_test_export_file_desc'] = 'Download the test import file.';
$_lang['usertest_test_empty_export_file'] = 'Download the test import file.';

//16.05.2019
$_lang['usertest_test_copy'] = 'Copy Test';
$_lang['usertest_tests_copy'] = 'Copy Tests';
$_lang['usertest_test_copy_confirm'] = 'Do you want to copy the test?';

//09.06.2019 Questionnaire SAN
$_lang['usertest_test_type'] = 'Test type';
//26.06.2019 
$_lang['usertest_test_ask_user_data'] = 'Ask for user data before a test';
//08.02.2022
$_lang['usertest_snippet_not_load_service'] = 'Failed to load service UserTest';//'Could not load UserTest service!';
$_lang['usertest_snippet_not_test_id'] = "No test number!";
$_lang['usertest_snippet_not_found_test'] = "Test not found!";
$_lang['usertest_snippet_not_publish_test'] = "The test has not yet been published!";
$_lang['usertest_snippet_end_publish_test_time'] = "Test posting time has ended!";
$_lang['usertest_snippet_end_test_count_attempts'] = "The number of attempts to pass the test has ended!";
$_lang['usertest_snippet_end_test'] = "Test completed!";
$_lang['usertest_snippet_end_test_renew'] = "The test has ended. Try to go through again.";
$_lang['usertest_snippet_question_not_found'] = "Question not found. q_id=[[+q_id]]";//array('error'=>"Question not found. q_id=".$q_id,'reset_url'=>$reset_url)
$_lang['usertest_access_error'] = 'Access error!';//array('error'=>'Access error!')
$_lang['usertest_snippet_answer_not_found'] = 'No test answers found!';
$_lang['usertest_snippet_no_invite_code'] = 'No invite code!';
$_lang['usertest_snippet_invite_code_error'] = 'The invitation code is not correct!';
$_lang['usertest_snippet_invite_code_timeout'] = 'The invitation has expired!';
$_lang['usertest_snippet_not_found_user'] = 'User not found!';
$_lang['usertest_not_remove_haker'] = 'Forbidden removal option Hacker!';

$_lang['usertest_answer_time'] = 'Time, s';

//07.06.2022
$_lang['usertest_email_create_user_subject'] = 'Кegistration on the website [[+site_name]]';
$_lang['usertest_email_create_user_body'] = 'An account has been created for you on the site [[+site_name]].
Your login: [[+email]]. Password: [[+password]]';
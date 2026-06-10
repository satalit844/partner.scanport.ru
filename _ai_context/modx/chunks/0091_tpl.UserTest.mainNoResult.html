{if $.get.test_id is integer}
    {set $get_test_id = $.get.test_id}
{/if}
 <script>
    {if !$check_ajax}
        var _init = [];
    {/if}
</script> 

<div id="testuser-main" class="row">

{* @@@ Steps replaced in next col *} 
{* <div class="col-md-2">
        <ul class="pagination justify-content-center">
        {if $block_q_number}
            {foreach $block_q_number as $q}
                <li class="page-item">
                <a href="#" class="page-link step-box__item {if $q.curStepCheck}current{/if} {if $q.ansCheck}check{/if}" 
                    onclick="$('#next_step').val({$q.step});$('#UserTestForm').trigger('submit');return false;">
                    {$q.numberQ}
                </a>
                </li>
            {/foreach}
        {/if}
        </ul>
    </div>    
*}    
    <div class="col-lg-12 px-3 py-4 p-sm-4 p-md-5 rounded-3 shadow-lg">	    
        
{* @@@ Intro test
-------------------------------------------------- *}	    
        <h2 class="">{$test.name}</h2>
 
        
{*	Change pagination. See below
    <ul class="pagination pagination-sm">
        {if $block_q_number}
            {foreach $block_q_number as $q}
                <li class="page-item">
                <a href="#" class="page-link step-box__item {if $q.curStepCheck}current{/if} {if $q.ansCheck}check{/if}" 
                    onclick="$('#next_step').val({$q.step});$('#UserTestForm').trigger('submit');return false;">
                    {$q.numberQ}
                </a>
                </li>
            {/foreach}
        {/if}
    </ul> 
*}
    
<div class="" role="group" aria-label="steps">
{if $block_q_number}
{foreach $block_q_number as $q}	
<a class="btn btn-outline-primary step-box__item {if $q.curStepCheck}current{/if} {if $q.ansCheck}check{/if}" onclick="$('#next_step').val({$q.step});$('#UserTestForm').trigger('submit');return false;">{$q.numberQ}</a>
{/foreach}
{/if}  
</div>   
        
        <p>{$debug}</p>

        {if $curStep == "start"}
            {if $test.description is empty}
            {else}
                {* <h3 class="fs-4 text-secondary fw-normal">Анотация</h3> *}
                <div class="row">{$test.description}</div>
            {/if}
            {if $test.appeal is empty}
            {else}
                <h3 class="fs-4 text-secondary fw-normal">Обращение к пользователю</h3>
                <div class="row">{$test.appeal}</div>
            {/if}
            {if $test.instruction && $test.instruction != "<p></p>"}
                <h3 class="fs-4 text-secondary fw-normal">Инструкция</h3>
                <div class="row">{$test.instruction}</div>
            {/if}
<hr>
         
{* @@@ Test
-------------------------------------------------- *}	     
    <form id="UserTestForm" class="text-center" action="{if $_modx->resource.id}{$_modx->makeUrl($_modx->resource.id,'',['test_id'=>$get_test_id,'step'=>'start'])}{/if}" method="POST">
                <input type="hidden" name="test_id" value="{$test_id}"/>
                <input type="hidden" name="step" value="start"/>
                {if $test.ask_user_data && !$check_restore}
                    {$_modx->getChunk('tpl.UserTest.AskUserDataForm',[])}
                {/if}
                <button type="submit" class="btn btn-primary btn-lg">Начать тестирование</button>
            </form>            
            
        {elseif $curStep == "finish"}
            {if $result_status == 3}
                <div class="alert alert-info" role="alert">Тест ожидает проверки преподователем!</div>
            {else}
                {*<p>Вы набрали {$test_point} баллов {if $test.type == 1} из {$max_point}{/if}. Время теста: {$test_time}с.</p>
                {if $test.test_type == 2}
                    {$_modx->getChunk('tpl.UserTest.OprosSANResult',['check_ajax'=>$check_ajax,'test'=>$test,'catResults'=>$catResults,'cat_history'=>$cat_history])}
                {else}
                    {if $var_passed}
                        <div class="alert alert-success" role="alert">Тест сдан!</div>
                    {else}
                        <div class="alert alert-danger" role="alert">Тест провален!</div>
                    {/if}
                    <p>Ваш результат теста:</p>
                    
                    <div class="row">
                        {if $is_timeout}
                            <div class="alert alert-warning" role="alert">Вы не успели пройти тест. По какой-то причине, Ваши ответы пришли позже времени окончания теста!</div>
                        {else}
                            {$var_result}
                        {/if}
                    </div>
                    {if $answer_page_url}
                        <p>&mdash;&nbsp;<a href="{$answer_page_url}">Просмотреть правильные ответы</a></p>
                    {/if}
                {/if} *}
                <script>
                    {if !$check_ajax}
                        onload_af = function() {
                    {/if}
                        $('#af_test_point').val('{$test_point}');
                        $('#af_var_result').val('{$var_result |preg_replace : '~\n~': ''}');
                        $('#af_test_name').val('{$test.name |preg_replace : '~\n~': ''}');
                        $('#af_result_id').val('{$result_id}');
                        $('#af_max_point').val('{$max_point}');
                        $('#af_cat_email_results').val('{$cat_email_results |preg_replace : '~\n~': ''}');
                        $('#af_name').val('{$user_name}');
                        $('#af_email').val('{$user_email}');                        
                        $('#sendMail').show();
                    {if !$check_ajax}
                        };
                        _init.push(onload_af);
                    {/if}
                </script>
                {if $test.use_category}
                    {$_modx->getChunk('tpl.UserTest.CatResult',['check_ajax'=>$check_ajax,'test'=>$test,'catResults'=>$catResults,'test_point'=>$test_point])}
                {/if}
            {/if}
        {else}
            {if $test_url}
            <div class="small alert alert-info rounded-0 border-start border-info border-4 border-top-0 border-bottom-0 border-end-0" role="alert">
                Если вы прервали тест, используйте для возврата ссылку: <a href="{$test_url}">{$test_url}</a>
             </div>
            {/if}{* /.alert *}
            {*$end_test_time} {$test_time*}
            {if $end_test_time > 0}
                <p>До окончания теста: <span id="getting-started"></span></p>
                <script>
                    {if !$check_ajax}
                        onload_time = function() {
                    {/if}
                        time1 = {$end_test_time}*1000 + Date.now();
                        $('#getting-started').countdown(time1)
                        .on('update.countdown', function(event) {
                            var format = '%H:%M:%S';
                            $(this).html(event.strftime(format));
                        })
                        .on('finish.countdown', function(event) {
                            //$(this).html('This offer has expired!').parent().addClass('disabled');
                            $('#testuser-main .validate').removeClass('validate');
                            $('#testuser-main .btn-next').trigger('click');
                        });
                    {if !$check_ajax}
                        };
                        _init.push(onload_time);
                    {/if}
                </script>
            {/if}
            <div id="testuser-questions">
                {*<p>Вы набрали {$test_point} баллов. Время теста: {$test_time}с.</p>*}
                
{* @@@ Body form *}   
             
                <form id="UserTestForm" action="{if $_modx->resource.id}{$_modx->makeUrl($_modx->resource.id,'',['test_id'=>$get_test_id])}{/if}" method="POST">
                    <input type="hidden" name="test_id" value="{$test_id}"/>
                    {foreach $questions as $q}
                        <div class="row">
{* @@@ Num question *}	                        
                           <div class="my-2">
                               <span class="text-primary py-2 px-3 badge rounded-pill text-bg-info">Вопрос {$q.numberQ} из {$q.countQ}</span>
                            </div>
                            
                        {if $q.type == 9} {* Селекты в тексте *}
                            <div class="question {if $q.validate}validate{/if}" data-id="{$q.id}" data-type_id="{$q.type}">{$q.q_str}</div>
                        {elseif $q.type == 12} {* Опросник САН *}
                                  {var $qarr = $q.question | split : '##'}  
                        <div class="col-lg-4">
                            {$qarr.0}
                        </div>
                        <div class="col-lg-4 question {if $q.validate}validate{/if}" data-id="{$q.id}" data-type_id="{$q.type}">
                        {foreach $q.answers as $a}
                            <div class="opros_san">
                                {switch $a.type_file}
                                    {case 1}
                                        <img src="{$a.file}" alt="{$a.answer}">
                                    {case 2}
                                        <video src="{$a.file}" controls></video>
                                    {case 3}
                                        <audio controls>
                                            <source src="{$a.file}" type="audio/mpeg">
                                        </audio>
                                {/switch}
                                {if $q.answer_id == $a.id}
                                <label><input name="question[{$q.id}]" type="radio" value="{$a.id}" checked>{$a.answer}</label>
                                {else}
                                <label><input name="question[{$q.id}]" type="radio" value="{$a.id}">{$a.answer}</label>
                                {/if}
                            </div>
                        {/foreach}
                        </div>{* /.col *}
                        
                        <div class="col-lg-4">
                            {$qarr.1}
                        </div>
                    {else}
                            <div class="row">{$q.question}</div>{* /.row *}
                                {switch $q.type_file}
                                    {case 1}
                                        <img src="{$q.file}" alt="{$q.question}">
                                    {case 2}
                                        <video src="{$q.file}" controls></video>
                                    {case 3}
                                        <audio controls>
                                            <source src="{$q.file}" type="audio/mpeg">
                                        </audio>
                                {/switch}
                                    {*$q | print_r : true*}
                        {/if}
                        </div>{* /.row *}
 {* @@@ Radiobutton *}                        
<div class="mb-5 row row-cols-1 row-cols-md-3 g-3 question {if $q.validate}validate{/if}" data-id="{$q.id}" data-type_id="{$q.type}">
                            
      
                            {switch $q.type}
                                {case 1} {* Одиночный выбор *}
                                    {foreach $q.answers as $a}
<div class="col">                           
<div class="card h-100">
                                            
                                            
                                            {switch $a.type_file}
                                                {case 1}
                                                    <img src="{$a.file}" class="card-img-top" alt="{$a.answer}" title="{$a.answer}">
                                                {case 2}
                                                    <video src="{$a.file}" class="card-img-top" controls></video>
                                                {case 3}
                                                    <audio controls>
                                                        <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                                    </audio>
                                            {/switch}
                                            
                                            <label  for="q{$q.id}v{$a.id}" class="card-body ">                                      
                                            <div class="card-text form-check">                                     
                                                {if $q.answer_id == $a.id}
                                                <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}]" type="radio" value="{$a.id}" checked>
                                                <span class="form-check-label">{$a.answer}</span>
                                                {else}
                                                <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}]" type="radio" value="{$a.id}">
                                                <span class="form-check-label">{$a.answer}</span>
                                                {/if}
                                            </div> {* /.card-text *}
                                            </label> {* /.card-body *}
                                            </div>{* /.card *}                                          
                                            </div>{* /.col *}
                                    {/foreach}
                        
                                {case 2} {* Множественный выбор *}
                                    {foreach $q.answers as $a}
<div class="col">
<div class="card h-100">                                        
                                            {switch $a.type_file}
                                                {case 1}
                                                    <img src="{$a.file}" class="card-img-top" alt="{$a.answer}">
                                                {case 2}
                                                    <video src="{$a.file}" class="card-img-top" controls></video>
                                                {case 3}
                                                    <audio controls>
                                                        <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                                    </audio>
                                            {/switch}

                                            <label  for="q{$q.id}v{$a.id}" class="card-body ">                                      
                                            <div class="card-text form-check">                                     
                                                {if $q.answer_id == $a.id}
                                                <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}][]" type="checkbox" value="{$a.id}" checked>
                                                <span class="form-check-label">{$a.answer}</span>
                                                {else}
                                                <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}][]" type="checkbox" value="{$a.id}">
                                                <span class="form-check-label">{$a.answer}</span>
                                                {/if}
                                            </div> {* /.card-text *}
                                            </label> {* /.card-body *}
                                            </div>{* /.card *}                                          
                                            </div>{* /.col *}
                                    {/foreach}
                                  
                                {case 3} {* Простой текст *}
                                    <div class="form-group flex-fill">
                                        <input name="question[{$q.id}]" type="text" value="{$q.answer}" class="form-control">
                                    </div>
                                {case 4} {* Открытый вопрос *}
                                    <div class="form-group flex-fill">
                                        <input name="question[{$q.id}]" type="text" value="{$q.answer}" class="form-control">
                                    </div>
                                    
                                {case 5} {* На сопоставление. Простой *}
                                    <div class="col w-50">{*  style="float: left; margin-top: 15px; margin-left: 10px" *}
                                        <ul class="comparison list-group list-group-flush user-select-none">
                                            {foreach $q.q as $qk => $qv}
                                                <li class="list-group-item" data-id="{$qk}">{$qv}</li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                    <div class="col w-50">
                                        <ul class="comparison list-group list-group-flush" id="sortable-{$q.id}">
                                            {foreach $q.a as $ak => $av}
                                                <li class="list-group-item justify-content-between cursor-move" data-id="{$ak}">
                                                <span>{$av}</span> <span text-secondary>&equiv;</span>
                                                </li>
                                            {/foreach}
                                        </ul>
                                    </div>
                                    <input name="question[{$q.id}]" id="ans-{$q.id}" type="hidden" value="{$q.answer}">
                                    <script>
                                    {if !$check_ajax}
                                        onload_sortable_{$q.id} = function() {
                                    {/if}
                                           var el = document.getElementById('sortable-{$q.id}');
                                           var sortable{$q.id} = Sortable.create(el,{
                                               onEnd: function (evt) {
                                                    var order{$q.id} = sortable{$q.id}.toArray();
                                                    //console.info(order{$q.id});
                                                    var ans{$q.id} = document.getElementById('ans-{$q.id}');
                                                    ans{$q.id}.value = order{$q.id}.join('|');
                                                },
                                           });
                                    {if !$check_ajax}
                                        };
                                        _init.push(onload_sortable_{$q.id});
                                    {/if}
                                    </script> 
                                    
                                {case 6} {* Комбинированный вариант *}
                                    {foreach $q.answers as $a}
<div class="col">
<div class="card h-100">		                                        
                                            {switch $a.type_file}
                                                {case 1}
                                                    <img src="{$a.file}" class="card-img-top" alt="{$a.answer}" title="{$a.answer}">
                                                {case 2}
                                                    <video src="{$a.file}" class="card-img-top" controls></video>
                                                {case 3}
                                                    <audio controls>
                                                        <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                                    </audio>
                                            {/switch}
<div class="card-body">                                      
<div class="card-text form-check">                                             
                                            {if $a.check}
                                            <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}][]" type="checkbox" value="{$a.id}" checked>
                                            <label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>
                                            {else}
                                            <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}][]" type="checkbox" value="{$a.id}">
                                            <label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>
                                            {/if}
</div>{* /.card-text *}
</div>{* /.card-body *}
</div>{* /.card *}                                          
</div>{* /.col *}
                                    {/foreach}
                                    <div class="col">
[[- <div class="card h-100">
<div class="card-body">                                      
<div class="card-text form-check"> ]] 		                                    
                                        {if $q.answers_add.check}
                                        <label class="form-label ms-1"></label>
                                            <input class="form-check-input" name="question[{$q.id}][ans_add]" type="checkbox" value="" checked> Другое
                                            <input name="question[{$q.id}][ans_add_ans]" type="text" value="{$q.answers_add.ans}" class="form-control">
                                        
                                        {else}
                                        <label class="form-label ms-1"></label>
                                            <input class="form-check-input" name="question[{$q.id}][ans_add]" type="checkbox" value=""> Другое
                                            <input class="form-control" name="question[{$q.id}][ans_add_ans]" type="text" value="">
                                        {/if}

[[- </div>{* /.card-text *}
</div>{* /.card-body *}
</div>{* /.card *}                                          
]]
</div>{* /.col *}
                                    
                                    
                                {case 7} {* Таблица чек-боксов *}
                                  
                                    <table class="table table-sm">
                                        <tbody>
                                            <tr>
                                                {foreach $q.header as $h}
                                                    <td>{$h}</td>
                                                {/foreach}
                                            </tr>
                                        {foreach $q.q_childs as $qc}
                                            <tr>
                                                <td>{$qc.question}</td>
                                                {foreach $qc.answers as $a}
                                                <td>
                                                    {if $a.check}                                                    
                                                    <label><input class="form-check-input" name="question[{$q.id}][{$qc.id}][]" type="checkbox" value="{$a.id}" checked></label>
                                                    {else}
                                                    <label><input class="form-check-input" name="question[{$q.id}][{$qc.id}][]" type="checkbox" value="{$a.id}"></label>
                                                    {/if}
                                                </td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {case 8} {* Таблица текстовых полей *}
                                    <table class="table">
                                        <tbody>
                                            <tr>
                                                {foreach $q.header as $h}
                                                    <td>{$h}</td>
                                                {/foreach}
                                            </tr>
                                        {foreach $q.q_childs as $qc}
                                            <tr>
                                                <td>{$qc.question}</td>
                                                {foreach $qc.answers as $a}
                                                <td>
                                                    <div class="answer__textarea-box">
                                                        <textarea name="question[{$q.id}][{$qc.id}][{$a.id}]" class="answer__textarea">{$a.ac}</textarea>
                                                    </div>
                                                </td>
                                                {/foreach}
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
                                {case 10} {* Комбинированный одиночный выбор *}
                                    
                                    {foreach $q.answers as $a}
                                        <div class="col">
<div class="card h-100">		                                        
                                            {switch $a.type_file}
                                                {case 1}
                                                    <img src="{$a.file}" class="card-img-top" alt="{$a.answer}" title="{$a.answer}">
                                                {case 2}
                                                    <video src="{$a.file}" class="card-img-top"controls></video>
                                                {case 3}
                                                    <audio controls>
                                                        <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                                    </audio>
                                            {/switch}
<div class="card-body">                                      
<div class="card-text form-check">                                             
                                            {if $a.check}
                                            <input class="form-check-input" id="q{$q.id}v{$a.id}" name="question[{$q.id}][ans]" type="radio" value="{$a.id}" checked>
                                            <label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>
                                            {else}
                                            <input class="form-check-input"  id="q{$q.id}v{$a.id}" name="question[{$q.id}][ans]" type="radio" value="{$a.id}">
                                            <label class="form-check-label"  for="q{$q.id}v{$a.id}">{$a.answer}</label>
                                            {/if}
</div>{* /.card-text *}
</div>{* /.card-body *}
</div>{* /.card *}                                          
</div>{* /.col *}
                                    {/foreach}
                                    <div class="col">
                                        
                                        {if $q.answers_add.check}
                                        <input class="form-check-input" id="q{$q.id}ans" name="question[{$q.id}][ans]" type="radio" value="ans_add" checked>
<label class="form-check-label" for="q{$q.id}ans">Другое</label>                                            
<input class="form-control" name="question[{$q.id}][ans_add_ans]" type="text" value="{$q.answers_add.ans}">
                                        
                                        {else}
                                        
                                            <input class="form-check-input" id="q{$q.id}ans" name="question[{$q.id}][ans]" type="radio" value="ans_add">
                                            <label class="form-check-label" for="q{$q.id}ans">Другое</label>   
                                            <input class="form-control" name="question[{$q.id}][ans_add_ans]" type="text" value="">
                                        
                                        {/if}                                        
</div>{* /.col *}
                            {/switch}
                            
</div> {* /.row *}
                    {/foreach}
               
                    <div class="row mt-5 pt-5">
                        
                        <input type="hidden" name="step" id="next_step" value="{$nextStep}">
                        <input type="hidden" name="answer_step" id="answer_step" value="{$curStep}">
                        
                        <div class="col-12 d-flex justify-content-between">
                            {if $prevStep != "start"}
                                <button type="submit" onclick="$('#next_step').val({$prevStep});return true;" class="me-auto btn btn-outline-primary">&larr; Назад</button>
                            {/if}
                        
                        
                            {if $nextStep != "finish"}
                            <button type="submit" class="ms-auto btn btn-outline-primary btn-next">Вперёд &rarr;</button> 
                             {/if}   
                            
                        </div> 
                    </div> {* /.row *}
                        {if $nextStep == "finish"}	
                    <div class="row">                           
                            <div class="col-12 text-center">
                            <hr>    
                                <button type="submit" class="btn btn-primary btn-lg btn-next">Финиш</button>
                            </div>
                    </div>     
                            {/if}
                        

                </form>
                
            </div>{* /#testuser-questions *}
        {/if}
   </div>
   
{*    
   <div class="col-lg-2">
        {if $test.customer}
            [[- <h2>Инструкция</h2> ]]
            <div class="row">{$test.customer}</div>
        {/if}
    </div>
*}  
    {if !$check_ajax}
        <script>
            window.onload = function()
            {
                for ( var i in _init )
                {
                    if ( typeof( _init[i] ) == 'function' ) _init[i](); // вызываем подряд все функции из _init
                }
            }
        </script>
    {/if}
    
</div>{* /#testuser-main *}
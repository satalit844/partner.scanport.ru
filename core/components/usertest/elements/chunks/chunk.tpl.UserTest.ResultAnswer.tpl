<div id="testuser_result" class="px-3 py-4 p-sm-4 p-md-5 rounded-3 shadow-lg">
    <h2>{$test.name}</h2>
    
    {foreach $ResultAnswers as $ra}
        {*$ra | print_r : true*}
        {set $q = $ra.question}
        
        <div class="row">
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
                </div>{* /.opros_san *}
            {/foreach}
            
            </div>{* /.col*}
            
            
            
            <div class="col-lg-4">
                +++++{$qarr.1}
            </div>
        {else}
        
        <div class="mt-4 mb-2">{$q.question}</div>
        
        <div class="bg-dark">
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
        </div>                
        {/if}
 </div>       
        
{* @@@ Card with answers
------------------------------------------------ *}         
<div class="mb-3 row row-cols-1 row-cols-md-3 g-3">

        {switch $q.type}
            {case 1} {* Одиночный выбор *}
                {foreach $q.answers as $a}
<div class="col">                           
<div class="card h-100">
                        {switch $a.type_file}
                            {case 1}
                             <img src="{$a.file}" class="card-img-top" alt="{$a.answer}" title="{$a.answer}">
                            {case 2}
                                <video src="{$a.file}" class="card-img-top"  controls></video>
                            {case 3}
                                <audio controls>
                                    <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                </audio>
                        {/switch}
<div class="card-body">                                      
<div class="card-text form-check">                          
                        {if $q.answer_id == $a.id}
                        <input class="form-check-input" id="q{$q.id}v{$a.id}" name="{$q.id}" type="radio" value="{$a.id}" checked>
                        <label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>                        
                        
                        {else}
                        <input class="form-check-input" id="q{$q.id}v{$a.id}" name="{$q.id}" type="radio" value="{$a.id}"  disabled>
                        <label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label> 
                        {/if}
</div> {* /.card-text *}
</div> {* /.card-body *}
</div>{* /.card *}                                          
</div>{* /.col *}
                {/foreach}
                
                
            {case 2} {* Множественный выбор *}
                {foreach $q.answers as $a}
<div class="col">                           
<div class="card h-100">
{switch $a.type_file}
                            {case 1}
                             <img src="{$a.file}" class="card-img-top" alt="{$a.answer}" title="{$a.answer}">
                            {case 2}
                                <video src="{$a.file}" class="card-img-top"  controls></video>
                            {case 3}
                                <audio controls>
                                    <source src="{$a.file}" class="card-img-top" type="audio/mpeg">
                                </audio>
                        {/switch}
<div class="card-body">                                      
<div class="card-text form-check"> 
	                        
{if $a.check}
<input class="form-check-input" id="q{$q.id}v{$a.id}" name="{$q.id}[]" type="checkbox" value="{$a.id}" checked>
<label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>
{else}                                          
<input class="form-check-input" id="q{$q.id}v{$a.id}" name="{$q.id}[]" type="checkbox" value="{$a.id}"  disabled>
<label class="form-check-label" for="q{$q.id}v{$a.id}">{$a.answer}</label>
{/if}
                        

                        
</div> {* /.card-text *}
</div> {* /.card-body *}
</div>{* /.card *}                                          
</div>{* /.col *}
                {/foreach}
        {/switch}
        </div>   
            
{* @@@ Revise answers
------------------------------------------------ *}                      
<div class="row mx-1 mb-4 gx-4 gy-1 pb-3 alert alert-info rounded-0 border-start border-info border-4 border-top-0 border-bottom-0 border-end-0">
            <div class="col-md-4 col-lg-3">
             <strong class="text-muted">Ваш ответ</strong>   
            </div>
            <div class="col-md-8 col-lg-9">
                {$ra.answer}
            </div>
            <div class="col-md-4 col-lg-3">
                <strong class="text-muted">Правильный ответ</strong>
            </div>
            <div class="col-md-8 col-lg-9">
                {$ra.rightAnswers}
            </div>
        {if $ra.comment}
                <div class="col-md-4 col-lg-3">
                    <strong class="text-muted">Комментарий преподавателя</strong>
                </div>
                <div class="col-md-8 col-lg-9">
                    {$ra.comment}
                </div>
        {/if}
            <div class="col-md-4 col-lg-3">
                <strong class="text-muted">Ваш балл за ответ</strong>
            </div>
            <div class="col-md-8 col-lg-9">
                {$ra.point} баллов
            </div>
</div>{* /.row *}
    {/foreach}
</div>
{foreach $groups as $group}
    <div class="row mt-4 mb-2"> 
        <h2 class="fs-5"><span class="text-white py-2 px-4 badge rounded-pill text-bg-info">{$group.name}</span></h2>
        {if $group.description}
            <div class="small text-muted ms-1">{$group.description}</div>
        {/if}
    </div>{* /.row *}
    
{foreach $group.tests as $test}  
    
<div class="card mb-2 shadow-lg border-0">                                          
<div class="card-body ps-3 ps-lg-4">                                      
<div class="row align-items-center">

<div class="col-lg-9">
<h3 class="fs-4 mt-2 mb-4">{$test.name}</h3>	                
                {if $test.description}
                    <div class="small text-muted">{$test.description}</div>
                {/if}	
</div> {* /.col *}
<div class="col-lg-3 pb-3 pb-lg-0">

                {if $_modx->user.id > 0 and $test.result_count > 0}
                    {if $test.last_result.status_id == 3}
                        <p>Тест ожидает проверки преподавателем</p>
                    {else}
                    
 {*$test.last_result | print_r*}                   
<h5 class="fs-6 d-block d-lg-none mt-3">Итог теста</h5>
{if $test.last_result.passed}
<div class="mb-2">
<span class="px-2 py-1 alert alert-info rounded-0 border-start border-info border-4 border-top-0 border-bottom-0 border-end-0" role="alert">Тест сдан<span>
</div>   
{else}
<div class="mb-2">
<span class="inline-block py-1 pe-4 alert alert-warning rounded-0 border-start border-warning border-4 border-top-0 border-bottom-0 border-end-0" role="alert">Тест не сдан</span>
</div>    
{/if}

                    
                     <p class="text-muted small">Вы набрали {$test.last_result.test_point} баллов. Время теста: {$test.last_result.test_time}с.</p> 
                       
                       {if $test.last_result.variant is empty}
                       {else}
                       <h5 class="fs-6 mb-0">Результат теста</h5>
		                <div class="text-muted small">{$test.last_result.variant}</div>   
                       {/if}
                       
                        {if $test.last_result.comment}
                         <h5 class="fs-6">Комментарий преподавателя</h5>
                         <div class="text-muted small">{$test.last_result.comment}</div>
                        {/if}	
                        
<div class="w-100 clearfix py-2"></div>
                     
{if $test.count_test_answer > 0}
{if $test.count_test_answer > $test.result_count}
<a href="{$_modx->makeUrl($test_page_id,'',['test_id'=>$test.id])}{if $start_step}&step=start{/if}" class="px-3 btn btn-primary mx-auto d-lg-block d-md-inline-block d-block">Пройти тест еще раз</a>
{else}
<a href="{$_modx->makeUrl($answer_page_id,'',['result_id'=>$test.last_result.id])}" class="px-3 btn btn-primary  mx-auto d-lg-block d-md-inline-block d-block">Просмотреть правильные ответы</a>
{/if}
{else}
<a href="{$_modx->makeUrl($test_page_id,'',['test_id'=>$test.id])}{if $start_step}&step=start{/if}" class="px-3 btn btn-primary  mx-auto d-lg-block d-md-inline-block d-block">Пройти тест еще раз</a>
{/if}
{/if}
{else}

<a href="{$_modx->makeUrl($test_page_id,'',['test_id'=>$test.id])}{if $start_step}&step=start{/if}" class="px-3 px-md-auto btn btn-primary mx-auto d-lg-block d-md-inline-block d-block">Пройти тест</a>

{/if}	

</div> {* /.col *}
</div> {* /.row *}
</div> {* /.card-body *}
</div>{* /.card *} 
    
    {/foreach}
{/foreach}
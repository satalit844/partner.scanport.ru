<div class="row justify-content-center align-items-center pb-5">
<div class="col-lg-5 col-sm-7 col-10">	

<div class="my-5 alert alert-info rounded-0 border-start border-info border-4 border-top-0 border-bottom-0 border-end-0" role="alert">
Сохраните Ваш результат теста. Отправьте его на почту
</div>

<form action="" method="post" id="UserTestEmailForm" class="ajax_form af_example">
    <input type="hidden" id="af_test_point" name="test_point" value="[[+test_point]]"/>
    <input type="hidden" id="af_max_point" name="max_point" value="[[+max_point]]"/>
    <input type="hidden" id="af_var_result" name="var_result" value="[[+var_result]]"/>
    <input type="hidden" id="af_test_name" name="test_name" value="[[+test_name]]"/>
    <input type="hidden" id="af_result_id" name="result_id" value="[[+result_id]]"/>
    <input type="hidden" id="af_cat_email_results" name="cat_email_results" value="[[+cat_email_results]]"/>
    
        <div class="form-floating">     
        <input type="text" class="form-control" id="floatingName" placeholder="Имя" name="name" value="[[!+fi.name]]">
        <label for="floatingName" class="mb-0">[[%af_label_name]]</label>
        <span class="error_name alert-error small">&nbsp;[[+fi.error.name]]</span> 
        </div>
        
         <div class="form-floating">     
        <input type="text" class="form-control" id="floatingEmail" placeholder="Email" name="email" value="[[+fi.email]]">
        <label for="floatingEmail" class="mb-0">[[%af_label_email]]</label>
        <span class="error_name alert-error small">&nbsp;[[+fi.error.name]]</span> 
        </div>	

    <div class="form-group">
        <div class="controls">
            <button type="reset" class="btn btn-default">[[%af_reset]]</button>
            <button type="submit" class="btn btn-primary">[[%af_submit]]</button>
        </div>
    </div>

    [[+fi.success:is=`1`:then=`
    <div class="alert alert-success">[[+fi.successMessage]]</div>
    `]]
    [[+fi.validation_error:is=`1`:then=`
    <div class="alert alert-danger">[[+fi.validation_error_message]]</div>
    `]]
</form>

</div>[[- /.col ]]
<div class="col-md-2"></div>[[- /.col ]]
</div>[[- /.row ]]
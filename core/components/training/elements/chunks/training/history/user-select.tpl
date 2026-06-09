<form method="get" class="history-user-select-form mb-0">
    <input type="hidden" name="{$sort_name}" value="{$sort_value}">
    <select name="{$select_name}" class="history-user-select form-select" onchange="this.form.submit()">
        {$options_html}
    </select>
</form>

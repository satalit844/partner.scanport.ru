<form method="get" class="certificates-page__toolbar certificates-user-select-form">
    <div class="certificates-user-select-wrap">
        <select name="{$select_name}" class="certificates-user-select" onchange="this.form.submit()" aria-label="Выбрать пользователя">
            {$select_options_html}
        </select>
    </div>
</form>

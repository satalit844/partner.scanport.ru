<div class="section-block tests-block">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="test-header">
                <img src="theme/images/training/tests/logo-scanport.svg" alt="Сканпорт" class="img-svg">
            </div>
            <div class="test-content text-left text-md-center mb-auto my-md-auto">
                <div class="test-title">{$title}</div>
                <div class="test-subtitle mt-2 mt-md-0">{$description_html}</div>
                <div class="text-info-test mt-4 d-inline-flex gap-2 align-items-center"><span>Попытки</span><span>{$attempts_text}</span></div>
            </div>
            <div class="test-footer d-flex flex-wrap align-items-center justify-content-between gap-2 gap-sm-4">
                {$back_button_html}
                <a href="{$instruction_url}" class="btn btn-test col-auto text-decoration-none">
                    <span>Начать тест</span>
                    <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                </a>
            </div>
        </div>
    </div>
</div>

<div class="section-block tests-block tests-block--instruction">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="test-header">
                <img src="theme/images/training/tests/logo-scanport.svg" alt="Сканпорт" class="img-svg">
            </div>

            <div class="test-content text-left mb-auto">
                <div class="test-title">Инструкция по прохождению теста</div>

                <div class="test-subtitle mb-4">
                    <ul class="test-instruction-list">
                        <li>Перед ответом внимательно прочитайте текст вопроса</li>
                        <li>Затем выберите правильный вариант ответа. В некоторых вопросах может быть несколько правильных ответов</li>
                        <li>Нажмите кнопку <b>“Ответить”</b> для подтверждения ответа</li>
                        <li>Для просмотра и выбора вопросов используйте <b>Список вопросов</b> слева вверху</li>
                    </ul>
                </div>

                <div class="text-info-test mt-3">Проходной балл: {$min_pass_percent}%</div>
                <div class="text-info-test mt-2">Попытки: {$attempts_text}</div>
            </div>

            <div class="test-footer d-flex flex-wrap align-items-center justify-content-between gap-2 gap-sm-4">
                {$back_button_html}
                <a href="{$start_url}" class="btn btn-test col-auto text-decoration-none">
                    <span>Начать тест</span>
                    <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                </a>
            </div>
        </div>
    </div>
</div>
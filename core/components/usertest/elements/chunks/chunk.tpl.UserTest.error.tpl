<div id="testuser-main">
{set $backUrl = $_modx->getPlaceholder('training_activity_back_url')}
{set $restartUrl = $_modx->getPlaceholder('training_activity_restart_url')}

<div class="section-block tests-block results-block">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="test-header d-flex align-items-center justify-content-between mb-4">
                <img src="theme/images/training/tests/logo-scanport.svg" alt="Сканпорт" class="img-svg">
            </div>

            <div class="test-content-list text-left mb-auto">
                <div class="test-question d-flex align-items-center gap-3 mb-4">
                    <img src="theme/images/training/tests/circle-x.svg" class="img-svg test-finish-icon"><span>Ошибка</span>
                </div>
                <div class="test-result-list d-flex flex-column gap-3 mb-4">
                    <div class="test-result-item">
                        <div class="result-value">{$error}</div>
                    </div>
                </div>
            </div>

            <div class="test-footer d-flex flex-wrap align-items-center justify-content-end gap-2 gap-sm-4">
                {if $restartUrl}
                    <a href="{$restartUrl}" class="btn btn-test btn-reload-test col-auto text-decoration-none">
                        <span>Пройти тест заново</span>
                        <img src="theme/images/training/tests/arrow-rotate-ico.svg" class="img-svg d-none d-sm-block">
                    </a>
                {/if}
                {if $backUrl}
                    <a href="{$backUrl}" class="btn btn-test col-auto text-decoration-none">
                        <span>Назад к курсу</span>
                        <img src="theme/images/training/tests/arrow-rotate-ico.svg" class="img-svg d-none d-sm-block">
                    </a>
                {/if}
            </div>
        </div>
    </div>
</div>
</div>

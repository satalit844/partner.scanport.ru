<div class="section-block course-page mb-3">
    <div class="course-hero mb-0">
        <div class="course-hero__media d-none d-md-block">
            <div class="course-hero__img">
                <img src="{$activity_image}" alt="{$activity_title}">
            </div>
        </div>
        <div class="course-hero__body d-flex flex-column align-items-start gap-4">
            <div class="w-100 d-flex align-items-center justify-content-between gap-3 flex-wrap">
                <a href="{$back_url}" class="subtitle text-decoration-none">← Обучение</a>
                {$status_chip_html}
            </div>
            <div class="course-hero__title">{$activity_title}</div>
            <div class="course-chip">
                <span class="course-chip__txt">{$activity_type_label}</span>
            </div>
            <div class="course-about__text is-full" style="max-height:none;">
                {$activity_description_html}
            </div>
            {$attempts_counter_html}
        </div>
    </div>
</div>

<div class="section-block tests-block">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
                <a href="{$attempts_tab_url}" class="course-filter__chip{$attempts_tab_class} text-decoration-none">Мои попытки</a>
                <a href="{$comments_tab_url}" class="course-filter__chip{$comments_tab_class} text-decoration-none">Комментарии</a>
            </div>

            <div class="test-content-list text-left mb-auto">
                {if $active_tab == 'comments'}
                    {$messages_rows_html}
                    {$composer_html}
                {else}
                    {$attempts_rows_html}
                {/if}
            </div>

            {if $active_tab != 'comments'}
                <div class="test-footer d-flex flex-wrap align-items-center justify-content-end gap-2 gap-sm-4">
                    {$composer_html}
                </div>
            {/if}
        </div>
    </div>
</div>

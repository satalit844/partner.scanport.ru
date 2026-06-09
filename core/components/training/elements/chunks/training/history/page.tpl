<div class="section-block training-history-page">
    <div class="d-flex align-items-center justify-content-between mb-3 gap-3 flex-wrap">
        <div class="title-training">{$history_title}</div>
        {$history_user_select_html}
    </div>

    <div class="my-hystory mb-3">
        <div class="subtitle">{$history_subtitle}</div>
    </div>

    <div class="history-table d-none d-xxl-block">
        <div class="history-table__head">
            <div class="history-col history-col--date">
                <a href="{$history_sort_url}" class="history-sort-link{$history_sort_class}">
                    <span>Дата/Время</span>
                    <span class="history-sort" aria-hidden="true">
                        <img src="theme/images/training/history/arrow-up.svg" class="img-svg" alt="">
                    </span>
                </a>
            </div>
            <div class="history-col history-col--mat">Материалы</div>
            <div class="history-col history-col--status">Статус</div>
            <div class="history-col history-col--viewed">Просмотрено</div>
            <div class="history-col history-col--score">Баллы</div>
            <div class="history-col history-col--dur">Продолж.</div>
        </div>
        {$history_table_rows_html}
    </div>

    <div class="history-cards d-block d-xxl-none">
        <div class="row g-3">
            {$history_cards_html}
        </div>
    </div>
</div>

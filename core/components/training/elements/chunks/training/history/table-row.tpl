<div class="history-table__row js-history-item" data-history-id="1">
    <div class="history-col history-col--date">
        <div class="history-date">{$date}</div>
        <div class="history-time">{$time}</div>
    </div>

    <div class="history-col history-col--mat">
        <div class="history-mat">
            <span class="history-mat__ico">
                <img src="{$icon}" class="img-svg" alt="">
            </span>
            <div class="history-mat__title">{$title}</div>
        </div>
    </div>

    <div class="history-col history-col--status">
        <div class="label-chip {$status_class}"><span>{$status_text}</span></div>
    </div>

    <div class="history-col history-col--viewed">{$viewed_html}</div>
    <div class="history-col history-col--score">{$score_html}</div>
    <div class="history-col history-col--dur">{$duration_text}</div>
</div>

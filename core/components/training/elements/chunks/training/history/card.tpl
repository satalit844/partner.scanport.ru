<div class="col-12 col-md-6">
    <div class="lesson-card history-card js-history-item" data-history-id="1">
        <div class="lesson-card__top">
            <div class="lesson-date"><span class="history-card__date">{$date}</span> <span class="history-card__time">{$time}</span></div>
            <div class="label-chip {$status_class} label-chip--sm"><span class="history-card__status">{$status_text}</span></div>
        </div>

        <div class="lesson-title">
            <span class="lesson-ico">
                <img src="{$icon}" alt="" class="img-svg">
            </span>
            <div class="lesson-name history-card__title">{$title}</div>
        </div>

        <div class="history-card__meta d-none">
            <div class="history-card__kv"><span class="history-card__v">{$viewed_html}</span></div>
            <div class="history-card__kv"><span class="history-card__v">{$score_html}</span></div>
            <div class="history-card__kv"><span class="history-card__v">{$duration_text}</span></div>
        </div>

        <div class="lesson-card__bottom">
            <div class="lesson-progress">
                <span class="lesson-progress__percent">{$viewed_html}</span>
            </div>
            <div class="lesson-time">{$duration_text}</div>
        </div>
    </div>
</div>

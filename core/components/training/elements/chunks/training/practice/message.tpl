<div class="practice-message {$message_class}">
    <div class="practice-message__avatar">
        <img src="{$author_avatar}" alt="" loading="lazy">
    </div>

    <div class="practice-message__content">
        <div class="practice-message__author">{$author_name}</div>

        <div class="practice-message__bubble">
            <div class="practice-message__text">
                {$message_text}
            </div>

            <div class="practice-message__files">
                {$files_html}
            </div>

            <div class="practice-message__time">{$message_date}</div>
        </div>
    </div>
</div>

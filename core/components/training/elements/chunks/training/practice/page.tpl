<div class="section-block practice-page"
     data-training-page="practice"
     data-training-connector="{$connector_url}"
     data-training-context="{$context_key}"
     data-practice-id="{$practice_id}">
    <div class="practice-task-card">
        <div class="practice-task-card__media d-none d-lg-flex">
            <img src="{$practice_image}" alt="{$practice_title}" class="img-svg">
        </div>

        <div class="practice-task-card__body">
            <div class="practice-task-card__top d-flex d-lg-none">
                <div class="d-flex flex-column w-100">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <img src="{$practice_image}" alt="{$practice_title}" class="practice-image">
                        <div class="practice-status {$status_class}" data-practice-status>{$status_text}</div>
                    </div>
                    <h1 class="practice-task-card__title">{$practice_title}</h1>
                </div>
            </div>
            <div class="practice-task-card__top d-none d-lg-flex">
                <h1 class="practice-task-card__title">{$practice_title}</h1>
                <div class="practice-status {$status_class}" data-practice-status>{$status_text}</div>
            </div>

            <div class="practice-task-card__subtitle">Задание</div>

            <div class="practice-task-card__text" data-practice-description>
                {$practice_description}
            </div>

            <button type="button" class="practice-more-btn" data-practice-more>Показать больше</button>
        </div>
    </div>

    <div class="practice-chat-card" data-practice-tabs>
        <div class="practice-tabs">
            <button type="button" class="practice-tab is-active" data-practice-tab="attempts">Мои попытки</button>
            <button type="button" class="practice-tab" data-practice-tab="comments">Комментарии</button>
        </div>

        <div class="practice-panel is-active" data-practice-panel="attempts">
            <div class="practice-panel__title">Новая попытка</div>

            <div class="practice-messages" data-practice-messages>
                {$messages_html}
            </div>
        </div>

        <div class="practice-panel" data-practice-panel="comments">
            <div class="practice-panel__title">Комментарии ментора</div>

            <div class="practice-messages" data-practice-comments>
                {$comments_html}
            </div>
        </div>

        {$form_html}
    </div>
</div>

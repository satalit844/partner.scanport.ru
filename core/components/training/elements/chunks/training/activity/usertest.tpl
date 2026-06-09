<div id="testuser-main">
{set $activityId = $.get.activity ?: 0}
{set $backId = $.get.back ?: 0}
{set $resourceId = $_modx->resource.id}
{set $backUrl = $_modx->getPlaceholder('training_activity_back_url')}
{if !$backUrl && $backId}
    {set $backUrl = $_modx->makeUrl($backId)}
{/if}
{set $restartUrl = $_modx->getPlaceholder('training_activity_restart_url')}
{if !$restartUrl && $activityId}
    {set $restartUrl = $_modx->makeUrl($resourceId, '', ['activity' => $activityId, 'back' => $backId, 'screen' => 'test', 'step' => 'start', 'reset' => 1])}
{/if}
{set $minPassPercent = $_modx->getPlaceholder('training_activity_min_pass_percent')}
{set $displayTestPoint = $test_point ?: 0}
{set $displayMaxPoint = $max_point ?: 0}
{set $scorePercent = 0}
{if $displayMaxPoint > 0}
    {set $scorePercent = (($displayTestPoint / $displayMaxPoint) * 100) | round : 0}
{/if}
{set $effectivePassed = $var_passed ? true : false}
{if !$effectivePassed && $minPassPercent && $displayMaxPoint > 0}
    {set $effectivePassed = $scorePercent >= $minPassPercent}
{/if}
{set $currentQuestionId = 0}
{if $questions}
    {foreach $questions as $qCurrent}
        {set $currentQuestionId = $qCurrent.id}
        {break}
    {/foreach}
{/if}
{set $nextStepUrl = ''}
{if $activityId && $nextStep}
    {set $nextStepUrl = $_modx->makeUrl($resourceId, '', ['activity' => $activityId, 'back' => $backId, 'screen' => 'test', 'step' => $nextStep])}
{/if}

<div class="section-block tests-block{if $curStep == 'finish'} results-block{/if}">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="test-header d-flex align-items-center justify-content-between mb-4">
                <img src="theme/images/training/tests/logo-scanport.svg" alt="Сканпорт" class="img-svg">
                {if $curStep != 'finish' && $questions}
                    {foreach $questions as $q}
                        <div class="test-step-box">Вопрос {$q.numberQ}/{$q.countQ}</div>
                        {break}
                    {/foreach}
                {/if}
            </div>

            {if $curStep == 'finish'}
                <div class="test-content-list text-left mb-auto">
                    <div class="test-question d-flex align-items-center gap-3 mb-4">
                        {if $effectivePassed}
                            <img src="theme/images/training/tests/circle-check-big.svg" class="img-svg test-finish-icon"><span>Вы прошли тест</span>
                        {else}
                            <img src="theme/images/training/tests/circle-x.svg" class="img-svg test-finish-icon"><span>Вы не прошли тест</span>
                        {/if}
                    </div>
                    <div class="test-result-list d-flex flex-column gap-3 mb-4">
                        <div class="test-result-item">
                            <div class="result-label">Набрано:</div>
                            <div class="result-value">{$scorePercent}% ({$displayTestPoint} баллов)</div>
                        </div>
                        {if $minPassPercent}
                            <div class="test-result-item">
                                <div class="result-label">Проходной балл:</div>
                                <div class="result-value">{$minPassPercent}% ({$displayMaxPoint} баллов)</div>
                            </div>
                        {/if}
                    </div>
                    {if $answer_page_url}
                        <div class="test-result-link">
                            <a href="{$answer_page_url}" class="btn-list-test text-decoration-none">
                                <img src="theme/images/training/tests/list-ico.svg" class="img-svg">
                                <span>Посмотреть тест</span>
                            </a>
                        </div>
                    {/if}
                </div>
                <div class="test-footer d-flex flex-wrap align-items-center justify-content-end gap-2 gap-sm-4">
                    <a href="{$restartUrl}" class="btn btn-test btn-reload-test col-auto text-decoration-none">
                        <span>Пройти тест заново</span>
                        <img src="theme/images/training/tests/arrow-rotate-ico.svg" class="img-svg d-none d-sm-block">
                    </a>
                    {if $backUrl}
                        <a href="{$backUrl}" class="btn btn-test col-auto text-decoration-none">
                            <span>Закрыть</span>
                            <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                        </a>
                    {/if}
                </div>
            {else}
                {set $pageAnswered = true}
                {foreach $questions as $q}
                    {if $q.type == 1 || $q.type == 12}
                        {if !$q.answer_id}
                            {set $pageAnswered = false}
                        {/if}
                    {elseif $q.type == 2}
                        {set $answeredThis = false}
                        {foreach $q.answers as $a}
                            {if $a.check}
                                {set $answeredThis = true}
                            {/if}
                        {/foreach}
                        {if !$answeredThis}
                            {set $pageAnswered = false}
                        {/if}
                    {elseif $q.type == 3 || $q.type == 4}
                        {if !$q.answer}
                            {set $pageAnswered = false}
                        {/if}
                    {else}
                        {if !$q.is_answered}
                            {set $pageAnswered = false}
                        {/if}
                    {/if}
                {/foreach}

                <form id="UserTestForm" action="" method="post" class="h-100 d-flex flex-column" data-page-answered="{if $pageAnswered}1{else}0{/if}" data-next-url="{$nextStepUrl}">
                    <input type="hidden" name="test_id" value="{$test_id}">
                    <input type="hidden" name="step" id="next_step" value="{if $pageAnswered}{$nextStep}{else}{$curStep}{/if}">
                    <input type="hidden" name="answer_step" id="answer_step" value="{$curStep}">

                    <div class="test-content-list text-left mb-4 flex-grow-1">
                        {foreach $questions as $q}
                            <div class="test-question mb-4">{$q.question}</div>

                            {set $answerStateText = ''}
                            {set $answerStateClass = ''}

                            {if $q.type == 1 || $q.type == 12}
                                {set $selectedRight = false}
                                {if $q.answer_id}
                                    {foreach $q.answers as $a}
                                        {if $a.id == $q.answer_id && $a.right}
                                            {set $selectedRight = true}
                                        {/if}
                                    {/foreach}
                                    {if $selectedRight}
                                        {set $answerStateText = 'Правильно'}
                                        {set $answerStateClass = 'is-correct'}
                                    {else}
                                        {set $answerStateText = 'Неправильно'}
                                        {set $answerStateClass = 'is-wrong'}
                                    {/if}
                                {/if}

                                <div class="test-answer d-flex flex-column gap-3">
                                    {foreach $q.answers as $a}
                                        {set $rowClass = ''}
                                        {if $q.answer_id}
                                            {if $a.right}
                                                {set $rowClass = 'is-right-answer'}
                                            {/if}
                                            {if $a.id == $q.answer_id && !$a.right}
                                                {set $rowClass = 'is-wrong-answer'}
                                            {/if}
                                        {/if}
                                        <div class="form-label ut-answer-row {$rowClass}">
                                            <label class="custom-radio-test">
                                                <input type="radio" name="question[{$q.id}]" value="{$a.id}"{if $q.answer_id == $a.id} checked{/if}{if $q.answer_id} disabled{/if}>
                                                <span class="radio-mark"></span>
                                                <div class="text-answer">{$a.answer}</div>
                                            </label>
                                        </div>
                                    {/foreach}
                                </div>
                            {elseif $q.type == 2}
                                {set $selectedCount = 0}
                                {set $selectedRightCount = 0}
                                {set $selectedWrongCount = 0}
                                {set $rightCount = 0}
                                {foreach $q.answers as $a}
                                    {if $a.right}
                                        {set $rightCount = $rightCount + 1}
                                    {/if}
                                    {if $a.check}
                                        {set $selectedCount = $selectedCount + 1}
                                        {if $a.right}
                                            {set $selectedRightCount = $selectedRightCount + 1}
                                        {else}
                                            {set $selectedWrongCount = $selectedWrongCount + 1}
                                        {/if}
                                    {/if}
                                {/foreach}
                                {if $selectedCount > 0}
                                    {if $selectedWrongCount == 0 && $selectedRightCount == $rightCount}
                                        {set $answerStateText = 'Правильно'}
                                        {set $answerStateClass = 'is-correct'}
                                    {elseif $selectedRightCount > 0}
                                        {set $answerStateText = 'Частично верно'}
                                        {set $answerStateClass = 'is-partial'}
                                    {else}
                                        {set $answerStateText = 'Неправильно'}
                                        {set $answerStateClass = 'is-wrong'}
                                    {/if}
                                {/if}

                                <div class="test-answer d-flex flex-column gap-3">
                                    {foreach $q.answers as $a}
                                        {set $rowClass = ''}
                                        {if $selectedCount > 0}
                                            {if $a.right}
                                                {set $rowClass = 'is-right-answer'}
                                            {/if}
                                            {if $a.check && !$a.right}
                                                {set $rowClass = 'is-wrong-answer'}
                                            {/if}
                                        {/if}
                                        <div class="form-label ut-answer-row {$rowClass}">
                                            <label class="custom-checkbox-test">
                                                <input type="checkbox" name="question[{$q.id}][]" value="{$a.id}"{if $a.check} checked{/if}{if $selectedCount > 0} disabled{/if}>
                                                <span class="checkbox-mark"></span>
                                                <div class="text-answer">{$a.answer}</div>
                                            </label>
                                        </div>
                                    {/foreach}
                                </div>
                            {elseif $q.type == 3 || $q.type == 4}
                                <div class="test-answer d-flex flex-column gap-3">
                                    <div class="form-label">
                                        <input type="text" class="form-control" name="question[{$q.id}]" value="{$q.answer|escape}"{if $q.answer} readonly{/if}>
                                    </div>
                                </div>
                                {if $q.answer}
                                    {if $q.type == 4}
                                        {set $answerStateText = 'На проверке'}
                                        {set $answerStateClass = 'is-review'}
                                    {else}
                                        {set $answerStateText = 'Ответ сохранён'}
                                        {set $answerStateClass = 'is-current'}
                                    {/if}
                                {/if}
                            {elseif $q.answers}
                                {* UserTest has extra question types, including order/sort questions. Render them safely as sortable answer list. *}
                                {if $q.is_answered}
                                    {set $answerStateText = 'Ответ сохранён'}
                                    {set $answerStateClass = 'is-current'}
                                {/if}
                                <div class="test-answer d-flex flex-column gap-3 ut-sort-answer" data-ut-sort-question="{$q.id}">
                                    {foreach $q.answers as $a index=$sortIndex}
                                        <div class="form-label ut-answer-row ut-sort-row" draggable="{if $q.is_answered}false{else}true{/if}">
                                            {if !$q.is_answered}
                                                <input type="hidden" name="question[{$q.id}][]" value="{$a.id}">
                                            {/if}
                                            <div class="custom-sort-test">
                                                <span class="ut-sort-index">{$sortIndex + 1}</span>
                                                <button type="button" class="ut-sort-move ut-sort-move--up" aria-label="Выше"{if $q.is_answered} disabled{/if}>↑</button>
                                                <button type="button" class="ut-sort-move ut-sort-move--down" aria-label="Ниже"{if $q.is_answered} disabled{/if}>↓</button>
                                                <div class="text-answer">{$a.answer}</div>
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {else}
                                <div class="test-answer d-flex flex-column gap-3">
                                    <div class="ut-answer-empty">
                                        У этого вопроса нет вариантов ответа. Проверьте привязанные ответы в UserTest.
                                    </div>
                                </div>
                            {/if}

                            {if $answerStateText}
                                <div class="ut-answer-result-wrap">
                                    <div class="ut-answer-result {$answerStateClass}">
                                        {if $answerStateClass == 'is-correct'}
                                            <img src="theme/images/training/tests/circle-check-big.svg" class="img-svg ut-answer-result__img" alt="">
                                        {elseif $answerStateClass == 'is-wrong'}
                                            <img src="theme/images/training/tests/circle-x.svg" class="img-svg ut-answer-result__img" alt="">
                                        {elseif $answerStateClass == 'is-partial'}
                                            <img src="theme/images/training/tests/circle-alert.svg" class="img-svg ut-answer-result__img" alt="">
                                        {/if}
                                        <span>{$answerStateText}</span>
                                    </div>
                                </div>
                            {/if}
                        {/foreach}
                    </div>

                    <div class="test-footer d-flex flex-wrap align-items-center justify-content-between mt-auto">
                        <button type="button" class="btn-list-test js-test-list-open mb-2 mb-sm-0" data-bs-toggle="modal" data-bs-target="#testListModal">
                            <img src="theme/images/training/tests/list-ico.svg" class="img-svg">
                            <span>Список вопросов</span>
                        </button>
                        <div class="text-info-test ms-auto me-4 mb-2 mb-sm-0">
                            <span class="d-none d-lg-inline-block">Набрано баллов</span>
                            <span>{$displayTestPoint} из {$displayMaxPoint}</span>
                            <span class="balls-text d-block d-lg-none">Баллы</span>
                        </div>
                        {if $pageAnswered}
                            <button type="button" class="btn btn-test col-auto js-ut-next-step" data-ut-next-url="{$nextStepUrl}">
                                <span>{if $nextStep == 'finish'}Смотреть результаты{else}Продолжить{/if}</span>
                                <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                            </button>
                        {else}
                            <button type="submit" class="btn btn-test col-auto">
                                <span>Ответить</span>
                                <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                            </button>
                        {/if}
                    </div>
                </form>

                <div class="modal fade testListModal" id="testListModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog testListModal__dialog modal-dialog-centered">
                        <div class="modal-content testListModal__content">
                            <button type="button" class="testListModal__close" data-bs-dismiss="modal" aria-label="Закрыть">
                                <span aria-hidden="true">×</span>
                            </button>
                            <div class="testListModal__head">
                                <div class="testListModal__grid testListModal__grid--head">
                                    <div></div>
                                    <div>Вопрос</div>
                                    <div>Результат</div>
                                    <div>Баллы</div>
                                </div>
                            </div>
                            <div class="testListModal__body">
                                <div class="testListModal__list">
                                    {foreach $block_q_number as $q}
                                        <div class="testListModal__row js-test-step {if $q.question_id == $currentQuestionId}is-current-step{/if}" data-step="{$q.step}">
                                            <div class="testListModal__number">{$q.numberQ}</div>
                                            <div class="testListModal__question">{$q.title}</div>
                                            <div class="testListModal__result"><span class="ut-mini-badge {$q.status_class}">{$q.status_text}</span></div>
                                            <div class="testListModal__score">{$q.point_text}</div>
                                        </div>
                                    {/foreach}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <script>
                    (function () {
                        var modalEl = document.getElementById('testListModal');
                        var modal = null;
                        if (modalEl && window.bootstrap && bootstrap.Modal) {
                            modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                        }

                        document.querySelectorAll('.js-test-step').forEach(function (row) {
                            row.addEventListener('click', function () {
                                var step = this.getAttribute('data-step');
                                var form = document.getElementById('UserTestForm');
                                var next = document.getElementById('next_step');
                                if (modal) {
                                    modal.hide();
                                }
                                if (form && form.getAttribute('data-page-answered') === '1') {
                                    var url = new URL(window.location.href);
                                    url.searchParams.set('screen', 'test');
                                    url.searchParams.set('step', step);
                                    window.location.href = url.toString();
                                    return;
                                }
                                if (!form || !next) return;
                                next.value = step;
                                form.submit();
                            });
                        });

                        document.querySelectorAll('[data-ut-sort-question]').forEach(function (list) {
                            var dragged = null;
                            var submit = document.querySelector('#UserTestForm .btn-test[type="submit"]');

                            if (submit) {
                                submit.disabled = false;
                                submit.removeAttribute('disabled');
                            }

                            function updateNumbers() {
                                list.querySelectorAll('.ut-sort-row').forEach(function (row, index) {
                                    var number = row.querySelector('.ut-sort-index');
                                    if (number) {
                                        number.textContent = index + 1;
                                    }
                                });
                            }

                            function moveRow(row, direction) {
                                if (!row) return;
                                if (direction < 0 && row.previousElementSibling) {
                                    list.insertBefore(row, row.previousElementSibling);
                                }
                                if (direction > 0 && row.nextElementSibling) {
                                    list.insertBefore(row.nextElementSibling, row);
                                }
                                updateNumbers();
                            }

                            list.addEventListener('click', function (e) {
                                var up = e.target.closest('.ut-sort-move--up');
                                var down = e.target.closest('.ut-sort-move--down');
                                if (!up && !down) return;
                                e.preventDefault();
                                var row = e.target.closest('.ut-sort-row');
                                moveRow(row, up ? -1 : 1);
                            });

                            list.addEventListener('dragstart', function (e) {
                                var row = e.target.closest('.ut-sort-row');
                                if (!row || row.getAttribute('draggable') === 'false') return;
                                dragged = row;
                                row.classList.add('is-dragging');
                                if (e.dataTransfer) {
                                    e.dataTransfer.effectAllowed = 'move';
                                }
                            });

                            list.addEventListener('dragover', function (e) {
                                if (!dragged) return;
                                e.preventDefault();
                                var target = e.target.closest('.ut-sort-row');
                                if (!target || target === dragged) return;
                                var rect = target.getBoundingClientRect();
                                var before = e.clientY < rect.top + rect.height / 2;
                                list.insertBefore(dragged, before ? target : target.nextSibling);
                                updateNumbers();
                            });

                            list.addEventListener('dragend', function () {
                                if (dragged) {
                                    dragged.classList.remove('is-dragging');
                                }
                                dragged = null;
                                updateNumbers();
                            });

                            updateNumbers();
                        });


                        document.addEventListener('click', function (event) {
                            var button = event.target.closest('.js-ut-next-step');
                            if (!button) return;

                            var url = button.getAttribute('data-ut-next-url');
                            if (!url) return;

                            event.preventDefault();
                            event.stopPropagation();
                            if (event.stopImmediatePropagation) {
                                event.stopImmediatePropagation();
                            }

                            window.location.href = url;
                        }, true);

                        document.addEventListener('submit', function (event) {
                            var form = event.target;
                            if (!form || form.id !== 'UserTestForm') return;
                            if (form.getAttribute('data-page-answered') !== '1') return;

                            var url = form.getAttribute('data-next-url');
                            if (!url) return;

                            event.preventDefault();
                            event.stopPropagation();
                            if (event.stopImmediatePropagation) {
                                event.stopImmediatePropagation();
                            }

                            window.location.href = url;
                        }, true);

                    })();
                </script>
            {/if}
        </div>
    </div>
</div>
</div>

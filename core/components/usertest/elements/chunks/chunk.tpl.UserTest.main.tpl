<div id="testuser-main" data-ut-finished="{if $curStep == 'finish'}1{else}0{/if}" data-ut-current-url="{$training_current_url}">
{set $activityId = $training_activity_id ?: ($.get.activity ?: 0)}
{set $backId = $.get.back ?: 0}
{set $resourceId = $_modx->resource.id}
{set $backUrl = $training_back_url ?: $_modx->getPlaceholder('training_activity_back_url')}
{if !$backUrl && $backId}
    {set $backUrl = $_modx->makeUrl($backId)}
{/if}
{set $restartUrl = $training_restart_url ?: $_modx->getPlaceholder('training_activity_restart_url')}
{set $answerViewUrl = $training_answer_url ?: $answer_page_url}
{set $minPassPercent = $min_pass_percent ?: $_modx->getPlaceholder('training_activity_min_pass_percent')}
{set $displayTestPoint = $test_point ?: 0}
{set $displayMaxPoint = $max_point ?: 0}
{set $scorePercent = 0}
{if $displayMaxPoint > 0}
    {set $scorePercent = (($displayTestPoint / $displayMaxPoint) * 100) | round : 0}
{/if}
{set $displayPassPoint = 0}
{if $minPassPercent > 0}
    {if $displayMaxPoint > 0}
        {set $displayPassPoint = (($displayMaxPoint * $minPassPercent) / 100) | round : 0}
    {/if}
{/if}
{set $effectivePassed = $var_passed ? true : false}
{if !$effectivePassed && $minPassPercent > 0}
    {if $displayMaxPoint > 0}
        {set $effectivePassed = $scorePercent >= $minPassPercent}
    {/if}
{/if}
{if !$effectivePassed && $scorePercent >= 100}
    {set $effectivePassed = true}
{/if}
{set $currentQuestionId = 0}
{set $currentQuestionNumber = 0}
{set $currentQuestionCount = 0}
{if $questions}
    {foreach $questions as $qCurrent}
        {set $currentQuestionId = $qCurrent.id}
        {set $currentQuestionNumber = $qCurrent.numberQ}
        {set $currentQuestionCount = $qCurrent.countQ}
        {break}
    {/foreach}
{/if}
{set $currentQuestionLabel = ''}
{if $currentQuestionNumber && $currentQuestionCount}
    {set $currentQuestionLabel = 'Вопрос ' ~ $currentQuestionNumber ~ '/' ~ $currentQuestionCount}
{/if}

<div class="section-block tests-block{if $curStep == 'finish'} results-block{/if}">
    <div class="page-content d-flex flex-column h-100">
        <div class="test-card position-relative w-100 d-flex flex-column">
            <div class="test-header d-flex align-items-center justify-content-between mb-4">
                <img src="theme/images/training/tests/logo-scanport.svg" alt="Сканпорт" class="img-svg">
                {if $curStep != 'finish' && $currentQuestionLabel}
                    <div class="test-step-box js-ut-question-counter" data-number="{$currentQuestionNumber}" data-count="{$currentQuestionCount}">{$currentQuestionLabel}</div>
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
                    <div class="test-result-summary">
                        <div class="test-result-list d-flex flex-column gap-3">
                            <div class="test-result-item">
                                <div class="result-label">Набрано:</div>
                                <div class="result-value">{$scorePercent}% ({$displayTestPoint} баллов)</div>
                            </div>
                            {if $minPassPercent > 0}
                                <div class="test-result-item test-result-item--pass">
                                    <div class="result-label">Проходной балл:</div>
                                    <div class="result-value">{$minPassPercent}% ({$displayPassPoint} баллов)</div>
                                </div>
                            {/if}
                        </div>
                        {if $block_q_number}
                            <div class="test-result-link">
                                <button type="button" class="btn-list-test js-test-list-open text-decoration-none">
                                    <img src="theme/images/training/tests/list-ico.svg" class="img-svg">
                                    <span>Посмотреть тест</span>
                                </button>
                            </div>
                        {elseif $answerViewUrl}
                            <div class="test-result-link">
                                <a href="{$answerViewUrl}" class="btn-list-test text-decoration-none">
                                    <img src="theme/images/training/tests/list-ico.svg" class="img-svg">
                                    <span>Посмотреть тест</span>
                                </a>
                            </div>
                        {/if}
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
                            <span>Закрыть</span>
                            <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                        </a>
                    {/if}
                </div>

                {if $block_q_number}
                    <div class="modal fade testListModal" id="testListModal" tabindex="-1" aria-hidden="true" data-static-result="1">
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
                                            <div class="testListModal__row is-result-row">
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
                {/if}
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

                <form id="UserTestForm" action="" method="post" class="h-100 d-flex flex-column" data-question-number="{$currentQuestionNumber}" data-question-count="{$currentQuestionCount}" data-question-label="{$currentQuestionLabel}" data-requires-choice="{if $pageAnswered}0{else}1{/if}">
                    {if $currentQuestionLabel}
                        <div class="js-ut-question-meta d-none" data-number="{$currentQuestionNumber}" data-count="{$currentQuestionCount}" data-label="{$currentQuestionLabel}"></div>
                    {/if}
                    <input type="hidden" name="test_id" value="{$test_id}">
                    <input type="hidden" name="step" id="next_step" value="{if $pageAnswered}{$nextStep}{else}{$curStep}{/if}">
                    <input type="hidden" name="answer_step" id="answer_step" value="{$curStep}">
                    <input type="hidden" name="training_activity_id" value="{$activityId}">
                    <input type="hidden" name="training_activity_page_url" value="{$training_current_url}">
                    <input type="hidden" name="training_activity_back_url" value="{$backUrl}">
                    <input type="hidden" name="training_activity_restart_url" value="{$restartUrl}">
                    <input type="hidden" name="training_activity_answer_url" value="{$training_answer_url_tpl}">
                    <input type="hidden" name="training_activity_min_pass_percent" value="{$minPassPercent}">

                    <div class="test-content-list text-left mb-4 flex-grow-1">
                        {foreach $questions as $q}
                            <div class="question validate" data-id="{$q.id}" data-type_id="{$q.type}">
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
                            </div>
                        {/foreach}
                    </div>

                    <div class="test-footer d-flex flex-wrap align-items-center justify-content-between mt-auto">
                        <button type="button" class="btn-list-test js-test-list-open mb-2 mb-sm-0">
                            <img src="theme/images/training/tests/list-ico.svg" class="img-svg">
                            <span>Список вопросов</span>
                        </button>
                        <div class="text-info-test ms-auto me-4 mb-2 mb-sm-0">
                            <span class="d-none d-lg-inline-block">Набрано баллов</span>
                            <span>{$displayTestPoint} из {$displayMaxPoint}</span>
                            <span class="balls-text d-block d-lg-none">Баллы</span>
                        </div>
                        <button type="submit" class="btn btn-test col-auto js-ut-submit"{if !$pageAnswered} disabled{/if}>
                            {if $pageAnswered}
                                <span>{if $nextStep == 'finish'}Смотреть результаты{else}Продолжить{/if}</span>
                            {else}
                                <span>Ответить</span>
                            {/if}
                            <img src="theme/images/training/tests/arrow-right-ico.svg" class="img-svg d-none d-sm-block">
                        </button>
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

            {/if}
        </div>
    </div>
</div>
</div>

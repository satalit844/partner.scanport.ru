$(document).ready(function () {
    $('body')
        .off('submit.UserTestAjax', '#UserTestForm')
        .on('submit.UserTestAjax', '#UserTestForm', function (e) {
            e.preventDefault();

            var form = $(this);
            if (form.data('ut-submitting')) {
                return false;
            }

            usertestUpdateSubmitState(form);
            if (form.attr('data-requires-choice') === '1' && !usertestFormHasAnswer(form)) {
                return false;
            }

            if (!validate_question() || !validate_ask()) {
                return false;
            }

            form.data('ut-submitting', 1);

            $.ajax({
                type: 'POST',
                url: typeof UserTestActionUrl !== 'undefined' ? UserTestActionUrl : form.attr('action'),
                dataType: 'json',
                data: form.serialize(),
                beforeSend: function () {
                    form.find('input[type="submit"], button[type="submit"]').prop('disabled', true);
                },
                success: function (data) {
                    if (!data || !data.success) {
                        alert(data && data.message ? data.message : 'Ошибка сохранения ответа');
                        return;
                    }

                    var parsed = $.parseHTML(data.html, document, true);
                    var $doc = $('<div>').append(parsed);
                    var $newMain = $doc.find('#testuser-main').first();
                    var oldModal = document.getElementById('testListModal');

                    if (oldModal && typeof bootstrap !== 'undefined') {
                        var oldInstance = bootstrap.Modal.getInstance(oldModal);
                        if (oldInstance) {
                            oldInstance.hide();
                            oldInstance.dispose();
                        }
                    }
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open').css({ overflow: '', paddingRight: '' });

                    if ($newMain.length) {
                        $('#testuser-main').replaceWith($newMain);
                    } else {
                        $('#testuser-main').replaceWith(data.html);
                    }

                    var $currentMain = $('#testuser-main').first();
                    var $currentModal = $currentMain.find('#testListModal').first();

                    $('#testListModal').not($currentModal).remove();

                    if (!$currentModal.length) {
                        var $fallbackModal = $doc.find('#testListModal').first();
                        if ($fallbackModal.length) {
                            $('body').append($fallbackModal);
                        }
                    }

                    var $testMain = $('#testuser-main');
                    usertestSyncQuestionCounter($testMain.length ? $testMain : $doc);
                    usertestSyncBrowserUrl($testMain.length ? $testMain : $doc);

                    if (window.AppTraining && typeof window.AppTraining.testListModal === 'function') {
                        window.AppTraining.testListModal();
                    }

                    usertestInitSubmitState();

                    if ($testMain.length && $testMain.offset()) {
                        $('html, body').animate({ scrollTop: $testMain.offset().top }, 300);
                    }

                    if (typeof window.CustomEvent === 'function') {
                        document.dispatchEvent(new CustomEvent('usertest:updated', {
                            detail: {
                                html: data.html
                            }
                        }));
                    }

                    var ctx = document.getElementById('chart-area');
                    if (ctx && typeof Chart !== 'undefined' && typeof config !== 'undefined') {
                        window.myPie = new Chart(ctx.getContext('2d'), config);
                    }
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    alert(xhr.status);
                    alert(thrownError);
                },
                complete: function () {
                    form.data('ut-submitting', 0);
                    form.find('input[type="submit"], button[type="submit"]').prop('disabled', false);
                }
            });

            return false;
        });

    usertestSyncQuestionCounter($('#testuser-main'));
    usertestSyncBrowserUrl($('#testuser-main'));
    usertestInitSubmitState();

    $('body')
        .off('change.UserTestChoice input.UserTestChoice', '#UserTestForm input, #UserTestForm textarea, #UserTestForm select')
        .on('change.UserTestChoice input.UserTestChoice', '#UserTestForm input, #UserTestForm textarea, #UserTestForm select', function () {
            usertestUpdateSubmitState($(this).closest('#UserTestForm'));
        });
});

function usertestFormHasAnswer(form) {
    var $form = form && form.length ? form : $('#UserTestForm');
    if (!$form.length) {
        return true;
    }

    var hasAnswer = false;
    $form.find('.question.validate').each(function () {
        var $question = $(this);
        var type = parseInt($question.attr('data-type_id') || 0, 10) || 0;

        if (type === 1 || type === 12) {
            if ($question.find('input:radio:checked').length > 0) {
                hasAnswer = true;
                return false;
            }
        } else if (type === 2) {
            if ($question.find('input:checkbox:checked').length > 0) {
                hasAnswer = true;
                return false;
            }
        } else if (type === 3 || type === 4) {
            var value = $.trim($question.find('input[type="text"], textarea').first().val() || '');
            if (value !== '') {
                hasAnswer = true;
                return false;
            }
        } else {
            if ($question.find('input:checked').length > 0 || $.trim($question.find('input, textarea').first().val() || '') !== '') {
                hasAnswer = true;
                return false;
            }
        }
    });

    return hasAnswer;
}

function usertestUpdateSubmitState(form) {
    var $form = form && form.length ? form : $('#UserTestForm');
    if (!$form.length) {
        return;
    }

    var requiresChoice = $form.attr('data-requires-choice') === '1';
    var disabled = requiresChoice && !usertestFormHasAnswer($form);
    $form.find('button[type="submit"], input[type="submit"]').prop('disabled', disabled).toggleClass('is-disabled', disabled);
}

function usertestInitSubmitState() {
    $('#UserTestForm').each(function () {
        usertestUpdateSubmitState($(this));
    });
}


function usertestSyncQuestionCounter($source) {
    var label = '';
    var number = 0;
    var count = 0;
    var $src = $source && $source.length ? $source : $(document);
    var $meta = $src.find('.js-ut-question-meta').first();
    var $form = $src.find('#UserTestForm').first();

    if ($meta.length) {
        label = $.trim($meta.attr('data-label') || '');
        number = parseInt($meta.attr('data-number') || 0, 10) || 0;
        count = parseInt($meta.attr('data-count') || 0, 10) || 0;
    }

    if (!label && $form.length) {
        label = $.trim($form.attr('data-question-label') || '');
        number = parseInt($form.attr('data-question-number') || 0, 10) || 0;
        count = parseInt($form.attr('data-question-count') || 0, 10) || 0;
    }

    if (!label && number > 0 && count > 0) {
        label = 'Вопрос ' + number + '/' + count;
    }

    if (!label) {
        return;
    }

    $('.js-ut-question-counter, .test-step-box').each(function () {
        $(this).text(label).attr('data-number', number).attr('data-count', count);
    });
}

function usertestSyncBrowserUrl($source) {
    if (!window.history || !window.history.replaceState) {
        return;
    }

    var $src = $source && $source.length ? $source : $(document);
    var $main = $src.is('#testuser-main') ? $src : $src.find('#testuser-main').first();
    if (!$main.length) {
        return;
    }

    var url = $.trim($main.attr('data-ut-current-url') || '');
    if (!url) {
        return;
    }

    try {
        window.history.replaceState({}, '', url);
    } catch (e) {}
}

function validate_question() {
    var validate = true;
    $('.error').remove();

    $('.question.validate').each(function () {
        var $question = $(this);
        var typeId = parseInt($question.data('type_id'), 10) || 0;
        var needError = false;

        switch (typeId) {
            case 1:
            case 12:
                needError = $question.find('input:radio:checked').length === 0;
                break;

            case 2:
                needError = $question.find('input:checkbox:checked').length === 0;
                break;

            case 3:
            case 4:
                needError = $.trim($question.find('input, textarea').first().val() || '') === '';
                break;

            case 9:
                $question.find('select').each(function () {
                    if ($(this).val() == 0) {
                        needError = true;
                    }
                });
                break;
        }

        if (needError) {
            validate = false;
            $question.append('<div class="w-100 m-0 p-0"></div><p class="error mt-2"><span class="text-danger py-2 px-3 badge rounded-pill text-bg-danger fw-normal">На этот вопрос требуется ответ!</span></p>');
        }
    });

    return validate;
}

function validate_ask() {
    var validate = true;
    $('.ask.validate').each(function () {
        var $ask = $(this);
        if ($.trim($ask.find('input').first().val() || '') === '') {
            validate = false;
            $ask.append('<span class="error text-danger py-2 px-3 badge rounded-pill text-bg-danger fw-normal">Это поле требуется!</span>');
        }
    });
    return validate;
}

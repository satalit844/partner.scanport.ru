<div class="modal fade training-course-access-modal" id="trainingCourseAccessModal" tabindex="-1" aria-labelledby="trainingCourseAccessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <div class="modal-title" id="trainingCourseAccessModalLabel">Активация курса</div>
                    <div class="training-course-access-modal__text" data-access-modal-text>Подтвердите действие.</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Закрыть"></button>
            </div>

            <div class="modal-body">
                <div class="training-course-access-modal__summary">
                    <div class="training-course-access-modal__kv">
                        <span class="training-course-access-modal__k">Курс</span>
                        <span class="training-course-access-modal__v" data-access-modal-course>—</span>
                    </div>
                    <div class="training-course-access-modal__kv">
                        <span class="training-course-access-modal__k">Сотрудников</span>
                        <span class="training-course-access-modal__v" data-access-modal-count>0</span>
                    </div>
                </div>

                <div class="training-course-access-modal__license-info is-hidden" data-access-modal-license-info></div>

                <div class="training-course-access-modal__dates" data-access-modal-dates>
                    <div class="training-request-modal__row">
                        <label class="training-request-modal__field">
                            <span class="training-request-modal__label">Активен с</span>
                            <input type="datetime-local" class="training-request-modal__input" data-access-active-from>
                        </label>

                        <label class="training-request-modal__field">
                            <span class="training-request-modal__label">Активен по</span>
                            <input type="datetime-local" class="training-request-modal__input" data-access-active-to>
                        </label>
                    </div>
                    <div class="training-course-access-modal__hint">
                        По умолчанию доступ выдаётся на 3 месяца от даты активации.
                    </div>
                </div>

                <div class="training-course-access-modal__alert d-none" data-access-modal-alert></div>

                <div class="training-request-modal__actions training-course-access-modal__actions">
                    <button type="button" class="training-course-access-modal__cancel" data-bs-dismiss="modal">Отмена</button>
                    <button type="button" class="training-request-modal__submit training-course-access-modal__confirm" data-access-modal-confirm>Подтвердить</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-block training-manage-page"
    id="training-manage-page"
    data-training-page="managecourses"
    data-training-connector="/assets/components/training/web.connector.php"
    data-training-context="[[+context_key]]">
    <div class="d-flex align-items-center justify-content-between mb-4 gap-3">
        <div class="title-training">Управление курсами</div>

        <button type="button" class="training-manage-mobile-search-toggle" aria-label="Открыть поиск">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M21 21L16.65 16.65M19 11C19 15.4183 15.4183 19 11 19C6.58172 19 3 15.4183 3 11C3 6.58172 6.58172 3 11 3C15.4183 3 19 6.58172 19 11Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </button>

        <div class="courses-available">
            <div class="courses-available__count">
                Доступно лицензий: <strong>[[+available_count]]</strong>
            </div>
            <button type="button"
                    class="courses-available__btn"
                    data-bs-toggle="modal"
                    data-bs-target="#trainingCourseRequestModal">
                Запросить курс
            </button>
        </div>
    </div>

    <div class="d-flex align-items-start flex-column gap-4">
        <div class="course-filters-swiper swiper w-100">
            <div class="swiper-wrapper filters-corses">
                [[+courses_buttons]]
            </div>
        </div>

        <div class="search-block w-100">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <form class="search-training" id="users-search" action="#" onsubmit="return false;">
                    <label class="search-input">
                        <span class="search-input__ico" aria-hidden="true">
                            <img src="theme/images/training/all-course/search-ico.svg" class="img-svg" alt="">
                        </span>
                        <input type="text" class="search-input__field" placeholder="Поиск по сотрудникам" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" inputmode="search">
                    </label>
                </form>

                <div class="action-panel">
                    <button type="button" class="action-btn action-btn--danger" data-bulk-action="unassign"[[+controls_disabled]]>
                        <span class="action-btn__ico" aria-hidden="true">
                            <img src="theme/images/training/lock-keyhole.svg" class="img-svg" alt="">
                        </span>
                        <span class="action-btn__text">Заблокировать</span>
                    </button>

                    <button type="button" class="action-btn action-btn--success" data-bulk-action="assign"[[+controls_disabled]]>
                        <span class="action-btn__ico action-btn__ico--circle" aria-hidden="true">
                            <img src="theme/images/training/arrow-pub-ico.svg" class="img-svg" alt="">
                        </span>
                        <span class="action-btn__text">Активировать</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="users-table-wrap mt-4 d-none d-xxl-block">
        <div class="users-table" data-users-table></div>
    </div>

    <div class="label-block d-flex flex-column gap-3 p-3 mt-4 d-xxl-none" data-users-cards></div>

    [[+request_modal]]
    [[+course_access_modal]]
</div>

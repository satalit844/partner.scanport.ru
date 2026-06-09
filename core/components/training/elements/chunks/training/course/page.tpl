<div class="section-block course-page mb-3 mb-lg-4">
    <div class="course-hero mb-3 mb-xl-4">
        <div class="d-flex d-xxl-none align-items-start gap-2">
            <div class="course-hero__img">
                <img src="{$course_image}" alt="{$course_title}">
            </div>
            <div class="course-hero__chips d-flex d-xxl-none flex-column align-items-start gap-2">
                <div class="label-chip {$course_status_class} label-chip--sm"><span>{$course_status_text}</span></div>
                <span class="course-chip"><span class="course-chip__ico" aria-hidden="true"><img src="theme/images/training/curses/user-ico.svg" class="img-svg" alt=""></span><span class="course-chip__txt">{$course_user_label}</span></span>
                <span class="course-chip {$course_duration_class}"><span class="course-chip__ico" aria-hidden="true"><img src="theme/images/training/curses/clock-ico.svg" class="img-svg" alt=""></span><span class="course-chip__txt">Продолжительность {$course_duration_text}</span></span>
            </div>
        </div>
        <div class="course-hero__media d-none d-xxl-block"><div class="course-hero__img"><img src="{$course_image}" alt="{$course_title}"></div></div>
        <div class="course-hero__body d-flex flex-column align-items-start gap-4">
            <div class="course-hero__top"><div class="course-hero__title">{$course_title}</div></div>
            <div class="course-hero__chips d-none d-xxl-flex">
                <div class="label-chip {$course_status_class}"><span>{$course_status_text}</span></div>
                <span class="course-chip"><span class="course-chip__ico" aria-hidden="true"><img src="theme/images/training/curses/user-ico.svg" class="img-svg" alt=""></span><span class="course-chip__txt">{$course_user_label}</span></span>
                <span class="course-chip {$course_duration_class}"><span class="course-chip__ico" aria-hidden="true"><img src="theme/images/training/curses/clock-ico.svg" class="img-svg" alt=""></span><span class="course-chip__txt">Продолжительность {$course_duration_text}</span></span>
            </div>
            <div class="course-item__info">
                <div class="course-item__stats">
                    <div class="course-stat"><div class="course-stat__value">{$course_videos_text}</div><div class="course-stat__label">видео</div></div>
                    <div class="course-stat"><div class="course-stat__value">{$course_practices_text}</div><div class="course-stat__label">Практические работы</div></div>
                    <div class="course-stat"><div class="course-stat__value">{$course_tests_text}</div><div class="course-stat__label">Тесты</div></div>
                </div>
                <div class="course-item__progress"><div class="progress-track"><div class="progress-fill" style="width: {$course_progress_percent}%"></div></div><div class="progress-value">{$course_progress_percent}%</div></div>
            </div>
        </div>
    </div>
    <div class="course-about">
        <div class="course-about__title mb-3">О курсе</div>
        <div class="course-about__text mb-3" data-course-about>{$course_description}</div>
        <button type="button" class="course-about__toggle" data-course-about-toggle>Показать больше</button>
    </div>
</div>
<div class="section-block course-structure-block">
    <div class="title-training mb-3 mb-xl-4">Структура курса</div>
    <div class="course-structure">{$course_modules_html}</div>
</div>

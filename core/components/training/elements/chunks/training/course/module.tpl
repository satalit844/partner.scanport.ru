<div class="cs-module{$module_open_class}" data-cs-module data-module-state="{$module_state}">
    <button type="button" class="cs-head" data-cs-toggle aria-expanded="{$module_aria_expanded}">
        <span class="cs-head__ico" aria-hidden="true"><img src="theme/images/training/curses/shapka-ico.png" alt=""></span>
        <span class="cs-head__main">
            <span class="cs-head__title">{$module_title}</span>
            <span class="cs-head__meta"><span class="cs-head__count">{$module_lessons_count_text}<span class="d-flex d-xl-none {$module_duration_class}">&nbsp;•&nbsp;{$module_duration_text}</span></span><img src="theme/images/training/curses/arrow-down-ico.svg" class="img-svg cs-head__arrow d-none d-xl-block" alt=""></span>
        </span>
        <span class="cs-head__right d-none d-xl-flex"><span class="cs-head__time {$module_duration_class}"><img src="theme/images/training/curses/clock-ico.svg" class="img-svg" alt=""><span>{$module_duration_text}</span></span>{$module_status_html}</span>
        <span class="cs-head__right d-flex d-xl-none"><img src="theme/images/training/curses/arrow-down-ico.svg" class="img-svg cs-head__arrow" alt=""></span>
    </button>
    <div class="cs-body" data-cs-body>{$module_lessons_html}</div>
</div>
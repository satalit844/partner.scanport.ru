<div class="my-courses" id="my-courses"
     data-training-page="mycourses"
     data-training-connector="{$connector_url}"
     data-training-context="{$context_key}">
    <div class="filters-corses mb-4">
        <div class="swiper course-filters-swiper">
            <div class="swiper-wrapper filters-items">
                <div class="swiper-slide"><button type="button" class="course-filter__chip is-active" data-filter="all" role="tab" aria-selected="true">Все</button></div>
                <div class="swiper-slide"><button type="button" class="course-filter__chip" data-filter="progress" role="tab" aria-selected="false">В процессе</button></div>
                <div class="swiper-slide"><button type="button" class="course-filter__chip" data-filter="new" role="tab" aria-selected="false">Не начатые</button></div>
                <div class="swiper-slide"><button type="button" class="course-filter__chip" data-filter="done" role="tab" aria-selected="false">Завершенные</button></div>
            </div>
        </div>
    </div>
    <div class="corses-items d-flex flex-wrap align-items-center gap-3" data-server-rendered="1">{$course_items_html}</div>
</div>

<!-- training-player-mobile-ui:start -->
<link rel="stylesheet" href="/theme/css/training-player-mobile-ui.css?v=20260623145238">
<script src="/theme/js/training-player-mobile-ui.js?v=20260623145238"></script>
<!-- training-player-mobile-ui:end -->
<div class="training-player w-100"
     data-training-page="player"
     data-training-connector="{$connector_url}"
     data-training-context="{$context_key}"
     data-player-module="{$requested_module}"
     data-player-lesson="{$requested_lesson}"
     data-player-video="{$requested_video}">
    <div class="player-layout section-block">
        <div class="player-main">
            <div class="player-card js-player-fullscreen-target">
                <div class="player-card__header d-flex align-items-start justify-content-between gap-3">
                    <div class="d-flex align-items-start gap-3 min-w-0">
                        <button type="button" class="player-nav-icon js-player-back" aria-label="Назад к курсу">
                            <img src="theme/images/training/player/arrow-left.svg" class="img-svg" alt="">
                        </button>

                        <div class="player-title-wrap min-w-0">
                            <div class="player-title js-player-title">Загрузка урока…</div>
                            <div class="subtitle js-player-subtitle">Подготавливаем данные проигрывателя</div>
                        </div>
                    </div>

                    <button type="button" class="player-nav-icon js-player-sidebar-toggle" aria-label="Свернуть правый блок">
                        <img src="theme/images/training/player/arrow-right-from-line-ico.svg" class="img-svg" alt="">
                    </button>
                </div>

                <div class="player-stage">
                    <div class="player-slide-area">
                        <div class="player-slide-frame">
                            <img class="player-slide-image js-player-slide-image" src="theme/images/training/player/img-slide-1.jpg" alt="">
                            <div class="player-floating-video"></div>
                        </div>

                        <div class="player-controls">
                            <div class="player-controls__top">
                                <div class="player-control-block d-flex align-items-center justify-content-end gap-3">
                                    <div class="player-bottom-nav__counter js-player-counter">0 из 0</div>
    
                                    <div class="player-controls__nav">
                                        <button type="button" class="btn btn-player-nav js-player-prev" disabled>
                                            <img src="theme/images/training/player/arrow-left.svg" class="img-svg" alt="">
                                            <span>Назад</span>
                                        </button>
    
                                        <button type="button" class="btn btn-player-next js-player-next" disabled>
                                            <span>Далее</span>
                                            <img src="theme/images/training/player/arrow-left.svg" class="img-svg" alt="">
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="player-controls__progress">
                                <div class="player-progress js-player-progress">
                                    <div class="player-progress__line">
                                        <div class="player-progress__fill js-player-progress-fill" style="width:0%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="player-controls__bottom">
                                <div class="player-controls__left d-flex align-items-center gap-2">
                                    <button type="button" class="player-control-btn js-player-play" aria-label="Воспроизвести">
                                        <img src="theme/images/training/player/play-ico.svg" class="img-svg" alt="">
                                    </button>

                                    <button type="button" class="player-control-btn js-player-restart" aria-label="Начать сначала">
                                        <img src="theme/images/training/player/reload-ico.svg" class="img-svg" alt="">
                                    </button>

                                    <div class="player-volume-wrap">
                                        <button type="button" class="player-control-btn js-player-sound" aria-label="Громкость">
                                            <img src="theme/images/training/player/volume-ico.svg" class="img-svg" alt="">
                                        </button>
                                        <input type="range" class="player-volume-range js-player-volume" min="0" max="100" step="1" value="70" aria-label="Громкость">
                                    </div>
                                </div>

                                <div class="player-controls__right d-flex align-items-center gap-2">
                                    <div class="player-timebox">
                                        <span class="js-player-time-current">0:00</span>
                                        <span>/</span>
                                        <span class="js-player-time-total">0:00</span>
                                    </div>

                                    <div class="player-quality js-player-quality">
                                        <button type="button" class="player-control-btn player-quality-trigger js-player-quality-toggle" aria-label="Качество видео">
                                            <span class="player-quality-trigger__left">
                                                <img src="theme/images/training/player/settings-ico.svg" class="img-svg" alt="">
                                                <span class="player-quality-trigger__label">Качество</span>
                                            </span>
                                            <span class="player-quality-trigger__value">—</span>
                                            <span class="player-quality-trigger__arrow"></span>
                                        </button>
                                        <div class="player-quality-menu js-player-quality-menu"></div>
                                    </div>

                                    <button type="button" class="player-control-btn js-player-fullscreen" aria-label="На весь экран">
                                        <img src="theme/images/training/player/full-screen-ico.svg" class="img-svg" alt="">
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-3 text-info-test js-player-message d-none"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="player-sidebar">
            <div class="player-sidebar__video">
                <video class="player-preview-video js-player-video" muted playsinline preload="metadata" controlslist="nodownload noplaybackrate"></video>
            </div>

            <div class="player-sidebar__section">
                <div class="player-sidebar__head mt-2">
                    <div class="player-sidebar__head-view">
                        <div class="player-sidebar__title">Слайды</div>
                        <button type="button" class="player-search-btn js-player-search-toggle" aria-label="Поиск по материалам">
                            <img src="theme/images/training/player/search-ico.svg" class="img-svg" alt="">
                        </button>
                    </div>
                    <div class="player-sidebar__searchbar">
                        <input type="text" class="player-sidebar__search-inline js-player-sidebar-search" placeholder="Поиск по материалам">
                        <button type="button" class="player-search-close js-player-search-close" aria-label="Закрыть поиск">×</button>
                    </div>
                </div>
                <div class="player-sidebar__list js-player-slide-list" data-list-type="slides"></div>
                <div class="player-sidebar__empty js-player-empty d-none" data-for="slides">Ничего не найдено</div>
            </div>
        </div>
    </div>

    <div class="player-resume-modal js-player-resume-modal">
        <div class="player-resume-modal__overlay"></div>
        <div class="player-resume-modal__dialog">
            <button type="button" class="player-resume-modal__close js-player-resume-close" aria-label="Закрыть">×</button>
            <div class="player-resume-modal__title">Возобновить видео</div>
            <div class="player-resume-modal__text js-player-resume-text">Продолжить курс со слайда, на котором вы остановились?</div>
            <div class="player-resume-modal__actions">
                <button type="button" class="btn btn-player-nav js-player-start-over">Сначала</button>
                <button type="button" class="btn btn-player-next js-player-resume-continue">Продолжить</button>
            </div>
        </div>
    </div>
</div>

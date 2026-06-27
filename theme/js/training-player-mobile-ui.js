(function (window, document) {
    'use strict';

    var STORAGE_KEY = 'training.player.mobile.ui.v1';

    function isMobile() {
        return window.matchMedia && window.matchMedia('(max-width: 991.98px)').matches;
    }

    function safeParse(value) {
        try {
            var parsed = JSON.parse(value || '');
            return parsed && typeof parsed === 'object' ? parsed : {};
        } catch (e) {
            return {};
        }
    }

    function loadSettings() {
        var data = safeParse(window.localStorage.getItem(STORAGE_KEY));
        return {
            position: ['top-left', 'top-right', 'bottom-left', 'bottom-right'].indexOf(data.position) !== -1
                ? data.position
                : 'bottom-left',
            size: ['small', 'medium', 'large'].indexOf(data.size) !== -1
                ? data.size
                : 'medium'
        };
    }

    function saveSettings(settings) {
        try {
            window.localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
        } catch (e) {}
    }

    function makeButton(className, label, ariaLabel) {
        var button = document.createElement('button');
        button.type = 'button';
        button.className = className;
        button.textContent = label;
        button.setAttribute('aria-label', ariaLabel || label);
        return button;
    }

    function makeMenu() {
        var menu = document.createElement('div');
        menu.className = 'player-mobile-video-menu';
        menu.setAttribute('role', 'dialog');
        menu.setAttribute('aria-label', 'Настройки видео спикера');

        menu.innerHTML =
            '<div class="player-mobile-video-menu__title">Расположение видео</div>' +
            '<div class="player-mobile-video-menu__grid">' +
                '<button type="button" data-player-video-position="top-left">↖ Слева сверху</button>' +
                '<button type="button" data-player-video-position="top-right">↗ Справа сверху</button>' +
                '<button type="button" data-player-video-position="bottom-left">↙ Слева снизу</button>' +
                '<button type="button" data-player-video-position="bottom-right">↘ Справа снизу</button>' +
            '</div>' +
            '<div class="player-mobile-video-menu__title">Размер видео</div>' +
            '<div class="player-mobile-video-menu__grid">' +
                '<button type="button" data-player-video-size="small">Маленькое</button>' +
                '<button type="button" data-player-video-size="medium">Среднее</button>' +
                '<button type="button" data-player-video-size="large">Большое</button>' +
            '</div>';

        return menu;
    }

    function init(root) {
        if (!root || root.dataset.mobileUiReady === '1') {
            return;
        }

        var frame = root.querySelector('.player-slide-frame');
        var controlsRight = root.querySelector('.player-controls__right');
        var playerCard = root.querySelector('.player-card');

        if (!frame || !controlsRight || !playerCard) {
            return;
        }

        root.dataset.mobileUiReady = '1';

        var settings = loadSettings();
        var hideButton = makeButton('player-mobile-ui-toggle', 'Скрыть', 'Скрыть элементы управления');
        var videoButton = makeButton('player-mobile-video-settings', 'Настроить', 'Настроить положение и размер видео спикера');
        var menu = makeMenu();

        var actionGroup = document.createElement('div');
        actionGroup.className = 'player-mobile-action-group';
        actionGroup.appendChild(hideButton);
        actionGroup.appendChild(videoButton);

        controlsRight.appendChild(actionGroup);
        playerCard.appendChild(menu);

        function applySettings() {
            root.setAttribute('data-mobile-video-position', settings.position);
            root.setAttribute('data-mobile-video-size', settings.size);

            Array.prototype.forEach.call(menu.querySelectorAll('[data-player-video-position]'), function (button) {
                button.classList.toggle('is-active', button.getAttribute('data-player-video-position') === settings.position);
            });

            Array.prototype.forEach.call(menu.querySelectorAll('[data-player-video-size]'), function (button) {
                button.classList.toggle('is-active', button.getAttribute('data-player-video-size') === settings.size);
            });
        }

        function hideUi() {
            root.classList.add('is-mobile-ui-hidden');
            root.classList.remove('is-controls-visible');
            root.classList.remove('is-mobile-video-menu-open');
            hideButton.textContent = 'Показать';
            hideButton.setAttribute('aria-label', 'Показать элементы управления');
        }

        function showUi() {
            root.classList.remove('is-mobile-ui-hidden');
            root.classList.add('is-controls-visible');
            hideButton.textContent = 'Скрыть';
            hideButton.setAttribute('aria-label', 'Скрыть элементы управления');
        }

        function isUiVisible() {
            return !root.classList.contains('is-mobile-ui-hidden')
                && root.classList.contains('is-controls-visible');
        }

        function isControlTarget(target) {
            return !!(target && target.closest && target.closest(
                '.player-controls, .player-floating-video, .player-mobile-video-menu, .player-mobile-ui-toggle, .player-mobile-video-settings'
            ));
        }

        hideButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();

            if (root.classList.contains('is-mobile-ui-hidden')) {
                showUi();
            } else {
                hideUi();
            }
        });

        videoButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            showUi();
            root.classList.toggle('is-mobile-video-menu-open');
        });

        menu.addEventListener('click', function (event) {
            var position = event.target.getAttribute('data-player-video-position');
            var size = event.target.getAttribute('data-player-video-size');

            if (position) {
                settings.position = position;
                saveSettings(settings);
                applySettings();
                return;
            }

            if (size) {
                settings.size = size;
                saveSettings(settings);
                applySettings();
            }
        });

        frame.addEventListener('click', function (event) {
            if (!isMobile() || isControlTarget(event.target)) {
                return;
            }

            /*
             * app-training can also update is-controls-visible on this click.
             * Waiting one task lets this manual action win deterministically.
             */
            window.setTimeout(function () {
                if (isUiVisible()) {
                    hideUi();
                } else {
                    showUi();
                }
            }, 0);
        });

        document.addEventListener('click', function (event) {
            if (!root.classList.contains('is-mobile-video-menu-open')) {
                return;
            }

            if (!event.target.closest('.player-mobile-video-menu')
                && !event.target.closest('.player-mobile-video-settings')) {
                root.classList.remove('is-mobile-video-menu-open');
            }
        });

        Array.prototype.forEach.call(root.querySelectorAll('.js-player-video'), function (video) {
            video.addEventListener('pause', function () {
                if (video.ended || !video.seeking) {
                    showUi();
                }
            });

            video.addEventListener('ended', function () {
                showUi();
            });
        });

        applySettings();
    }

    function boot() {
        Array.prototype.forEach.call(
            document.querySelectorAll('.training-player[data-training-page="player"]'),
            init
        );
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})(window, document);

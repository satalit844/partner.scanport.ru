var AppTraining = (function () {
    return {
        init: function () {
            this.filtersSwipers();
            this.filtersLogic();
            this.sertModal();
            this.historyModal();
            this.usersCoursesUi();
            this.bindMyCourseLinks();
            this.webMyCourses();
            this.coursePage();
            this.courseStructure();
            this.testListModal();
            this.trainingRequestCourseModal();
            this.trainingPlayer();
        },

        bindMyCourseLinks: function () {
            $(document)
                .off('click.appTrainingMyCourseLink')
                .on('click.appTrainingMyCourseLink', '#my-courses .course-item[data-href], .my-courses .course-item[data-href]', function (e) {
                    if ($(e.target).closest('a, button, input, label').length) {
                        return;
                    }

                    var href = $(this).attr('data-href');
                    if (href && href !== '#') {
                        window.location.href = href;
                    }
                })
                .off('keydown.appTrainingMyCourseLink')
                .on('keydown.appTrainingMyCourseLink', '#my-courses .course-item[data-href], .my-courses .course-item[data-href]', function (e) {
                    if (e.key !== 'Enter' && e.key !== ' ') {
                        return;
                    }

                    e.preventDefault();

                    var href = $(this).attr('data-href');
                    if (href && href !== '#') {
                        window.location.href = href;
                    }
                });
        },

        getTrainingConnectorUrl: function ($scope) {
            if ($scope && $scope.length) {
                var local = $scope.data('training-connector');
                if (local) return local;
            }

            if (window.TrainingWebConfig && window.TrainingWebConfig.connectorUrl) {
                return window.TrainingWebConfig.connectorUrl;
            }

            return '/assets/components/training/web.connector.php';
        },

        getTrainingContext: function ($scope) {
            if ($scope && $scope.length) {
                var local = $scope.data('training-context');
                if (local) return local;
            }

            if (window.TrainingWebConfig && window.TrainingWebConfig.contextKey) {
                return window.TrainingWebConfig.contextKey;
            }

            return 'web';
        },

        trainingRequest: function (action, data, $scope) {
            return $.ajax({
                url: this.getTrainingConnectorUrl($scope),
                type: 'POST',
                dataType: 'json',
                data: $.extend({
                    action: action,
                    ctx: this.getTrainingContext($scope)
                }, data || {})
            });
        },

        escapeHtml: function (value) {
            return $('<div>').text(value == null ? '' : String(value)).html();
        },

        webMyCourses: function () {
            var self = this;
            var $wrap = $('#my-courses[data-training-page="mycourses"], .my-courses[data-training-page="mycourses"]').first();
            if (!$wrap.length) return;

            var $list = $wrap.find('.corses-items').first();
            if (!$list.length) return;

            if ($list.attr('data-server-rendered') === '1' && $.trim($list.html()) !== '') {
                var $activeFilter = $wrap.find('.filters-corses .course-filter__chip.is-active').first();
                if ($activeFilter.length) {
                    $activeFilter.trigger('click');
                }
                return;
            }

            function renderStatsItem(value, label) {
                return '' +
                    '<div class="course-stat">' +
                        '<div class="course-stat__value">' + value + '</div>' +
                        '<div class="course-stat__label">' + label + '</div>' +
                    '</div>';
            }

            function renderStatusBlock(item, progress) {
                return '' +
                    '<div class="course-item__status">' +
                        '<span class="course-status__dot" aria-hidden="true"></span>' +
                        '<span>' + self.escapeHtml(item.status_text || '') + '</span>' +
                    '</div>' +
                    '<div class="course-item__progress">' +
                        '<div class="progress-track"><div class="progress-fill" style="width: ' + progress + '%"></div></div>' +
                        '<div class="progress-value">' + progress + '%</div>' +
                    '</div>';
            }

            function renderProgressBlock(item, progress) {
                return '' +
                    '<div class="course-item__stats">' +
                        renderStatsItem((item.videos_text || ((item.videos_completed || 0) + '/' + (item.videos_total || 0))), 'видео') +
                        renderStatsItem((item.practices_text || ((item.practices_completed || 0) + '/' + (item.practices_total || 0))), 'Практические работы') +
                        renderStatsItem((item.tests_text || ((item.tests_passed || 0) + '/' + (item.tests_total || 0))), 'Тесты') +
                    '</div>' +
                    '<div class="course-item__progress">' +
                        '<div class="progress-track"><div class="progress-fill" style="width: ' + progress + '%"></div></div>' +
                        '<div class="progress-value">' + progress + '%</div>' +
                    '</div>';
            }

            function renderItem(item) {
                var title = self.escapeHtml(item.title || item.pagetitle || 'Без названия');
                var image = self.escapeHtml(item.image || 'theme/images/training/image_2.jpg');
                var url = self.escapeHtml(item.url || '#');
                var progress = parseInt(item.progress_percent || 0, 10);
                var bodyHtml = '';

                if (item.state === 'progress') {
                    bodyHtml = renderProgressBlock(item, progress);
                } else {
                    bodyHtml = renderStatusBlock(item, progress);
                }

                return '' +
                    '<div class="course-item ' + self.escapeHtml(item.item_class || 'is-new') + '" data-href="' + url + '" tabindex="0" role="link">' +
                        '<div class="course-item__head">' +
                            '<div class="course-item__title">' + title + '</div>' +
                            '<div class="course-item__logo"><img src="' + image + '" alt=""></div>' +
                        '</div>' +
                        bodyHtml +
                    '</div>';
            }

            function applyCurrentFilter() {
                var $activeFilter = $wrap.find('.filters-corses .course-filter__chip.is-active').first();
                if ($activeFilter.length) {
                    $activeFilter.trigger('click');
                }
            }

            function render(rows) {
                if (!rows || !rows.length) {
                    $list.html('<div class="w-100">У вас пока нет назначенных курсов</div>');
                    return;
                }

                var html = '';
                $.each(rows, function (_, item) {
                    html += renderItem(item);
                });

                $list.html(html);
                applyCurrentFilter();
            }

            self.trainingRequest('web/course/mycourses', {}, $wrap)
                .done(function (res) {
                    if (!res || !res.success || !res.object) {
                        $list.html('<div class="w-100">Не удалось загрузить курсы</div>');
                        return;
                    }

                    render(res.object.results || []);
                })
                .fail(function () {
                    $list.html('<div class="w-100">Ошибка загрузки курсов</div>');
                });
        },

        trainingPlayer: function () {
            var self = this;
            var $player = $('.training-player[data-training-page="player"]').first();
            if (!$player.length) return;

            var player = $player.find('.js-player-video').get(0);
            if (!player) return;

            var requestedModule = parseInt($player.attr('data-player-module') || 0, 10) || 0;
            var requestedLesson = parseInt($player.attr('data-player-lesson') || 0, 10) || 0;
            var requestedVideo = parseInt($player.attr('data-player-video') || 0, 10) || 0;
            var currentData = null;
            var currentVideoId = requestedVideo;
            var activeTimelineSlides = [];
            var saveInterval = null;
            var lastSavedTime = -1;
            var loadXhr = null;
            var saveXhr = null;
            var completeSent = false;
            var pendingResumeTime = 0;
            var pendingSeekTime = null;
            var pendingAutoplay = false;
            var shouldPromptResume = false;
            var activeQualityPath = '';
            var activeSlideId = 0;
            var lastEnsuredActiveSlideId = 0;
            var fullscreenTarget = $player.find('.js-player-fullscreen-target').get(0) || $player.find('.player-card').get(0) || $player.get(0);

            var $title = $player.find('.js-player-title');
            var $subtitle = $player.find('.js-player-subtitle');
            var $counter = $player.find('.js-player-counter');
            var $progressFill = $player.find('.js-player-progress-fill');
            var $progressBar = $player.find('.js-player-progress');
            var $progressTimeCurrent = $player.find('.js-player-time-current');
            var $progressTimeTotal = $player.find('.js-player-time-total');
            var $message = $player.find('.js-player-message');
            var $slideImage = $player.find('.js-player-slide-image');
            var $sidebarVideoHolder = $player.find('.player-sidebar__video').first();
            var $floatingPreviewHolder = $player.find('.player-floating-video').first();
            var $slideList = $player.find('.js-player-slide-list');
            var $prevBtn = $player.find('.js-player-prev');
            var $nextBtn = $player.find('.js-player-next');
            var $backBtn = $player.find('.js-player-back');
            var $soundBtn = $player.find('.js-player-sound');
            var $volume = $player.find('.js-player-volume');
            var $sidebarSearch = $player.find('.js-player-sidebar-search');
            var $searchToggle = $player.find('.js-player-search-toggle');
            var $searchClose = $player.find('.js-player-search-close');
            var $resumeModal = $player.find('.js-player-resume-modal');
            var $resumeText = $player.find('.js-player-resume-text');
            var $resumeClose = $player.find('.js-player-resume-close');
            var $qualityToggle = $player.find('.js-player-quality-toggle');
            var $qualityMenu = $player.find('.js-player-quality-menu');

            function ensureMobilePlayerVisuals() {
                var $area = $player.find('.player-slide-area').first();
                var $frame = $area.find('.player-slide-frame').first();

                if ($area.length && $frame.length && !$area.find('.player-mobile-caption').length) {
                    $('<div class="player-mobile-caption" aria-hidden="false"><div class="player-title--mobile"></div></div>').insertAfter($frame);
                }

                var $sidebar = $player.find('.player-sidebar').first();
                var $videoBox = $sidebar.find('.player-sidebar__video').first();

                if ($sidebar.length && $videoBox.length && !$sidebar.find('.player-mobile-video-toggle').length) {
                    $('<button type="button" class="player-mobile-video-toggle" aria-expanded="true"><span>Видео инструктора</span><span class="player-mobile-toggle__chevron" aria-hidden="true"></span></button>').insertBefore($videoBox);
                }
            }

            function syncMobilePlayerCaption() {
                var title = $.trim($player.find('.player-card__header .js-player-title').first().text() || $title.first().text() || '');
                $player.find('.player-title--mobile').text(title);
            }

            ensureMobilePlayerVisuals();
            player.defaultMuted = false;
            player.muted = false;
            player.volume = 1;
            $volume.val(100);
            var $fullscreenBtn = $player.find('.js-player-fullscreen');
            var $playerCard = $player.find('.player-card');
            var pendingSwitchSlideId = 0;
            var pendingSwitchTimecode = 0;
            var pendingSwitchImage = '';
            var $busyLayer = $player.find('.js-player-busy');
            if (!$busyLayer.length) {
                $busyLayer = $('<div class="player-busy js-player-busy d-none"><div class="player-busy__spinner"></div><div class="player-busy__text">Загрузка…</div></div>');
                $player.find('.player-slide-frame').append($busyLayer);
            }
            var busyTimer = null;

            function formatTime(value) {
                value = parseInt(value || 0, 10);
                if (!value || value < 0) value = 0;
                var h = Math.floor(value / 3600);
                var m = Math.floor((value % 3600) / 60);
                var s = value % 60;
                if (h > 0) {
                    return h + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                }
                return m + ':' + String(s).padStart(2, '0');
            }

            function parsePlayerUrl(url) {
                if (!url) return null;
                try {
                    var parsed = new URL(url, window.location.origin);
                    return {
                        module: parseInt(parsed.searchParams.get('module') || 0, 10) || 0,
                        lesson: parseInt(parsed.searchParams.get('lesson') || 0, 10) || 0,
                        video: parseInt(parsed.searchParams.get('video') || 0, 10) || 0
                    };
                } catch (e) {
                    return null;
                }
            }

            function setMessage(text, isError) {
                if (!text) {
                    $message.addClass('d-none').text('').removeClass('text-danger');
                    return;
                }
                $message.removeClass('d-none').text(text);
                $message.toggleClass('text-danger', !!isError);
            }

            function setBusy(state, text, immediate) {
                if (!$busyLayer.length) {
                    return;
                }
                if (busyTimer) {
                    clearTimeout(busyTimer);
                    busyTimer = null;
                }
                if (text) {
                    $busyLayer.find('.player-busy__text').text(text);
                }
                if (!state) {
                    $busyLayer.removeClass('is-visible').addClass('d-none');
                    return;
                }
                var showLayer = function () {
                    $busyLayer.removeClass('d-none').addClass('is-visible');
                };
                if (immediate) {
                    showLayer();
                } else {
                    busyTimer = setTimeout(showLayer, 260);
                }
            }

            function getVideoItems() {
                return currentData && currentData.video_items ? currentData.video_items : [];
            }

            function getVideoStateById(videoId) {
                videoId = parseInt(videoId || 0, 10) || 0;
                var items = getVideoItems();
                for (var i = 0; i < items.length; i++) {
                    if ((parseInt(items[i].id || 0, 10) || 0) === videoId) {
                        return items[i];
                    }
                }
                return null;
            }

            function getAdjacentVideoId(direction) {
                direction = direction === 'prev' ? 'prev' : 'next';
                var items = getVideoItems();
                for (var i = 0; i < items.length; i++) {
                    if ((parseInt(items[i].id || 0, 10) || 0) !== currentVideoId) {
                        continue;
                    }
                    if (direction === 'prev' && i > 0) {
                        return parseInt(items[i - 1].id || 0, 10) || 0;
                    }
                    if (direction === 'next' && i < items.length - 1) {
                        return parseInt(items[i + 1].id || 0, 10) || 0;
                    }
                    break;
                }
                return 0;
            }

            function getVideoQualitiesById(videoId) {
                videoId = String(parseInt(videoId || 0, 10) || 0);
                if (!currentData || !currentData.video_qualities_map || !currentData.video_qualities_map[videoId]) {
                    return [];
                }
                return currentData.video_qualities_map[videoId] || [];
            }

            function getTimelineSlidesByVideo(videoId) {
                videoId = String(parseInt(videoId || 0, 10) || 0);
                if (!currentData || !currentData.timeline_slides_map || !currentData.timeline_slides_map[videoId]) {
                    return [];
                }
                return currentData.timeline_slides_map[videoId] || [];
            }

            function videoHasOwnProgress(videoState) {
                if (!videoState) return false;
                return parseInt(videoState.completed || 0, 10) === 1
                    || parseInt(videoState.current_time || 0, 10) > 0
                    || parseInt(videoState.max_time || 0, 10) > 0
                    || parseInt(videoState.progress_percent || 0, 10) > 0;
            }

            function recalculateVideoLocks() {
                var items = getVideoItems();
                var previousCompleted = true;
                $.each(items, function (index, item) {
                    var completed = parseInt(item.completed || 0, 10) === 1;
                    var ownProgress = videoHasOwnProgress(item);
                    var locked = index === 0 ? false : !(previousCompleted || ownProgress);
                    item.locked = locked ? 1 : 0;
                    item.is_current = (parseInt(item.id || 0, 10) === currentVideoId) ? 1 : 0;
                    item.url = item.locked ? '' : (item.url || new URL(window.location.href, window.location.origin).pathname + '?module=' + requestedModule + '&lesson=' + requestedLesson + '&video=' + parseInt(item.id || 0, 10));
                    previousCompleted = completed;
                });
                if (currentData && currentData.slides_all) {
                    $.each(currentData.slides_all, function (_, slide) {
                        var itemState = getVideoStateById(slide.lesson_video_id || 0);
                        slide.locked = itemState && parseInt(itemState.locked || 0, 10) === 1 ? 1 : 0;
                        slide.url = slide.locked ? '' : (new URL(window.location.href, window.location.origin).pathname + '?module=' + requestedModule + '&lesson=' + requestedLesson + '&video=' + parseInt(slide.lesson_video_id || 0, 10));
                    });
                }
            }

            function syncCurrentVideoStateFromItems() {
                var state = getVideoStateById(currentVideoId);
                if (!state) {
                    return null;
                }
                currentData.current_video = $.extend(true, {}, state);
                currentData.videos = getVideoQualitiesById(currentVideoId);
                activeTimelineSlides = getTimelineSlidesByVideo(currentVideoId);
                return state;
            }

            function stopSaveInterval() {
                if (saveInterval) {
                    clearInterval(saveInterval);
                    saveInterval = null;
                }
            }

            function startSaveInterval() {
                stopSaveInterval();
            }

            function updateUrl(moduleResourceId, lessonId, videoId) {
                if (!window.history || !window.history.replaceState) return;
                var url = new URL(window.location.href);
                url.searchParams.set('module', moduleResourceId);
                url.searchParams.set('lesson', lessonId);
                if ((parseInt(videoId || 0, 10) || 0) > 0) {
                    url.searchParams.set('video', parseInt(videoId || 0, 10));
                } else {
                    url.searchParams.delete('video');
                }
                window.history.replaceState({}, '', url.toString());
            }

            function updateVolumeUi() {
                var volume = player.muted ? 0 : player.volume;
                $volume.val(Math.round(volume * 100));
                $soundBtn.toggleClass('is-muted', player.muted || volume <= 0.01);
            }

            function updatePlayUi() {
                var isPlaying = !player.paused && !player.ended;
                var $btn = $player.find('.js-player-play');
                $btn.toggleClass('is-playing', isPlaying);
                $btn.attr('aria-label', isPlaying ? 'Пауза' : 'Воспроизвести');
            }

            function getQualityLabel(item) {
                if (!item) return 'Видео';
                if (item.quality && String(item.quality).trim() !== '') {
                    return String(item.quality);
                }
                if (parseInt(item.height || 0, 10) > 0) {
                    return parseInt(item.height || 0, 10) + 'p';
                }
                return 'Видео';
            }

            function isFullscreenActive() {
                return !!(document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement);
            }

            function movePlayerVideo() {
                if (!player) {
                    return;
                }

                var $targetHolder = isFullscreenActive() ? $floatingPreviewHolder : $sidebarVideoHolder;
                if (!$targetHolder.length) {
                    return;
                }

                if (!$player.parent().is($targetHolder)) {
                    var wasPlaying = !player.paused && !player.ended;
                    $targetHolder.children().not(player).remove();
                    $(player).detach().appendTo($targetHolder);
                    if (wasPlaying) {
                        var playPromise = player.play();
                        if (playPromise && typeof playPromise.catch === 'function') {
                            playPromise.catch(function () {});
                        }
                    }
                }
            }

            function findSlideStateById(slideId) {
                slideId = parseInt(slideId || 0, 10) || 0;
                if (!slideId || !currentData || !currentData.slides_all) {
                    return null;
                }
                for (var i = 0; i < currentData.slides_all.length; i++) {
                    if ((parseInt(currentData.slides_all[i].id || 0, 10) || 0) === slideId) {
                        return currentData.slides_all[i];
                    }
                }
                return null;
            }

            function syncRenderedSlideItems() {
                $slideList.find('.js-player-slide-jump').each(function () {
                    var $item = $(this);
                    var slideId = parseInt($item.attr('data-slide-id') || 0, 10) || 0;
                    var slideState = findSlideStateById(slideId);
                    if (!slideState) {
                        return;
                    }
                    var locked = parseInt(slideState.locked || 0, 10) === 1;
                    $item.attr('data-url', slideState.url || '');
                    $item.attr('data-locked', locked ? '1' : '0');
                    $item.toggleClass('is-locked', locked);
                });
                refreshActiveSlideList(false);
            }

            function ensureActiveSlideVisible(force) {
                var $active = $slideList.find('.js-player-slide-jump.is-active:visible').first();
                if (!$active.length) {
                    return;
                }

                var activeId = parseInt($active.attr('data-slide-id') || 0, 10) || 0;
                if (!force && activeId > 0 && activeId === lastEnsuredActiveSlideId) {
                    return;
                }
                if (activeId > 0) {
                    lastEnsuredActiveSlideId = activeId;
                }

                var listNode = $slideList.get(0);
                var itemNode = $active.get(0);
                if (!listNode || !itemNode) {
                    return;
                }

                var listRect = listNode.getBoundingClientRect();
                var itemRect = itemNode.getBoundingClientRect();
                var outside = itemRect.top < listRect.top || itemRect.bottom > listRect.bottom;
                if (force || outside) {
                    if (typeof itemNode.scrollIntoView === 'function') {
                        itemNode.scrollIntoView({block: 'nearest', inline: 'nearest'});
                    }
                }
            }

            function refreshActiveSlideList(forceScroll) {
                $slideList.find('.js-player-slide-jump').each(function () {
                    var $item = $(this);
                    var itemSlideId = parseInt($item.attr('data-slide-id') || 0, 10) || 0;
                    var itemVideoId = parseInt($item.attr('data-video') || 0, 10) || 0;
                    var isCurrentVideo = itemVideoId > 0 && itemVideoId === currentVideoId;
                    var isActive = itemSlideId > 0 && itemSlideId === activeSlideId;

                    if (!isActive && activeSlideId <= 0 && isCurrentVideo) {
                        isActive = true;
                    }

                    var isLocked = parseInt($item.attr('data-locked') || 0, 10) === 1;
                    $item.toggleClass('is-active', isActive);
                    $item.toggleClass('is-current-video', isCurrentVideo);
                    $item.toggleClass('is-locked', isLocked);
                });

                ensureActiveSlideVisible(!!forceScroll);
            }

            function setMainSlide(image, slideId) {
                if (image) {
                    $slideImage.attr('src', image).attr('data-slide-id', slideId || 0);
                }
                activeSlideId = slideId || 0;
                refreshActiveSlideList();
            }

            function updateActiveSlideByTime() {
                if (!currentData) {
                    return;
                }
                if (!activeTimelineSlides || !activeTimelineSlides.length) {
                    var poster = currentData.lesson && currentData.lesson.poster ? currentData.lesson.poster : '';
                    if (poster) {
                        setMainSlide(poster, 0);
                    }
                    return;
                }

                var currentMs = Math.floor((player.currentTime || 0) * 1000);
                var activeSlide = null;
                $.each(activeTimelineSlides, function (_, slide) {
                    if ((slide.timecode_ms || 0) <= currentMs) {
                        activeSlide = slide;
                    }
                });
                if (!activeSlide) {
                    activeSlide = activeTimelineSlides[0];
                }
                if (!activeSlide) {
                    return;
                }
                if (parseInt(activeSlide.id || 0, 10) !== activeSlideId) {
                    setMainSlide(activeSlide.image || '', parseInt(activeSlide.id || 0, 10));
                }
            }

            function updateVideoProgress() {
                var duration = player.duration || (currentData && currentData.current_video ? parseInt(currentData.current_video.duration_seconds || 0, 10) : 0);
                if (!duration || !isFinite(duration) || duration <= 0) {
                    $progressFill.css('width', '0%');
                    $progressTimeCurrent.text('0:00');
                    $progressTimeTotal.text('0:00');
                    updateActiveSlideByTime();
                    return;
                }
                var pct = Math.max(0, Math.min(100, (player.currentTime / duration) * 100));
                $progressFill.css('width', pct + '%');
                $progressTimeCurrent.text(formatTime(Math.floor(player.currentTime || 0)));
                $progressTimeTotal.text(formatTime(Math.floor(duration)));
                updateActiveSlideByTime();
                maybeCompleteAutomatically();
            }

            function maybeCompleteAutomatically() {
                if (!currentData || !currentData.current_video || !player.duration || !isFinite(player.duration) || completeSent) {
                    return;
                }
                var ratio = player.currentTime / player.duration;
                if (ratio >= 0.9) {
                    completeSent = true;
                    saveProgress(true);
                }
            }

            function applyNav(data) {
                var items = getVideoItems();
                var prevItem = null;
                var nextItem = null;
                $.each(items, function (index, item) {
                    if ((parseInt(item.id || 0, 10) || 0) !== currentVideoId) {
                        return;
                    }
                    if (index > 0) {
                        prevItem = items[index - 1];
                    }
                    if (index < items.length - 1) {
                        nextItem = items[index + 1];
                    }
                    return false;
                });

                if (prevItem) {
                    $prevBtn.prop('disabled', false).attr('data-url', prevItem.url || '').attr('data-video', parseInt(prevItem.id || 0, 10));
                } else {
                    $prevBtn.prop('disabled', true).attr('data-url', '').attr('data-video', '');
                }

                if (nextItem) {
                    var nextLocked = parseInt(nextItem.locked || 0, 10) === 1;
                    $nextBtn.prop('disabled', nextLocked).attr('data-url', nextLocked ? '' : (nextItem.url || '')).attr('data-video', parseInt(nextItem.id || 0, 10)).attr('data-locked', nextLocked ? '1' : '0');
                } else {
                    $nextBtn.prop('disabled', true).attr('data-url', '').attr('data-video', '').attr('data-locked', '0');
                }
            }

            function applySearch() {
                var query = $.trim($sidebarSearch.val() || '').toLowerCase();
                var visible = 0;
                $slideList.find('.player-slide-item').each(function () {
                    var text = $.trim($(this).text() || '').toLowerCase();
                    var show = !query || text.indexOf(query) !== -1;
                    $(this).toggle(show);
                    if (show) visible++;
                });
                var $empty = $slideList.parent().find('.js-player-empty[data-for="' + $slideList.attr('data-list-type') + '"]');
                if ($empty.length) {
                    $empty.toggle(visible === 0);
                }
            }

            function openSearch() {
                $player.addClass('is-search-open');
                setTimeout(function () {
                    $sidebarSearch.trigger('focus');
                }, 30);
            }

            function closeSearch() {
                $player.removeClass('is-search-open');
                $sidebarSearch.val('');
                applySearch();
            }

            function showResumeModal(seconds, completedVideo) {
                pendingResumeTime = parseInt(seconds || 0, 10) || 0;
                completedVideo = !!completedVideo;
                if (pendingResumeTime <= 5 && !completedVideo) {
                    return false;
                }
                if (completedVideo) {
                    $resumeText.text('Это видео уже было просмотрено. Начать сначала или продолжить с того места, где вы остановились?');
                } else {
                    $resumeText.text('Продолжить просмотр видео с того места, на котором вы остановились?');
                }
                $resumeModal.addClass('is-open');
                return true;
            }

            function hideResumeModal() {
                $resumeModal.removeClass('is-open');
            }

            function closeQualityMenu() {
                $qualityMenu.removeClass('is-open');
                $qualityToggle.removeClass('is-open');
            }

            function renderQualityMenu(data) {
                var items = data && data.videos ? data.videos : [];
                var html = '<div class="player-quality-menu__panel">';
                var currentLabel = '—';

                $.each(items, function (_, item) {
                    var filePath = item.file_path || '';
                    if (!filePath) return;
                    var label = getQualityLabel(item);
                    var isActive = filePath === activeQualityPath || ((data && data.current_video && data.current_video.quality) ? String(data.current_video.quality) === String(item.quality || '') : false);
                    var activeClass = isActive ? ' is-active' : '';
                    if (isActive) {
                        currentLabel = label;
                    }
                    html += '' +
                        '<button type="button" class="player-quality-menu__item js-player-quality-option' + activeClass + '" data-file="' + self.escapeHtml(filePath) + '">' +
                            '<span class="player-quality-menu__item-check" aria-hidden="true"></span>' +
                            '<span class="player-quality-menu__item-label">' + self.escapeHtml(label) + '</span>' +
                        '</button>';
                });

                if (items.length === 0) {
                    html += '<div class="player-quality-menu__empty">Нет вариантов качества</div>';
                }

                html += '</div>';
                $qualityMenu.html(html);
                $qualityToggle.find('.player-quality-trigger__value').text(currentLabel);
            }

            function switchQuality(filePath) {
                if (!filePath || !player) return;
                var currentTime = Math.floor(player.currentTime || 0);
                var wasPaused = player.paused;
                activeQualityPath = filePath;
                if (currentData && currentData.current_video) {
                    $.each((currentData.videos || []), function (_, videoItem) {
                        if ((videoItem.file_path || '') === filePath) {
                            currentData.current_video.quality = videoItem.quality || '';
                            return false;
                        }
                    });
                }
                pendingSeekTime = currentTime;
                pendingAutoplay = !wasPaused;
                shouldPromptResume = false;
                setBusy(true, 'Переключаем качество…');
                player.src = filePath;
                player.load();
                renderQualityMenu(currentData);
                closeQualityMenu();
            }

            function renderSlideItems(data) {
                var html = '';
                var items = data && data.slides_all ? data.slides_all : [];
                $.each(items, function (_, item) {
                    var thumb = item.image || (data.current_video && data.current_video.preview_image ? data.current_video.preview_image : '') || (data.lesson && data.lesson.poster ? data.lesson.poster : '');
                    var lockedClass = parseInt(item.locked || 0, 10) === 1 ? ' is-locked' : '';
                    html += '' +
                        '<button type="button" class="player-slide-item js-player-slide-jump' + lockedClass + '" data-url="' + self.escapeHtml(item.url || '') + '" data-video="' + parseInt(item.lesson_video_id || 0, 10) + '" data-slide-id="' + parseInt(item.id || 0, 10) + '" data-timecode="' + parseInt(item.timecode_ms || 0, 10) + '" data-image="' + self.escapeHtml(item.image || '') + '" data-locked="' + (parseInt(item.locked || 0, 10) === 1 ? '1' : '0') + '">' +
                            '<span class="player-slide-item__thumb"><img src="' + self.escapeHtml(thumb) + '" alt=""></span>' +
                            '<span class="player-slide-item__content">' +
                                '<span class="player-slide-item__text">' + self.escapeHtml(item.title || '') + '</span>' +
                                '<span class="player-slide-item__meta">' + self.escapeHtml(item.meta || '') + '</span>' +
                            '</span>' +
                        '</button>';
                });
                $slideList.html(html);
                applySearch();
                refreshActiveSlideList(true);
            }

            function updateCurrentVideoUi() {
                if (!currentData || !currentData.current_video) {
                    return;
                }
                var data = currentData;
                var videoCounterText = (data.current_video && data.current_video.total)
                    ? (data.current_video.position + ' из ' + data.current_video.total)
                    : ((data.nav ? data.nav.current_index : 0) + ' из ' + (data.nav ? data.nav.total_lessons : 0));
                var subtitleParts = [];
                if (data.course && data.course.title) {
                    subtitleParts.push(data.course.title);
                }
                if (data.current_video && data.current_video.title) {
                    subtitleParts.push(data.current_video.title);
                }
                subtitleParts.push('Видео ' + videoCounterText);
                if (data.current_video && data.current_video.duration_text) {
                    subtitleParts.push(data.current_video.duration_text);
                }
                $subtitle.text(subtitleParts.join(' • '));
                $counter.text(videoCounterText);
                applyNav(data);
                renderQualityMenu(data);
                refreshActiveSlideList(true);
            }

            function switchToVideo(videoId, options) {
                options = options || {};
                videoId = parseInt(videoId || 0, 10) || 0;
                var targetState = getVideoStateById(videoId);
                if (!targetState) {
                    setMessage('Не удалось открыть выбранное видео.', true);
                    return;
                }
                if (parseInt(targetState.locked || 0, 10) === 1) {
                    setMessage('Это видео пока заблокировано.', true);
                    return;
                }

                if (videoId !== currentVideoId) {
                    saveProgress(false, {silent: true, skipUiSync: true});
                }

                currentVideoId = videoId;
                requestedVideo = videoId;
                pendingSwitchSlideId = parseInt(options.slideId || 0, 10) || 0;
                pendingSwitchTimecode = parseInt(options.timecodeMs || 0, 10) || 0;
                pendingSwitchImage = options.image || '';
                lastEnsuredActiveSlideId = 0;

                var state = syncCurrentVideoStateFromItems();
                if (!state || !currentData.current_video) {
                    return;
                }

                completeSent = parseInt(currentData.current_video.completed || 0, 10) === 1;
                updateUrl(requestedModule, requestedLesson, currentVideoId);
                updateCurrentVideoUi();

                var poster = currentData.current_video.preview_image || (currentData.lesson && currentData.lesson.poster ? currentData.lesson.poster : '');
                if (activeTimelineSlides && activeTimelineSlides.length && activeTimelineSlides[0].image) {
                    poster = activeTimelineSlides[0].image;
                }
                if (poster) {
                    player.setAttribute('poster', poster);
                } else {
                    player.removeAttribute('poster');
                }

                if (pendingSwitchImage) {
                    setMainSlide(pendingSwitchImage, pendingSwitchSlideId || 0);
                } else if (activeTimelineSlides && activeTimelineSlides.length) {
                    setMainSlide(activeTimelineSlides[0].image || poster, parseInt(activeTimelineSlides[0].id || 0, 10));
                } else if (poster) {
                    setMainSlide(poster, 0);
                }

                activeQualityPath = currentData.current_video.video_url || '';
                pendingSeekTime = pendingSwitchTimecode > 0 ? (pendingSwitchTimecode / 1000) : null;
                pendingAutoplay = true;
                shouldPromptResume = false;
                try {
                    player.pause();
                } catch (e) {}
                updatePlayUi();
                setBusy(true, 'Загрузка видео…');
                player.src = activeQualityPath;
                player.load();
            }

            function renderData(data) {
                currentData = data || null;
                if (!currentData) return;

                currentVideoId = parseInt(data.resolved_video_id || (data.current_video ? data.current_video.id : 0) || requestedVideo, 10) || 0;
                requestedModule = parseInt(data.resolved_module_resource_id || requestedModule, 10) || requestedModule;
                requestedLesson = parseInt(data.resolved_lesson_id || requestedLesson, 10) || requestedLesson;
                requestedVideo = currentVideoId;
                lastEnsuredActiveSlideId = 0;
                recalculateVideoLocks();
                syncCurrentVideoStateFromItems();
                completeSent = currentData.current_video && parseInt(currentData.current_video.completed || 0, 10) === 1;
                activeTimelineSlides = getTimelineSlidesByVideo(currentVideoId);

                updateUrl(requestedModule, requestedLesson, currentVideoId);
                $title.text((data.module && data.module.title ? data.module.title : '') + ' • ' + (data.lesson && data.lesson.title ? data.lesson.title : ''));
                syncMobilePlayerCaption();
                $backBtn.attr('data-url', data.course && data.course.url ? data.course.url : '');

                closeSearch();
                renderSlideItems(data);
                updateCurrentVideoUi();
                setMessage(data.redirected ? 'Открыт ближайший доступный урок.' : '', false);

                var poster = data.lesson && data.lesson.poster ? data.lesson.poster : '';
                if (activeTimelineSlides && activeTimelineSlides.length) {
                    setMainSlide(activeTimelineSlides[0].image || poster, parseInt(activeTimelineSlides[0].id || 0, 10));
                } else if (poster) {
                    setMainSlide(poster, 0);
                }

                var videoUrl = data.current_video && data.current_video.video_url ? data.current_video.video_url : '';
                activeQualityPath = videoUrl;
                renderQualityMenu(data);

                if (poster) {
                    player.setAttribute('poster', poster);
                } else {
                    player.removeAttribute('poster');
                }
                movePlayerVideo();

                if (videoUrl && player.getAttribute('src') !== videoUrl) {
                    setBusy(true, 'Загрузка видео…');
                    pendingSeekTime = null;
                    pendingAutoplay = false;
                    shouldPromptResume = true;
                    try {
                        player.pause();
                    } catch (e) {}
                    updatePlayUi();
                    player.src = videoUrl;
                    player.load();
                } else {
                    setBusy(false);
                    updateVideoProgress();
                    refreshActiveSlideList(true);
                }

                if (!videoUrl) {
                    setMessage('У текущего видео нет активного качества.', true);
                }
            }

            function loadPlayer(forceMessage) {
                stopSaveInterval();
                hideResumeModal();
                closeQualityMenu();
                if (loadXhr && loadXhr.abort) {
                    loadXhr.abort();
                }
                setMessage(forceMessage ? 'Загрузка урока…' : '', false);

                setBusy(true, 'Загрузка урока…', true);
                loadXhr = self.trainingRequest('web/player/get', {
                    module: requestedModule,
                    lesson: requestedLesson,
                    video: requestedVideo
                }, $player).done(function (res) {
                    if (!res || !res.success || !res.object) {
                        setBusy(false);
                        setMessage(res && res.message ? res.message : 'Не удалось загрузить урок.', true);
                        return;
                    }
                    renderData(res.object);
                }).fail(function () {
                    setBusy(false);
                    setMessage('Ошибка загрузки урока.', true);
                });
            }

            function saveProgress(markCompleted, options) {
                options = options || {};
                if (!currentData || !player || !player.src) {
                    return;
                }

                var savingVideoId = parseInt(currentVideoId || 0, 10) || 0;
                if (!savingVideoId) {
                    return;
                }

                var currentTime = Math.floor(player.currentTime || 0);
                if (!markCompleted && currentTime === lastSavedTime) {
                    return;
                }
                lastSavedTime = currentTime;

                var durationSeconds = Math.floor(player.duration || (currentData.current_video ? currentData.current_video.duration_seconds : 0) || 0);
                var localState = getVideoStateById(savingVideoId);
                if (localState) {
                    localState.current_time = currentTime;
                    localState.max_time = Math.max(parseInt(localState.max_time || 0, 10) || 0, currentTime);
                    localState.resume_time = markCompleted ? 0 : Math.max(localState.current_time, localState.max_time);
                    if (markCompleted) {
                        localState.completed = 1;
                        localState.progress_percent = 100;
                        localState.status = 'completed';
                        localState.completedon = 1;
                    }
                }
                recalculateVideoLocks();
                if (!options.skipUiSync) {
                    syncRenderedSlideItems();
                    if (savingVideoId === currentVideoId) {
                        syncCurrentVideoStateFromItems();
                        updateCurrentVideoUi();
                    }
                }

                if (saveXhr && saveXhr.abort) {
                    saveXhr.abort();
                }

                saveXhr = self.trainingRequest('web/player/progress', {
                    module: requestedModule,
                    lesson: requestedLesson,
                    video: savingVideoId,
                    current_time: currentTime,
                    duration_seconds: durationSeconds,
                    completed: markCompleted ? 1 : 0
                }, $player).done(function (res) {
                    if (!res || !res.success || !res.object) {
                        return;
                    }
                    var videoState = getVideoStateById(savingVideoId);
                    if (videoState && res.object.video) {
                        videoState.progress_percent = parseInt(res.object.video.progress_percent || 0, 10) || 0;
                        videoState.completed = parseInt(res.object.video.completed || 0, 10) === 1 ? 1 : 0;
                        videoState.current_time = parseInt(res.object.video.current_time || 0, 10) || 0;
                        videoState.max_time = parseInt(res.object.video.max_time || 0, 10) || 0;
                        videoState.resume_time = videoState.completed ? 0 : Math.max(videoState.current_time, videoState.max_time);
                    }
                    recalculateVideoLocks();
                    if (!options.skipUiSync) {
                        syncRenderedSlideItems();
                        if (savingVideoId === currentVideoId) {
                            syncCurrentVideoStateFromItems();
                            updateCurrentVideoUi();
                        }
                    }
                });
            }

            function setFullscreenState() {
                var active = isFullscreenActive();
                $player.toggleClass('is-fullscreen', active);
                $playerCard.toggleClass('is-fullscreen', active);
                movePlayerVideo();
            }

            function requestFullscreen() {
                var node = fullscreenTarget;
                if (!node) {
                    return;
                }

                if (document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement) {
                    if (document.exitFullscreen) document.exitFullscreen();
                    else if (document.webkitExitFullscreen) document.webkitExitFullscreen();
                    else if (document.mozCancelFullScreen) document.mozCancelFullScreen();
                    else if (document.msExitFullscreen) document.msExitFullscreen();
                    return;
                }

                if (node.requestFullscreen) node.requestFullscreen();
                else if (node.webkitRequestFullscreen) node.webkitRequestFullscreen();
                else if (node.mozRequestFullScreen) node.mozRequestFullScreen();
                else if (node.msRequestFullscreen) node.msRequestFullscreen();
            }

            function openPlayerUrl(url, lockedMessage) {
                var parsed = parsePlayerUrl(url);
                if (!parsed || parsed.module <= 0 || parsed.lesson <= 0) {
                    if (lockedMessage) {
                        setMessage(lockedMessage, true);
                    }
                    return;
                }
                if (currentData && parsed.module === requestedModule && parsed.lesson === requestedLesson && (parseInt(parsed.video || 0, 10) || 0) > 0) {
                    switchToVideo(parsed.video, {});
                    return;
                }
                requestedModule = parsed.module;
                requestedLesson = parsed.lesson;
                requestedVideo = parsed.video || 0;
                loadPlayer(false);
            }

            $(document)
                .off('click.appTrainingPlayerBack')
                .on('click.appTrainingPlayerBack', '.js-player-back', function () {
                    var url = $(this).attr('data-url') || '';
                    if (url) {
                        window.location.href = url;
                    }
                })
                .off('click.appTrainingPlayerPrev')
                .on('click.appTrainingPlayerPrev', '.js-player-prev, .js-player-next', function () {
                    var $btn = $(this);
                    var locked = parseInt($btn.attr('data-locked') || 0, 10) === 1;
                    var targetVideoId = parseInt($btn.attr('data-video') || 0, 10) || 0;
                    var url = $btn.attr('data-url') || '';
                    if (locked) {
                        setMessage('Следующее видео пока недоступно.', true);
                        return;
                    }
                    if (targetVideoId > 0 && currentData) {
                        switchToVideo(targetVideoId, {});
                        return;
                    }
                    if (!url) {
                        setMessage('Следующее видео пока недоступно.', true);
                        return;
                    }
                    openPlayerUrl(url, 'Следующее видео пока недоступно.');
                })
                .off('click.appTrainingPlayerSlide')
                .on('click.appTrainingPlayerSlide', '.js-player-slide-jump', function () {
                    var $item = $(this);
                    var targetVideoId = parseInt($item.attr('data-video') || 0, 10) || 0;
                    var targetSlideId = parseInt($item.attr('data-slide-id') || 0, 10) || 0;
                    var targetTimecodeMs = parseInt($item.attr('data-timecode') || 0, 10) || 0;
                    var targetImage = $item.attr('data-image') || '';
                    var targetUrl = $item.attr('data-url') || '';
                    var locked = parseInt($item.attr('data-locked') || 0, 10) === 1;

                    if (locked) {
                        setMessage('Это видео пока заблокировано.', true);
                        return;
                    }

                    if (targetVideoId > 0 && targetVideoId !== currentVideoId) {
                        switchToVideo(targetVideoId, {
                            slideId: targetSlideId,
                            timecodeMs: targetTimecodeMs,
                            image: targetImage
                        });
                        return;
                    }

                    if (targetImage) {
                        setMainSlide(targetImage, targetSlideId);
                    } else {
                        activeSlideId = targetSlideId;
                        refreshActiveSlideList(true);
                    }

                    if (targetTimecodeMs > 0) {
                        try {
                            player.currentTime = targetTimecodeMs / 1000;
                            updateVideoProgress();
                        } catch (e) {}
                    }
                });

            $player.off('click.appTrainingPlay').on('click.appTrainingPlay', '.js-player-play', function () {
                if (player.paused || player.ended) {
                    player.play();
                } else {
                    player.pause();
                }
            });

            $player.off('click.appTrainingRestart').on('click.appTrainingRestart', '.js-player-restart', function () {
                hideResumeModal();
                pendingResumeTime = 0;
                completeSent = false;
                player.currentTime = 0;
                updateVideoProgress();
                saveProgress(false);
                player.play();
            });

            $player.off('click.appTrainingSound').on('click.appTrainingSound', '.js-player-sound', function (e) {
                if (window.matchMedia && window.matchMedia('(max-width: 991px)').matches) {
                    e.preventDefault();
                    $(this).closest('.player-volume-wrap').toggleClass('is-open-volume');
                    return;
                }
                player.muted = !player.muted;
                if (!player.muted && player.volume <= 0.01) {
                    player.volume = 0.7;
                }
                updateVolumeUi();
            });

            $volume.off('input.appTrainingVolume change.appTrainingVolume').on('input.appTrainingVolume change.appTrainingVolume', function () {
                var value = Math.max(0, Math.min(100, parseInt($(this).val() || 0, 10)));
                player.volume = value / 100;
                player.muted = value === 0;
                updateVolumeUi();
            });

            $player.off('click.appTrainingSidebarToggle').on('click.appTrainingSidebarToggle', '.js-player-sidebar-toggle', function () {
                $player.toggleClass('is-sidebar-collapsed');
                $(this).toggleClass('is-collapsed', $player.hasClass('is-sidebar-collapsed'));
            });

            $player.off('click.appTrainingMobileVideoToggle').on('click.appTrainingMobileVideoToggle', '.player-mobile-video-toggle', function () {
                var collapsed = !$player.hasClass('is-video-collapsed');
                $player.toggleClass('is-video-collapsed', collapsed);
                $(this)
                    .toggleClass('is-collapsed', collapsed)
                    .attr('aria-expanded', collapsed ? 'false' : 'true');
            });

            $searchToggle.off('click.appTrainingSearchToggle').on('click.appTrainingSearchToggle', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if ($player.hasClass('is-search-open')) {
                    closeSearch();
                } else {
                    openSearch();
                }
            });

            $searchClose.off('click.appTrainingSearchClose').on('click.appTrainingSearchClose', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeSearch();
            });

            $sidebarSearch.off('input.appTrainingSidebarSearch').on('input.appTrainingSidebarSearch', function () {
                applySearch();
            });

            $(document).off('click.appTrainingSearchOutside').on('click.appTrainingSearchOutside', function (e) {
                if (!$player.hasClass('is-search-open')) return;
                if ($(e.target).closest('.player-sidebar__head').length) return;
                closeSearch();
            });

            $player.off('click.appTrainingResumeStart').on('click.appTrainingResumeStart', '.js-player-start-over', function () {
                hideResumeModal();
                pendingResumeTime = 0;
                shouldPromptResume = false;
                lastSavedTime = -1;
                try {
                    player.currentTime = 0;
                } catch (e) {}
                if (currentData && currentData.current_video) {
                    currentData.current_video.resume_time = 0;
                }
                saveProgress(false);
                player.play();
            });

            $resumeClose.off('click.appTrainingResumeClose').on('click.appTrainingResumeClose', function () {
                hideResumeModal();
            });

            $player.off('click.appTrainingResumeContinue').on('click.appTrainingResumeContinue', '.js-player-resume-continue', function () {
                hideResumeModal();
                if (pendingResumeTime > 0 && player.duration && pendingResumeTime < player.duration) {
                    player.currentTime = pendingResumeTime;
                } else if (pendingResumeTime <= 0) {
                    try {
                        player.currentTime = 0;
                    } catch (e) {}
                }
                player.play();
            });

            $progressBar.off('click.appTrainingProgress').on('click.appTrainingProgress', function (e) {
                if (!player.duration || !isFinite(player.duration)) return;
                var barWidth = $(this).outerWidth();
                var offsetX = e.pageX - $(this).offset().left;
                var percent = (offsetX / barWidth);
                player.currentTime = Math.max(0, Math.min(player.duration, player.duration * percent));
                updateVideoProgress();
            });

            $qualityToggle.off('click.appTrainingQualityToggle').on('click.appTrainingQualityToggle', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $qualityMenu.toggleClass('is-open');
                $qualityToggle.toggleClass('is-open', $qualityMenu.hasClass('is-open'));
            });

            $qualityMenu.off('click.appTrainingQualityItem').on('click.appTrainingQualityItem', '.js-player-quality-option', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var filePath = $(this).attr('data-file') || '';
                if (filePath) {
                    switchQuality(filePath);
                }
            });

            $(document).off('click.appTrainingQualityClose').on('click.appTrainingQualityClose', function (e) {
                if (!$(e.target).closest('.player-quality').length) {
                    closeQualityMenu();
                }
            });

            $fullscreenBtn.off('click.appTrainingFullscreen').on('click.appTrainingFullscreen', function (e) {
                e.preventDefault();
                requestFullscreen();
            });

            $(document)
                .off('fullscreenchange.appTrainingPlayer webkitfullscreenchange.appTrainingPlayer mozfullscreenchange.appTrainingPlayer MSFullscreenChange.appTrainingPlayer')
                .on('fullscreenchange.appTrainingPlayer webkitfullscreenchange.appTrainingPlayer mozfullscreenchange.appTrainingPlayer MSFullscreenChange.appTrainingPlayer', function () {
                    setFullscreenState();
                });

            $(player)
                .off('loadedmetadata.appTrainingPlayer')
                .on('loadedmetadata.appTrainingPlayer', function () {
                    updateVolumeUi();
                    if (pendingSeekTime !== null) {
                        var seekValue = parseInt(pendingSeekTime || 0, 10) || 0;
                        if (seekValue > 0 && player.duration && seekValue < player.duration) {
                            player.currentTime = seekValue;
                        }
                        if (pendingAutoplay) {
                            player.play();
                        }
                        pendingSeekTime = null;
                        pendingAutoplay = false;
                        if (pendingSwitchImage) {
                            setMainSlide(pendingSwitchImage, pendingSwitchSlideId || 0);
                            pendingSwitchImage = '';
                            pendingSwitchSlideId = 0;
                        }
                        pendingSwitchTimecode = 0;
                        setBusy(false);
                        updateVideoProgress();
                        return;
                    }

                    updateVideoProgress();
                    if (!currentData || !currentData.current_video) {
                        setBusy(false);
                        return;
                    }

                    if (shouldPromptResume) {
                        shouldPromptResume = false;
                        var resume = parseInt(currentData.current_video.resume_time || 0, 10);
                        var completedVideo = parseInt(currentData.current_video.completed || 0, 10) === 1;
                        if (!showResumeModal(resume, completedVideo)) {
                            if (resume > 0 && player.duration && resume < player.duration) {
                                player.currentTime = resume;
                                updateVideoProgress();
                            }
                            var autoPlayPromise = player.play();
                            if (autoPlayPromise && typeof autoPlayPromise.catch === 'function') {
                                autoPlayPromise.catch(function () {
                                    updatePlayUi();
                                });
                            }
                        }
                    } else if (pendingAutoplay) {
                        pendingAutoplay = false;
                        var switchedPlayPromise = player.play();
                        if (switchedPlayPromise && typeof switchedPlayPromise.catch === 'function') {
                            switchedPlayPromise.catch(function () {
                                updatePlayUi();
                            });
                        }
                    }
                    setBusy(false);
                })
                .off('timeupdate.appTrainingPlayer')
                .on('timeupdate.appTrainingPlayer', function () {
                    updateVideoProgress();
                })
                .off('play.appTrainingPlayer')
                .on('play.appTrainingPlayer', function () {
                    updatePlayUi();
                    startSaveInterval();
                })
                .off('pause.appTrainingPlayer')
                .on('pause.appTrainingPlayer', function () {
                    updatePlayUi();
                    stopSaveInterval();
                    saveProgress(false);
                })
                .off('ended.appTrainingPlayer')
                .on('ended.appTrainingPlayer', function () {
                    updatePlayUi();
                    stopSaveInterval();
                    completeSent = true;
                    var nextVideoId = getAdjacentVideoId('next');
                    saveProgress(true, {silent: true, skipUiSync: false});
                    if (nextVideoId > 0) {
                        setTimeout(function () {
                            switchToVideo(nextVideoId, {});
                        }, 120);
                    }
                })
                .off('volumechange.appTrainingPlayer')
                .on('volumechange.appTrainingPlayer', function () {
                    updateVolumeUi();
                });

            $(document)
                .off('visibilitychange.appTrainingPlayer')
                .on('visibilitychange.appTrainingPlayer', function () {
                    if (document.hidden) {
                        saveProgress(false);
                    }
                });

            $(window)
                .off('beforeunload.appTrainingPlayer')
                .on('beforeunload.appTrainingPlayer', function () {
                    stopSaveInterval();
                    saveProgress(false);
                });

            updateVolumeUi();
            updatePlayUi();
            setFullscreenState();
            movePlayerVideo();
            loadPlayer(true);
        },

        courseStructure: function () {
            var $modules = $('.cs-module');
            if (!$modules.length) return;

            function setBodyHeight($module, open, instant) {
                var $body = $module.find('[data-cs-body]').first();
                if (!$body.length) return;

                var target = open ? $body.get(0).scrollHeight : 0;

                if (instant) {
                    $body.css('transition', 'none').height(target);
                    $body.get(0).offsetHeight;
                    $body.css('transition', '');
                    return;
                }

                $body.height(target);
            }

            $modules.each(function () {
                var $m = $(this);
                var isOpen = $m.hasClass('is-open');
                $m.find('[data-cs-toggle]').attr('aria-expanded', isOpen ? 'true' : 'false');
                setBodyHeight($m, isOpen, true);
            });

            $(document)
                .off('click.appTrainingCourseStruct')
                .on('click.appTrainingCourseStruct', '[data-cs-toggle]', function (e) {
                    e.preventDefault();

                    var $head = $(this);
                    var $module = $head.closest('.cs-module');
                    var isOpen = $module.hasClass('is-open');

                    if (isOpen) {
                        var $body = $module.find('[data-cs-body]').first();
                        $body.height($body.get(0).scrollHeight);
                        $body.get(0).offsetHeight;

                        $module.removeClass('is-open');
                        $head.attr('aria-expanded', 'false');
                        $body.height(0);
                        return;
                    }

                    $module.addClass('is-open');
                    $head.attr('aria-expanded', 'true');
                    setBodyHeight($module, true, false);
                });

            $(document)
                .off('transitionend.appTrainingCourseStruct')
                .on('transitionend.appTrainingCourseStruct', '.cs-body', function () {
                    var $body = $(this);
                    var $module = $body.closest('.cs-module');
                    if ($module.hasClass('is-open')) {
                        $body.height($body.get(0).scrollHeight);
                    }
                });

            this.courseStructureRecalc = function ($module) {
                if (!$module || !$module.length) return;
                if ($module.hasClass('is-open')) setBodyHeight($module, true, false);
            };
        },

        coursePage: function () {
            var COLLAPSED = 169;

            function setExpanded($wrap, $text, on) {
                if (on) {
                    var h = $text.get(0).scrollHeight;
                    $wrap.addClass('is-open');
                    $text.css('max-height', h + 'px');
                    $wrap.find('[data-course-about-toggle]').text('Свернуть');
                } else {
                    $wrap.removeClass('is-open');
                    $text.css('max-height', COLLAPSED + 'px');
                    $wrap.find('[data-course-about-toggle]').text('Показать еще');
                }
            }

            $('.course-about').each(function () {
                var $wrap = $(this);
                var $text = $wrap.find('[data-course-about]').first();
                if (!$text.length) return;

                $text.css('max-height', COLLAPSED + 'px');

                var full = $text.get(0).scrollHeight;
                if (full <= COLLAPSED + 5) {
                    $wrap.find('[data-course-about-toggle]').hide();
                    $text.addClass('is-full');
                } else {
                    $wrap.find('[data-course-about-toggle]').show().text('Показать больше');
                }
            });

            $(document)
                .off('click.appTrainingCourseAbout')
                .on('click.appTrainingCourseAbout', '[data-course-about-toggle]', function () {
                    var $wrap = $(this).closest('.course-about');
                    var $text = $wrap.find('[data-course-about]').first();
                    if (!$text.length) return;

                    var isOpen = $wrap.hasClass('is-open');
                    setExpanded($wrap, $text, !isOpen);
                });

            $(window)
                .off('resize.appTrainingCourseAbout')
                .on('resize.appTrainingCourseAbout', function () {
                    $('.course-about.is-open').each(function () {
                        var $wrap = $(this);
                        var $text = $wrap.find('[data-course-about]').first();
                        if (!$text.length) return;
                        $text.css('max-height', $text.get(0).scrollHeight + 'px');
                    });
                });
        },

        sertModal: function () {
            if (typeof bootstrap === 'undefined') return;

            var modalEl = document.getElementById('sertModal');
            if (!modalEl) return;

            var bsModal = bootstrap.Modal.getInstance(modalEl);
            if (!bsModal) {
                bsModal = new bootstrap.Modal(modalEl, {
                    backdrop: true,
                    keyboard: true
                });
            }

            $(document).on('click', '.sert-btn', function (e) {
                e.preventDefault();

                var img = $(this).data('img') || 'theme/images/training/sert/image_1.jpg';
                var file = $(this).data('file') || img;

                $('#sertModal .sert-modal__img').attr('src', img);
                $('#sertModal .sert-modal__download').attr('href', file);

                bsModal.show();
            });
        },

        testListModal: function () {
            if (typeof bootstrap === 'undefined') return;

            var modalEl = document.getElementById('testListModal');
            if (!modalEl) return;

            var bsModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                backdrop: true,
                keyboard: true
            });

            var refreshXhr = null;
            var refreshTimer = null;
            var lastStateKey = '';

            function getStateKey() {
                var step = $.trim($('.test-step-box').first().text() || '');
                var answerState = $.trim($('.ut-answer-result span').last().text() || '');
                var totalScore = $.trim($('.text-info-test span').eq(1).text() || '');
                var finishTitle = $.trim($('.results-block .test-question span').first().text() || '');
                return [window.location.pathname, window.location.search, step, answerState, totalScore, finishTitle].join('|');
            }

            function bindStepClick() {
                $(document)
                    .off('click.appTrainingTestStep')
                    .on('click.appTrainingTestStep', '#testListModal .js-test-step', function () {
                        var form = document.getElementById('UserTestForm');
                        var next = document.getElementById('next_step');
                        if (!form || !next) return;

                        next.value = $(this).attr('data-step') || '';
                        bsModal.hide();
                        form.submit();
                    });
            }

            function replaceModalContent(html) {
                var parsed = $.parseHTML(html, document, true);
                var $doc = $('<div>').append(parsed);
                var $newModal = $doc.find('#testListModal').first();
                if (!$newModal.length) {
                    return false;
                }

                var $currentModal = $('#testListModal');
                var $newHead = $newModal.find('.testListModal__head').first();
                var $newBody = $newModal.find('.testListModal__body').first();

                if ($newHead.length) {
                    $currentModal.find('.testListModal__head').first().html($newHead.html());
                }
                if ($newBody.length) {
                    $currentModal.find('.testListModal__body').first().html($newBody.html());
                }

                bindStepClick();
                return true;
            }

            function isStaticResultModal() {
                return $('#testListModal').attr('data-static-result') === '1';
            }

            function refreshModal(force) {
                if (isStaticResultModal()) {
                    return;
                }

                if (!force) {
                    var stateKey = getStateKey();
                    if (stateKey === lastStateKey) {
                        return;
                    }
                    lastStateKey = stateKey;
                }

                if (refreshXhr && refreshXhr.abort) {
                    refreshXhr.abort();
                }

                refreshXhr = $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    cache: false,
                    data: {
                        _ut_modal_refresh: Date.now()
                    }
                }).done(function (html) {
                    replaceModalContent(html);
                });
            }

            function scheduleRefresh(delay, force) {
                if (isStaticResultModal()) {
                    return;
                }

                if (refreshTimer) {
                    clearTimeout(refreshTimer);
                }
                refreshTimer = setTimeout(function () {
                    refreshModal(!!force);
                }, delay || 0);
            }

            bindStepClick();

            $(document)
                .off('click.appTrainingTestListOpen')
                .on('click.appTrainingTestListOpen', '.btn-list-test', function (e) {
                    if (!$(this).attr('data-bs-toggle')) {
                        e.preventDefault();
                        bsModal.show();
                    }
                    scheduleRefresh(0, true);
                });

            $(document)
                .off('shown.bs.modal.appTrainingTestList')
                .on('shown.bs.modal.appTrainingTestList', '#testListModal', function () {
                    scheduleRefresh(0, true);
                });

            var root = document.getElementById('testuser-main');
            if (root && !root._appTrainingModalObserver) {
                root._appTrainingModalObserver = new MutationObserver(function () {
                    scheduleRefresh(150, false);
                });
                root._appTrainingModalObserver.observe(root, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            }
        },

        filtersSwipers: function () {
            if (typeof Swiper === 'undefined') return;

            $('.my-courses .course-filters-swiper, .training-manage-page .course-filters-swiper').each(function (index, element) {
                if (element.swiper) return;

                new Swiper(element, {
                    slidesPerView: 'auto',
                    spaceBetween: 16,
                    freeMode: {
                        enabled: true,
                        momentum: true
                    },
                    watchOverflow: true
                });
            });
        },

        filtersLogic: function () {
            const $blocks = $('.my-courses');
            if (!$blocks.length) return;

            const map = {
                progress: 'is-progress',
                done: 'is-done',
                new: 'is-new'
            };

            function setActive($scope, $btn) {
                $scope.find('.filters-corses .course-filter__chip')
                    .removeClass('is-active')
                    .attr('aria-selected', 'false');

                $btn.addClass('is-active').attr('aria-selected', 'true');
            }

            function getItemsContainer($scope) {
                let $list = $scope.find('.corses-items').first();
                if ($list.length) return $list;

                $list = $scope.find('.sert-items').first();
                if ($list.length) return $list;

                $list = $scope.find('*').filter(function () {
                    return $(this).find('.course-item, .sert-item').length;
                }).first();

                return $list.length ? $list : $scope;
            }

            function applyFilter($scope, filter) {
                const $list = getItemsContainer($scope);
                const $items = $list.find('.course-item, .sert-item');
                if (!$items.length) return;

                if (filter === 'all') {
                    $items.show();
                    return;
                }

                const cls = map[filter] || ('is-' + filter);
                $items.each(function () {
                    const $item = $(this);
                    $item.toggle($item.hasClass(cls));
                });
            }

            $(document).on('click', '.my-courses .filters-corses .course-filter__chip', function (e) {
                e.preventDefault();

                const $btn = $(this);
                const $scope = $btn.closest('.my-courses');
                const filter = $btn.data('filter') || 'all';

                setActive($scope, $btn);
                applyFilter($scope, filter);
            });

            $blocks.each(function () {
                const $scope = $(this);
                const $active = $scope.find('.filters-corses .course-filter__chip.is-active').first();
                const $first = $scope.find('.filters-corses .course-filter__chip').first();
                const $initBtn = $active.length ? $active : $first;

                if ($initBtn.length) {
                    setActive($scope, $initBtn);
                    applyFilter($scope, $initBtn.data('filter') || 'all');
                }
            });
        },

        historyModal: function () {
            var $any = $('.js-history-item');
            if (!$any.length) return;

            var $modal = $('#historyModal');
            if (!$modal.length) {
                var tpl = `
                  <div class="history-modal" id="historyModal" aria-hidden="true">
                    <div class="history-modal__overlay" data-history-close></div>

                    <button type="button"
                            class="history-modal__close"
                            aria-label="Закрыть"
                            data-history-close>
                      <svg xmlns="http://www.w3.org/2000/svg"
                           width="24" height="24"
                           viewBox="0 0 24 24"
                           fill="none">
                        <path d="M18 6L6 18M6 6L18 18"
                              stroke="black"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"/>
                      </svg>
                    </button>

                    <div class="history-modal__sheet" role="dialog" aria-modal="true">
                      <div class="history-modal__content">
                        <div class="history-modal__hint" data-hm-hint>Загрузка…</div>

                        <div class="history-modal__grid">
                          <div class="history-modal__row">
                            <div class="history-modal__k">Дата/Время</div>
                            <div class="history-modal__v" data-hm-date>—</div>
                          </div>

                          <div class="history-modal__row">
                            <div class="history-modal__k">Материалы</div>
                            <div class="history-modal__v" data-hm-mat>—</div>
                          </div>

                          <div class="history-modal__row">
                            <div class="history-modal__k">Статус</div>
                            <div class="history-modal__v" data-hm-status>—</div>
                          </div>

                          <div class="history-modal__row">
                            <div class="history-modal__k">Просмотрено</div>
                            <div class="history-modal__v" data-hm-viewed>—</div>
                          </div>

                          <div class="history-modal__row">
                            <div class="history-modal__k">Баллы</div>
                            <div class="history-modal__v" data-hm-score>—</div>
                          </div>

                          <div class="history-modal__row">
                            <div class="history-modal__k">Продолжительность</div>
                            <div class="history-modal__v" data-hm-dur>—</div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                `;
                $('body').append(tpl);
                $modal = $('#historyModal');
            }

            var $body = $('body');

            var $hint = $modal.find('[data-hm-hint]');
            var $out = {
                date: $modal.find('[data-hm-date]'),
                mat: $modal.find('[data-hm-mat]'),
                status: $modal.find('[data-hm-status]'),
                viewed: $modal.find('[data-hm-viewed]'),
                score: $modal.find('[data-hm-score]'),
                dur: $modal.find('[data-hm-dur]')
            };

            var currentXhr = null;

            var demoHistoryData = {
                date: '5 сент. 2025 8:54',
                materials: 'Модуль 3. Урок 1. Штрихкодирование 202',
                status: 'В процессе',
                viewed: '6% (1/16)',
                score: '0 (0%)',
                duration: '00:00:01'
            };

            function setLoading(isLoading) {
                $modal.toggleClass('is-loading', !!isLoading);
                $hint.toggle(!!isLoading);
            }

            function setPlaceholders() {
                $out.date.text('—');
                $out.mat.text('—');
                $out.status.text('—');
                $out.viewed.text('—');
                $out.score.text('—');
                $out.dur.text('—');
            }

            function fill(data) {
                $out.date.text(data.date || '—');
                $out.mat.text(data.materials || '—');
                $out.status.text(data.status || '—');
                $out.viewed.text(data.viewed || '—');
                $out.score.text(data.score || '—');
                $out.dur.text(data.duration || '—');
            }

            function openModal() {
                $modal.addClass('is-open').attr('aria-hidden', 'false');
                $body.addClass('is-modal-open');
            }

            function closeModal() {
                $modal.removeClass('is-open').attr('aria-hidden', 'true');
                $body.removeClass('is-modal-open');
                setLoading(false);
                if (currentXhr && currentXhr.abort) currentXhr.abort();
            }

            $(document)
                .off('click.appTrainingHistoryClose')
                .on('click.appTrainingHistoryClose', '#historyModal [data-history-close]', function (e) {
                    e.preventDefault();
                    closeModal();
                });

            $(document)
                .off('keydown.appTrainingHistoryEsc')
                .on('keydown.appTrainingHistoryEsc', function (e) {
                    if (e.key === 'Escape' && $modal.hasClass('is-open')) closeModal();
                });

            $(document)
                .off('click.appTrainingHistoryOpen')
                .on('click.appTrainingHistoryOpen', '.js-history-item', function (e) {
                    if ($(e.target).closest('a, button').length) return;

                    var $item = $(this);

                    setPlaceholders();
                    setLoading(true);
                    openModal();

                    var id = $item.data('historyId');
                    var rowData = {};

                    if ($item.hasClass('history-table__row')) {
                        var date = $.trim($item.find('.history-date').text());
                        var time = $.trim($item.find('.history-time').text());

                        rowData = {
                            date: (date + ' ' + time).trim(),
                            materials: $.trim($item.find('.history-mat__title').text()),
                            status: $.trim($item.find('.history-col--status').text()),
                            viewed: $.trim($item.find('.history-col--viewed').text()),
                            score: $.trim($item.find('.history-col--score').text()),
                            duration: $.trim($item.find('.history-col--dur').text())
                        };
                    } else {
                        var date2 = $.trim($item.find('.history-card__date').text());
                        var time2 = $.trim($item.find('.history-card__time').text());
                        rowData = {
                            date: (date2 + ' ' + time2).trim(),
                            materials: $.trim($item.find('.history-card__title').text()),
                            status: $.trim($item.find('.history-card__status').text()),
                            viewed: $.trim($item.find('.history-card__meta .history-card__kv').eq(0).find('.history-card__v').text()),
                            score: $.trim($item.find('.history-card__meta .history-card__kv').eq(1).find('.history-card__v').text()),
                            duration: $.trim($item.find('.history-card__meta .history-card__kv').eq(2).find('.history-card__v').text())
                        };
                    }

                    setTimeout(function () {
                        fill({
                            date: rowData.date || demoHistoryData.date,
                            materials: rowData.materials || demoHistoryData.materials,
                            status: rowData.status || demoHistoryData.status,
                            viewed: rowData.viewed || demoHistoryData.viewed,
                            score: rowData.score || demoHistoryData.score,
                            duration: rowData.duration || demoHistoryData.duration
                        });
                        setLoading(false);
                    }, 250);
                });
        },


        trainingRequestCourseModal: function () {
            var modalId = 'trainingCourseRequestModal';

            function normalizeBareModal() {
                $('[data-request-course-form]').each(function () {
                    var $form = $(this);
                    if ($form.closest('.modal.training-request-modal').length) {
                        return;
                    }

                    var $content = $form.closest('.modal-content');
                    if (!$content.length) {
                        $content = $('<div class="modal-content"></div>');
                        $form.wrap($content);
                        $content = $form.closest('.modal-content');
                    }

                    var $modal = $('#' + modalId);
                    if (!$modal.length) {
                        $modal = $('<div class="modal fade training-request-modal" id="' + modalId + '" tabindex="-1" aria-labelledby="trainingCourseRequestModalLabel" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"></div></div></div>');
                        $('body').append($modal);
                    }

                    $modal.addClass('training-request-modal');
                    $modal.find('.modal-content').first().replaceWith($content.detach());
                });
            }

            function setSelectedCourse($trigger) {
                var $modal = $('#' + modalId);
                if (!$modal.length) return;

                var $scope = $trigger.closest('.training-manage-page');
                var $activeCourse = $scope.find('.course-filter__chip.is-active').first();
                var courseId = $trigger.attr('data-course-id') || ($activeCourse.length ? $activeCourse.attr('data-course-id') : '') || '';
                var resourceId = $trigger.attr('data-resource-id') || ($activeCourse.length ? $activeCourse.attr('data-resource-id') : '') || '';
                var title = $trigger.attr('data-course-title') || ($activeCourse.length ? $.trim($activeCourse.text()) : '') || '';

                $modal.find('[data-request-course-id]').val(courseId);
                $modal.find('[data-request-course-resource-id]').val(resourceId);
                $modal.find('[data-request-course-title]').val(title);
                $modal.find('[data-request-course-selected-title]').val(title);
                $modal.find('[data-request-course-selected-wrap]').toggleClass('d-none', !title);
            }

            normalizeBareModal();

            $(document)
                .off('click.appTrainingRequestCourseOpen')
                .on('click.appTrainingRequestCourseOpen', '[data-bs-target="#trainingCourseRequestModal"], [data-training-request-course]', function () {
                    normalizeBareModal();
                    setSelectedCourse($(this));
                })
                .off('shown.bs.modal.appTrainingRequestCourse')
                .on('shown.bs.modal.appTrainingRequestCourse', '#' + modalId, function () {
                    normalizeBareModal();
                    $(this).find('input[name="fullname"]').trigger('focus');
                });
        },

        usersCoursesUi: function () {
            var self = this;
            var $wrap = $('#training-manage-page[data-training-page="managecourses"], .training-manage-page[data-training-page="managecourses"]').first();
            if (!$wrap.length) return;

            var $table = $wrap.find('[data-users-table]').first();
            var $cards = $wrap.find('[data-users-cards]').first();
            var $search = $wrap.find('#users-search .search-input__field').first();
            var activeCourseId = 0;
            var activeResourceId = 0;
            var activeCourseTitle = '';
            var currentRows = [];
            var currentRowsMap = {};
            var searchTimer = null;
            var pendingAction = '';
            var pendingIds = [];
            var modalEl = document.getElementById('trainingCourseAccessModal');
            var accessModal = null;

            if (typeof bootstrap !== 'undefined' && modalEl) {
                accessModal = bootstrap.Modal.getOrCreateInstance(modalEl, {
                    backdrop: true,
                    keyboard: true
                });
            }

            function getRenderedUserCheckboxes() {
                return $wrap.find('.users-check[data-user-id]');
            }

            function getUniqueUserIds() {
                var ids = [];
                $.each(currentRows, function (_, item) {
                    var id = parseInt(item.id, 10) || 0;
                    if (id > 0) {
                        ids.push(id);
                    }
                });
                return ids;
            }

            function setUserSelection(userId, checked) {
                userId = parseInt(userId, 10) || 0;
                if (userId <= 0) return;
                $wrap.find('.users-check[data-user-id="' + userId + '"]').prop('checked', !!checked);
            }

            function getSelectedIds() {
                var map = {};
                var ids = [];
                getRenderedUserCheckboxes().each(function () {
                    var id = parseInt($(this).attr('data-user-id'), 10) || 0;
                    if (id > 0 && $(this).prop('checked') && !map[id]) {
                        map[id] = true;
                        ids.push(id);
                    }
                });
                return ids;
            }

            function getRowById(id) {
                id = parseInt(id, 10) || 0;
                return currentRowsMap[id] || null;
            }

            function formatDateTimeLocal(date) {
                function pad(value) {
                    return String(value).padStart(2, '0');
                }

                return date.getFullYear() + '-' + pad(date.getMonth() + 1) + '-' + pad(date.getDate()) + 'T' + pad(date.getHours()) + ':' + pad(date.getMinutes());
            }

            function addMonths(date, months) {
                var copy = new Date(date.getTime());
                var day = copy.getDate();
                copy.setMonth(copy.getMonth() + months);
                if (copy.getDate() < day) {
                    copy.setDate(0);
                }
                return copy;
            }

            function setRows(rows) {
                currentRows = rows || [];
                currentRowsMap = {};
                $.each(currentRows, function (_, item) {
                    var id = parseInt(item.id, 10) || 0;
                    if (id > 0) {
                        currentRowsMap[id] = item;
                    }
                });
            }

            function renderStatus(item) {
                return '<div class="label-chip ' + self.escapeHtml(item.status_class || 'label-chip--blue') + '"><span>' + self.escapeHtml(item.status_text || '—') + '</span></div>';
            }

            function renderTable(rows) {
                var html = '' +
                    '<div class="users-table__head">' +
                        '<div class="users-col users-col--check"><input type="checkbox" class="users-check" data-select-all aria-label="Выбрать все"></div>' +
                        '<div class="users-col users-col--user">Имя пользователя</div>' +
                        '<div class="users-col users-col--status">Статус</div>' +
                        '<div class="users-col users-col--org">Организация</div>' +
                        '<div class="users-col users-col--last">Последний вход</div>' +
                        '<div class="users-col users-col--start">Старт курса</div>' +
                    '</div>';

                if (!rows.length) {
                    html += '<div class="users-table__row"><div class="users-col" style="grid-column: 1 / -1;">Пользователи не найдены</div></div>';
                    $table.html(html);
                    syncSelectionState();
                    return;
                }

                $.each(rows, function (_, item) {
                    html += '' +
                        '<div class="users-table__row" data-user-row data-user-id="' + item.id + '">' +
                            '<div class="users-col users-col--check">' +
                                '<input type="checkbox" class="users-check" data-user-id="' + item.id + '" aria-label="Выбрать">' +
                            '</div>' +
                            '<div class="users-col users-col--user">' +
                                '<div class="user-mini">' +
                                    '<span class="user-mini__ava"><img src="theme/images/training/user-ico.svg" class="img-svg" alt=""></span>' +
                                    '<div class="user-mini__txt">' +
                                        '<div class="user-mini__name">' + self.escapeHtml(item.display_name || item.username || '') + '</div>' +
                                        '<div class="user-mini__mail">' + self.escapeHtml(item.email || '') + '</div>' +
                                    '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="users-col users-col--status">' + renderStatus(item) + '</div>' +
                            '<div class="users-col users-col--org">' + self.escapeHtml(item.organization || '—') + '</div>' +
                            '<div class="users-col users-col--last">' + self.escapeHtml(item.last_login_formatted || '—') + '</div>' +
                            '<div class="users-col users-col--start">' + self.escapeHtml(item.startedon_formatted || '—') + '</div>' +
                        '</div>';
                });

                $table.html(html);
                syncSelectionState();
            }

            function renderCards(rows) {
                if (!rows.length) {
                    $cards.html('<div class="course-card">Пользователи не найдены</div>');
                    syncSelectionState();
                    return;
                }

                var html = '';
                $.each(rows, function (_, item) {
                    html += '' +
                        '<div class="course-card" data-user-card data-user-id="' + item.id + '">' +
                            '<div class="course-card__top">' +
                                '<div class="course-org">' + self.escapeHtml(item.organization || '—') + '</div>' +
                                '<div>' + renderStatus(item) + '</div>' +
                            '</div>' +
                            '<div class="course-user">' +
                                '<div class="course-avatar" aria-hidden="true"><img src="theme/images/training/user-ico.svg" class="img-svg" alt=""></div>' +
                                '<div class="course-user__text">' +
                                    '<div class="course-name">' + self.escapeHtml(item.display_name || item.username || '') + '</div>' +
                                    '<div class="course-email">' + self.escapeHtml(item.email || '') + '</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="course-meta">' +
                                '<div class="course-meta__item">' +
                                    '<div class="course-meta__date">' + self.escapeHtml(item.last_login_formatted || '—') + '</div>' +
                                    '<div class="course-meta__label">Посл. вход</div>' +
                                '</div>' +
                                '<div class="course-meta__item text-end">' +
                                    '<div class="course-meta__date">' + self.escapeHtml(item.startedon_formatted || '—') + '</div>' +
                                    '<div class="course-meta__label">Старт курса</div>' +
                                '</div>' +
                            '</div>' +
                            '<div class="pt-2">' +
                                '<label class="d-flex align-items-center gap-2">' +
                                    '<input type="checkbox" class="users-check" data-user-id="' + item.id + '">' +
                                    '<span>Выбрать</span>' +
                                '</label>' +
                            '</div>' +
                        '</div>';
                });

                $cards.html(html);
                syncSelectionState();
            }

            function syncSelectionState(changedUserId, changedChecked) {
                changedUserId = parseInt(changedUserId, 10) || 0;

                if (changedUserId > 0) {
                    setUserSelection(changedUserId, !!changedChecked);
                }

                var selectedIds = getSelectedIds();
                var selectedMap = {};
                $.each(selectedIds, function (_, id) {
                    selectedMap[id] = true;
                });

                $.each(getUniqueUserIds(), function (_, id) {
                    setUserSelection(id, !!selectedMap[id]);
                });

                var total = getUniqueUserIds().length;
                var $selectAll = $wrap.find('[data-select-all]');
                var allSelected = total > 0 && selectedIds.length === total;
                var partiallySelected = selectedIds.length > 0 && selectedIds.length < total;

                $selectAll.prop('checked', allSelected);
                $selectAll.prop('indeterminate', partiallySelected);

                updateBulkButtons(selectedIds);
            }

            function updateBulkButtons(selectedIds) {
                selectedIds = selectedIds || getSelectedIds();
                var hasCourse = activeCourseId > 0 || activeResourceId > 0;
                var hasSelection = selectedIds.length > 0;
                var canUnassign = false;

                $.each(selectedIds, function (_, id) {
                    var row = getRowById(id);
                    if (row && parseInt(row.can_unassign || 0, 10) === 1) {
                        canUnassign = true;
                        return false;
                    }
                });

                $wrap.find('[data-bulk-action="assign"]').prop('disabled', !(hasCourse && hasSelection));
                $wrap.find('[data-bulk-action="unassign"]').prop('disabled', !(hasCourse && hasSelection && canUnassign));
            }

            function loadUsers() {
                if (!activeCourseId && !activeResourceId) {
                    setRows([]);
                    renderTable([]);
                    renderCards([]);
                    return;
                }

                self.trainingRequest('web/course/assignableusers', {
                    course_id: activeCourseId,
                    resource_id: activeResourceId,
                    query: $search.val() || '',
                    include_self: 0
                }, $wrap).done(function (res) {
                    var rows = [];
                    if (res && res.success && res.object && $.isArray(res.object.results)) {
                        rows = res.object.results;
                    }
                    setRows(rows);
                    renderTable(rows);
                    renderCards(rows);
                }).fail(function () {
                    setRows([]);
                    renderTable([]);
                    renderCards([]);
                });
            }

            function executeBulk(action, ids, extraData) {
                if (!ids.length) return;

                var processor = action === 'assign' ? 'web/course/assign' : 'web/course/unassign';
                var requests = [];

                $.each(ids, function (_, userId) {
                    requests.push(
                        self.trainingRequest(processor, $.extend({
                            course_id: activeCourseId,
                            resource_id: activeResourceId,
                            user_id: userId
                        }, extraData || {}), $wrap)
                    );
                });

                $.when.apply($, requests).always(function () {
                    pendingAction = '';
                    pendingIds = [];
                    loadUsers();
                });
            }

            function openBulkModal(action, ids) {
                if (!ids.length) return;

                if (!accessModal || !modalEl) {
                    if (action === 'assign') {
                        var now = new Date();
                        executeBulk('assign', ids, {
                            access_role: 'employee',
                            is_active: 1,
                            active_from: formatDateTimeLocal(now),
                            active_to: formatDateTimeLocal(addMonths(now, 3))
                        });
                    } else if (window.confirm('Снять доступ к курсу у выбранных сотрудников?')) {
                        executeBulk('unassign', ids, {});
                    }
                    return;
                }

                pendingAction = action;
                pendingIds = ids.slice(0);

                var $modal = $(modalEl);
                var $title = $modal.find('.modal-title').first();
                var $text = $modal.find('[data-access-modal-text]');
                var $count = $modal.find('[data-access-modal-count]');
                var $course = $modal.find('[data-access-modal-course]');
                var $dates = $modal.find('[data-access-modal-dates]');
                var $from = $modal.find('[data-access-active-from]');
                var $to = $modal.find('[data-access-active-to]');
                var $confirm = $modal.find('[data-access-modal-confirm]');
                var now = new Date();

                $count.text(ids.length);
                $course.text(activeCourseTitle || '—');

                if (action === 'assign') {
                    $title.text('Активация курса');
                    $text.text('Подтвердите активацию курса для выбранных сотрудников.');
                    $dates.removeClass('d-none');
                    $from.val(formatDateTimeLocal(now));
                    $to.val(formatDateTimeLocal(addMonths(now, 3)));
                    $confirm.text('Активировать');
                } else {
                    $title.text('Блокировка курса');
                    $text.text('Подтвердите блокировку курса для выбранных сотрудников.');
                    $dates.addClass('d-none');
                    $confirm.text('Заблокировать');
                }

                accessModal.show();
            }

            $(document)
                .off('click.appTrainingManageCourseFilter')
                .on('click.appTrainingManageCourseFilter', '#training-manage-page .course-filter__chip, .training-manage-page .course-filter__chip', function (e) {
                    e.preventDefault();

                    var $btn = $(this);
                    activeCourseId = parseInt($btn.attr('data-course-id') || 0, 10) || 0;
                    activeResourceId = parseInt($btn.attr('data-resource-id') || 0, 10) || 0;
                    activeCourseTitle = $.trim($btn.text() || '');

                    $btn.closest('.filters-corses, .swiper-wrapper').find('.course-filter__chip')
                        .removeClass('is-active')
                        .attr('aria-selected', 'false');

                    $btn.addClass('is-active').attr('aria-selected', 'true');
                    loadUsers();
                });

            $(document)
                .off('input.appTrainingManageSearch')
                .on('input.appTrainingManageSearch', '#training-manage-page #users-search .search-input__field, .training-manage-page #users-search .search-input__field', function () {
                    clearTimeout(searchTimer);
                    searchTimer = setTimeout(loadUsers, 250);
                });

            $(document)
                .off('change.appTrainingSelectAll')
                .on('change.appTrainingSelectAll', '#training-manage-page [data-select-all], .training-manage-page [data-select-all]', function () {
                    var checked = $(this).is(':checked');
                    $(this).prop('indeterminate', false);

                    $.each(getUniqueUserIds(), function (_, id) {
                        setUserSelection(id, checked);
                    });

                    syncSelectionState();
                });

            $(document)
                .off('change.appTrainingSelectedUser')
                .on('change.appTrainingSelectedUser', '#training-manage-page .users-check[data-user-id], .training-manage-page .users-check[data-user-id]', function () {
                    var userId = parseInt($(this).attr('data-user-id'), 10) || 0;
                    var checked = $(this).is(':checked');
                    syncSelectionState(userId, checked);
                });

            $(document)
                .off('click.appTrainingBulkAction')
                .on('click.appTrainingBulkAction', '#training-manage-page [data-bulk-action], .training-manage-page [data-bulk-action]', function (e) {
                    e.preventDefault();
                    if ($(this).prop('disabled')) return;
                    openBulkModal($(this).attr('data-bulk-action'), getSelectedIds());
                });

            $(document)
                .off('click.appTrainingAccessConfirm')
                .on('click.appTrainingAccessConfirm', '#trainingCourseAccessModal [data-access-modal-confirm]', function (e) {
                    e.preventDefault();
                    if (!pendingAction || !pendingIds.length) return;

                    if (pendingAction === 'assign') {
                        var $modal = $(modalEl);
                        executeBulk('assign', pendingIds, {
                            access_role: 'employee',
                            is_active: 1,
                            active_from: $modal.find('[data-access-active-from]').val() || '',
                            active_to: $modal.find('[data-access-active-to]').val() || ''
                        });
                    } else {
                        executeBulk('unassign', pendingIds, {});
                    }

                    accessModal.hide();
                });

            if (modalEl) {
                $(modalEl)
                    .off('hidden.bs.modal.appTrainingAccess')
                    .on('hidden.bs.modal.appTrainingAccess', function () {
                        pendingAction = '';
                        pendingIds = [];
                    });
            }

            var $first = $wrap.find('.course-filter__chip.is-active').first();
            if (!$first.length) {
                $first = $wrap.find('.course-filter__chip').first();
            }

            if ($first.length) {
                $first.trigger('click');
            } else {
                setRows([]);
                renderTable([]);
                renderCards([]);
            }
        },
    };
})();

AppTraining.init();

/* ===== practice tasks ===== */
(function () {
    'use strict';

    function closest(el, selector) {
        while (el && el.nodeType === 1) {
            if (el.matches && el.matches(selector)) {
                return el;
            }
            el = el.parentNode;
        }
        return null;
    }

    function initPracticeTabs(root) {
        if (root.__practiceTabsReady) {
            return;
        }
        root.__practiceTabsReady = true;

        var tabs = root.querySelectorAll('[data-practice-tab]');
        var panels = root.querySelectorAll('[data-practice-panel]');

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var target = tab.getAttribute('data-practice-tab');

                tabs.forEach(function (item) {
                    item.classList.toggle('is-active', item === tab);
                });

                panels.forEach(function (panel) {
                    panel.classList.toggle('is-active', panel.getAttribute('data-practice-panel') === target);
                });
            });
        });
    }

    function setFormMessage(form, text, type) {
        var box = form.querySelector('[data-practice-form-message]');
        if (!box) {
            return;
        }

        if (box.__practiceMessageTimer) {
            clearTimeout(box.__practiceMessageTimer);
            box.__practiceMessageTimer = null;
        }

        box.className = 'practice-form-message';
        box.textContent = '';

        if (!text) {
            box.style.display = 'none';
            return;
        }

        box.textContent = text;
        box.classList.add(type === 'error' ? 'is-error' : 'is-success');
        box.style.display = '';

        if (type !== 'error') {
            box.__practiceMessageTimer = setTimeout(function () {
                setFormMessage(form, '', '');
            }, 3500);
        }
    }

    function removeEmptyState(container) {
        if (!container) {
            return;
        }
        container.querySelectorAll('.practice-empty').forEach(function (empty) {
            empty.parentNode.removeChild(empty);
        });
    }

    function setStatus(root, statusText, statusClass) {
        var status = root ? root.querySelector('[data-practice-status]') : null;
        if (!status || !statusText) {
            return;
        }

        var keep = [];
        status.className.split(/\s+/).forEach(function (cls) {
            if (cls && cls.indexOf('practice-status--') !== 0) {
                keep.push(cls);
            }
        });

        if (statusClass) {
            keep.push(statusClass);
        }

        status.className = keep.join(' ');
        status.textContent = statusText;
    }

    function getConnector(form) {
        var local = form.getAttribute('data-practice-connector');
        if (local) {
            return local;
        }

        var root = closest(form, '[data-training-connector]');
        if (root) {
            local = root.getAttribute('data-training-connector');
            if (local) {
                return local;
            }
        }

        if (window.TrainingWebConfig && window.TrainingWebConfig.connectorUrl) {
            return window.TrainingWebConfig.connectorUrl;
        }

        return '/assets/components/training/web.connector.php';
    }

    function getContext(form) {
        var local = form.getAttribute('data-practice-context');
        if (local) {
            return local;
        }

        var root = closest(form, '[data-training-context]');
        if (root) {
            local = root.getAttribute('data-training-context');
            if (local) {
                return local;
            }
        }

        if (window.TrainingWebConfig && window.TrainingWebConfig.contextKey) {
            return window.TrainingWebConfig.contextKey;
        }

        return 'web';
    }

    function sendPracticeForm(form, submit, input, fileInput, preview, updateSubmit) {
        var root = closest(form, '[data-training-page="practice"]') || document;
        var messages = root.querySelector('[data-practice-messages]');
        var fd = new FormData(form);
        var connector = getConnector(form);
        var xhr = new XMLHttpRequest();

        fd.set('action', 'web/practice/submit');
        fd.set('ctx', getContext(form));

        form.classList.add('is-sending');
        setFormMessage(form, '', '');
        var oldSuccess = form.querySelector('.practice-form-success');
        if (oldSuccess && oldSuccess.parentNode) {
            oldSuccess.parentNode.removeChild(oldSuccess);
        }

        if (submit) {
            submit.disabled = true;
            submit.setAttribute('data-default-text', submit.getAttribute('data-default-text') || submit.textContent);
            submit.innerHTML = '<span>Отправка...</span>';
        }

        xhr.open('POST', connector, true);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

        xhr.onreadystatechange = function () {
            if (xhr.readyState !== 4) {
                return;
            }

            form.classList.remove('is-sending');

            var res = null;
            try {
                res = JSON.parse(xhr.responseText || '{}');
            } catch (e) {
                res = null;
            }

            if (!res || !res.success) {
                var message = res && res.message ? res.message : 'Не удалось отправить сообщение.';
                setFormMessage(form, message, 'error');
                if (submit) {
                    submit.innerHTML = '<span>Отправить</span>';
                }
                updateSubmit();
                return;
            }

            if (res.object && res.object.message_html && messages) {
                removeEmptyState(messages);
                var holder = document.createElement('div');
                holder.innerHTML = res.object.message_html;
                while (holder.firstChild) {
                    messages.appendChild(holder.firstChild);
                }
                messages.scrollTop = messages.scrollHeight;
            }

            if (res.object) {
                setStatus(root, res.object.status_text || '', res.object.status_class || '');
            }

            if (input) {
                input.value = '';
                input.style.height = 'auto';
            }
            if (fileInput) {
                fileInput.value = '';
            }
            if (preview) {
                preview.innerHTML = '';
            }

            setFormMessage(form, res.message || 'Сообщение отправлено.', 'success');

            if (submit) {
                submit.innerHTML = '<span>Отправить</span>';
            }
            updateSubmit();
        };

        xhr.onerror = function () {
            form.classList.remove('is-sending');
            setFormMessage(form, 'Ошибка соединения. Попробуйте ещё раз.', 'error');
            if (submit) {
                submit.innerHTML = '<span>Отправить</span>';
            }
            updateSubmit();
        };

        xhr.send(fd);
    }

    function initPracticeForm(form) {
        if (form.__practiceFormReady) {
            return;
        }
        form.__practiceFormReady = true;

        var input = form.querySelector('[data-practice-message]');
        var fileInput = form.querySelector('[data-practice-files]');
        var preview = form.querySelector('[data-practice-files-preview]');
        var submit = form.querySelector('[data-practice-submit]');
        var oldSuccess = form.querySelector('.practice-form-success');

        if (oldSuccess) {
            setTimeout(function () {
                if (oldSuccess.parentNode) {
                    oldSuccess.parentNode.removeChild(oldSuccess);
                }
            }, 3500);
        }

        function hasFiles() {
            return fileInput && fileInput.files && fileInput.files.length > 0;
        }

        function hasText() {
            return input && input.value.trim().length > 0;
        }

        function updateSubmit() {
            if (!submit) {
                return;
            }
            submit.disabled = form.classList.contains('is-sending') || !(hasText() || hasFiles());
        }

        function renderFiles() {
            if (!preview || !fileInput) {
                return;
            }

            preview.innerHTML = '';

            if (!fileInput.files || !fileInput.files.length) {
                updateSubmit();
                return;
            }

            Array.prototype.forEach.call(fileInput.files, function (file) {
                var chip = document.createElement('div');
                chip.className = 'practice-file-chip';

                var icon = document.createElement('span');
                icon.className = 'practice-file-chip__ico';
                icon.textContent = '📎';

                var name = document.createElement('span');
                name.className = 'practice-file-chip__name';
                name.textContent = file.name;

                chip.appendChild(icon);
                chip.appendChild(name);
                preview.appendChild(chip);
            });

            updateSubmit();
        }

        if (input) {
            input.addEventListener('input', function () {
                input.style.height = 'auto';
                input.style.height = Math.min(input.scrollHeight, 120) + 'px';
                setFormMessage(form, '', '');
                updateSubmit();
            });
        }

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                setFormMessage(form, '', '');
                renderFiles();
            });
        }

        form.addEventListener('submit', function (e) {
            if (!hasText() && !hasFiles()) {
                e.preventDefault();
                updateSubmit();
                return;
            }

            if (!window.FormData || !window.XMLHttpRequest) {
                if (submit) {
                    submit.disabled = true;
                    submit.innerHTML = '<span>Отправка...</span>';
                }
                return;
            }

            e.preventDefault();
            sendPracticeForm(form, submit, input, fileInput, preview, updateSubmit);
        });

        updateSubmit();
    }

    function initPracticeMore(button) {
        if (button.__practiceMoreReady) {
            return;
        }
        button.__practiceMoreReady = true;

        button.addEventListener('click', function () {
            var card = closest(button, '.practice-task-card');
            if (!card) {
                return;
            }

            var isOpen = card.classList.toggle('is-open');
            button.textContent = isOpen ? 'Свернуть' : 'Показать больше';
        });
    }

    function init() {
        document.querySelectorAll('[data-practice-tabs]').forEach(initPracticeTabs);
        document.querySelectorAll('[data-practice-form]').forEach(initPracticeForm);
        document.querySelectorAll('[data-practice-more]').forEach(initPracticeMore);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

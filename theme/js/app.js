var App = (function () {
    return {
        init: function () {
            let $this = this;
            this.menuCollapse();
            this.swiperProjects();
            this.mailing();
            this.rememberMeUser();
            this.formPhone();
            this.profileImage();
            this.profileForm();
            this.paginationQuestions();
            this.ajaxForms();
            this.formSelect();
            this.CountButton();
            this.ajaxLicense();
            this.orderHistory();
            this.History();
            this.svgImages();
            this.fileUpload();
            this.copyField();
            /*this.maskedInn();*/
            this.collapsedHistory();
            this.pdoAjaxLoad();
            this.formReg();
            this.modalMessage();
            this.checkPolotic();
            this.menuToggle();
        },
        menuToggle: function () {
            $(document)
                .off('click.appTrainingMenuToggle')
                .on('click.appTrainingMenuToggle', '.button-menu', function (e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var $menuItem = $(this).closest('.menu-toggle');
                    if (!$menuItem.length) return;
                /* training-sidebar-collapsed-toggle */
                var isDesktopCollapsed = window.matchMedia &&
                    window.matchMedia('(min-width: 991px)').matches &&
                    $('body').hasClass('menu-Collapsed');

                if (isDesktopCollapsed) {
                    // В свёрнутом виде стрелка всегда открывает «Обучение».
                    $menuItem.addClass('active');

                    var $burger = $('.aside.fixed-top .burger-toggle').first();

                    if ($burger.length) {
                        $burger.trigger('click');
                    }

                    return;
                }

                    $menuItem.toggleClass('active');
                });
        },
        checkPolotic: function() {
            var $chk = $('.form-reg input[name="politic"]');
            var $btn = $('.form-reg button[type="submit"]');
        
            function updateBtn(){
              var checked = $chk.prop('checked');
              $btn.prop('disabled', !checked)
                  .toggleClass('disabled', !checked);
            }
            $chk.on('change', updateBtn);
            updateBtn();
        },
        modalMessage: function() {
            let action = 'check-modal-seen';
            $.ajax({
                url: '/ajax/',
                data : {action : action},
                type: 'POST',
                success: function(response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }
                    if (!response.seen) {
                        var modal = document.getElementById('message');
                        if (modal) {
                            var bsModal = bootstrap.Modal.getInstance(modal);
                            if (!bsModal) {
                                bsModal = new bootstrap.Modal(modal);
                            }
                            bsModal.show();
                        }
                    }
                }
            });
            $(document).on('click','#message .btn-close', function() {
                let action = 'message';
                $.ajax({
                    url: '/ajax/',
                    type: 'POST',
                    data: {action : action, modal_seen: true },
                    success: function(response) {
                    },
                    error: function() {
                        console.log('Ошибка при отправке AJAX-запроса');
                    }
                });
            });
        },
        formReg: function () {
            $('#office-auth-register-inn_company').on('input', function() {
                var value = $(this).val().replace(/\D/g, '');
                
                if (value.length > 30) {
                    value = value.slice(0, 30);
                }
                
                $(this).val(value);
            });
            $('#office-auth-register-region').on('input', function() {
                var value = $(this).val().replace(/[^а-яА-ЯёЁ\s]/g, '');
                
                $(this).val(value);
            });
        },
        pdoAjaxLoad: function() {
            let $this = this;
            $(document).on('pdopage_load', function (e, config, response) {
                console.log(e, config, response);
                $this.svgImages();
            });
        },
        copyField: function() {
            $('.copy-field').click(function() {
                var input = $(this).closest('.d-flex').find('input');
                var tempTextarea = $('<textarea>');
                $('body').append(tempTextarea);
                tempTextarea.val(input.val()).select();
                document.execCommand('copy');
                tempTextarea.remove();
                miniShop2.Message.success('Скопировано')
            });
        },
        fileUpload: function () {
            $('.button-group:not(.multiple-files)').each(function() {
                const fileInput = $(this).find('.file-input');
                const uploadBtn = $(this).find('.upload-btn');
        
                uploadBtn.off('click').on('click', function(event) {
                    event.preventDefault();
                    fileInput.click();
                });
        
                fileInput.off('change').on('change', function() {
                    if (fileInput[0].files.length > 0) {
                        const fileName = fileInput[0].files[0].name;
                        uploadBtn.text(fileName);
                    } else {
                        uploadBtn.text('Загрузить файл');
                    }
                });
            });
            
            $('.button-group.multiple-files').each(function () {
                const container = $(this); // Текущий контейнер
                const fileInput = container.find('.file-input');
                const uploadBtn = container.find('.upload-btn');
                const thumbsContainer = container.find('.block_thumbs');
                const maxFiles = parseInt(container.find('input[name="responce_36_max_files"]').val(), 10) || 5;
                const allowedTypes = container.find('input[name="responce_36_type_files"]').val().split(',');
                const errorField = container.find('.error_uploads');
                
                uploadBtn.off('click').on('click', function (event) {
                    event.preventDefault();
                    fileInput.click();
                });
                
                fileInput.off('change').on('change', function () {
                    errorField.text('');
                    thumbsContainer.empty();
            
                    const files = Array.from(fileInput[0].files);
                    if (files.length > maxFiles) {
                        errorField.text(`Вы можете загрузить не более ${maxFiles} файлов.`);
                        return;
                    }
            
                    files.forEach((file, index) => {
                        const fileName = file.name;
                        const fileType = file.type.split('/')[1];
                        const fileSize = file.size / (1024 * 1024);
                        
                        if (!allowedTypes.includes(fileType)) {
                            errorField.text(`Недопустимый тип файла: ${fileName}`);
                            return;
                        }
                        
                        if (fileSize > 10) {
                            errorField.text(`Файл ${fileName} превышает максимальный размер 10 МБ.`);
                            return;
                        }
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const thumb = $(`
                                <div class="file-thumb" data-index="${index}">
                                    <img src="${e.target.result}" alt="${fileName}" class="thumb-img">
                                    <button type="button" class="btn-close remove-file" data-index="${index}"></button>
                                </div>
                            `);
                            thumbsContainer.append(thumb);
                        };
                        reader.readAsDataURL(file);
                    });
                });
                thumbsContainer.off('click').on('click', '.remove-file', function () {
                    const index = $(this).data('index');
                    const files = Array.from(fileInput[0].files);
                    files.splice(index, 1);
                    const dataTransfer = new DataTransfer();
                    files.forEach(file => dataTransfer.items.add(file));
                    fileInput[0].files = dataTransfer.files;
                    $(this).closest('.file-thumb').remove();
                });
            });
            
        },
        History: function () {
            $(document).on('change', 'select[name="company"], select[name="status"]', function() {
                var filter = {};
                $('.filter-history').find(':selected').each(function () {
                    filter[$(this).parent().attr('name')] = $(this).val();
                });
            
                $('.history-item').each(function() {
                    var $this = $(this);
                    var companyMatch = filter['company'] === 'all' || $this.data('company') == filter['company'];
                    var statusMatch = filter['status'] === 'all' || $this.data('status').split(',').includes(filter['status']);
            
                    if (companyMatch && statusMatch) {
                        $this.show();
                    } else {
                        $this.hide();
                    }
                });
            });
        },
        orderHistory: function () {
            var count = 0;
            $('.list_history_order form').each(function(key,index) {
                count = count + 1;
            });
            if (count > 5 ) {
                $(document).on('click', '.show-all', function(e) {
                    e.preventDefault();
                    $('.list_history_order').toggleClass('show');
                    if (!$(this).hasClass('show')) {
                        $(this).addClass('show');
                        $(this).find('span').text('Скрыть');
                    } else {
                        $(this).removeClass('show');
                        $(this).find('span').text('Показать всю историю заказов');
                    }
                    let target = $('.list_history_order');
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 300);
                });
            } else {
                $('.list_history_order').css('height', 'auto');
                $('.show-all').hide();
            }
        },
        CountButton: function () {
            var countButton = '.countButton';
            $(document)
                .on('click touchend', countButton, function (e) {
                    var container = $(this).closest('.card-cart-product__count'),
                    count = container.find('[name="count"]'),
                    num_count = count.val();
                    if (isNaN(num_count) === false) {
                        num_count = parseInt(num_count, 10);
                        switch ($(this).data('add')) {
                            case 'plus':
                                num_count = num_count + 1;
                                count.val(num_count);
                                break;
                            case 'minus':
                                if (num_count <= 1) return;
                                num_count = num_count - 1;
                                count.val(num_count);
                                break;
                        }
                    } else {
                        return false;
                    }
                    count.trigger('change');
                }).on('change keypress keyup', '.card-cart-product__count [name="count"]', function() {
                    if ($(this).val().match(/\D/)) {
                        this.value = $(this).val().replace(/\D/g,'');
                    }
                    if (parseInt($(this).val(), 10) < 1) {
                        this.value = 1;
                    }
                });
        },
        formSelect: function () {
            $(document).ready(function () {
                $('.form-software select.form-control').each((index, element) => {
                    if ($(element).val() === '') {
                        $(element).removeClass('filled')
                    } else {
                        $(element).addClass('filled')
                    }
                });
                miniShop2.Callbacks.add('Cart.add.response.success', 'cart_add_success', function (response) {
                    $('button[value="order/submit"]').removeClass('disabledd');
                });
            });
            var product_name = {};
            var result = [];
            $('.form-software select.form-control').change(function () {
                var action = 'selected', name = $(this).prop('name'), value = $(this).val();
                var currentIndex = $('.form-software select.form-control').index(this);
                var selectedText = $(this).find('option:selected').text();
                product_name[currentIndex] = selectedText;
                
                if (currentIndex == 3) {
                    action = 'license';
                    $.ajax({
                        type: 'POST',
                        url: 'ajax/',
                        data: {
                            "action" : action, "name" : name, "value" : value, "index" : currentIndex, "product_name" : product_name
                        },
                        dataType: 'json',
                        success: function(response) {
                            console.log(response);
                            if (response.success) {
                                $('.form-software [name="id"]').val(response.id);
                                $('.form-software [name="options[mid]"]').val(response.mid);
                                $('.form-software [name="options[pagetitle]"]').val(response.product_name);
                                $('#add_software').removeClass('disabledd');
                            }
                        },
                        error: function(xhr, status, error) {
                            miniShop2.Message.error('Произошла ошибка при выборе: ' + error);
                        }
                    });
                } else {
                    $.ajax({
                        type: 'POST',
                        url: 'ajax/',
                        data: {
                            "action" : action, "name" : name, "value" : value, "index" : currentIndex
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                $('#add_software').addClass('disabledd');
                                var temp = document.createElement('div');
                                temp.innerHTML = response.options;
                                var optionsArray = $(temp).find('option').map(function() {
                                    return $(this).val();
                                }).get();
                                var countOptions = optionsArray.length;
                                $('#' + response.id).html(response.options);
                                switch (response.id) {
                                    case 'ver':
                                        if (countOptions == 1) {
                                            $('#ver').addClass('filled').prop('disabled', true);
                                            $('#ver').html(response.options).trigger('change');
                                        } else {
                                            $('#ver').removeClass('filled').prop('disabled', false);
                                        }
                                        $('#modul').html('<option value=""></option>').removeClass('filled').prop('disabled', true);
                                        $('#lic').html('<option value=""></option>').removeClass('filled').prop('disabled', true);
                                        break;
                                    case 'modul':
                                        if (countOptions == 1) {
                                            $('#modul').addClass('filled').prop('disabled', true);
                                            $('#modul').html(response.options).trigger('change');
                                        } else {
                                            $('#modul').removeClass('filled').prop('disabled', false);
                                        }
                                        $('#lic').html('<option value=""></option>').removeClass('filled').prop('disabled', true);
                                        break;
                                    case 'lic':
                                        console.log(response.options);
                                        if (countOptions == 1) {
                                            $('#lic').addClass('filled').prop('disabled', true);
                                            
                                            $('#lic').html(response.options).trigger('change');
                                        } else {    
                                            $('#lic').removeClass('filled').prop('disabled', false);
                                        }
                                        break;
                                    default:
                                        break;
                                }
                            }
                        },
                        error: function(xhr, status, error) {
                            miniShop2.Message.error('Произошла ошибка при выборе: ' + error);
                        }
                    });
                }
                if ($(this).val() === '') {
                    $(this).removeClass('filled');
                } else {
                    $(this).addClass('filled');
                }
            });
            
        },
        ajaxLicense: function() {
            $(document).ready(function () {
                $(document).on('click', '.form-software [type="submit"]', function(e) {
                    var message = 'Не выбрана лицензия';
                    if ($('#lic').val() == '') {
                        miniShop2.Message.error('Произошла ошибка: ' + message);
                        return false;
                    } else {
                       
                    }
                });
            });
        },
        svgImages: function () {
            $('img.img-svg').each(function () {
                var $img = $(this);
                var imgClass = $img.attr('class');
                var imgURL = $img.attr('src');
                $.get(imgURL, function (data) {
                    var $svg = $(data).find('svg');
                    if (typeof imgClass !== 'undefined') {
                        $svg = $svg.attr('class', imgClass + ' replaced-svg');
                    }
                    $svg = $svg.removeAttr('xmlns:a');
                    if (!$svg.attr('viewBox') && $svg.attr('height') && $svg.attr('width')) {
                        $svg.attr('viewBox', '0 0 ' + $svg.attr('height') + ' ' + $svg.attr('width'))
                    }
                    $img.replaceWith($svg);
                }, 'xml');
            });
        },
        rememberMeUser: function () {
            $('input[name="remember"]').change(function() {
                var check = $(this).is(':checked');
                console.log(check);
                $.ajax({
                    type: 'POST',
                    url: 'ajax/',
                    data: {"action" : "rememberMeUser", "check" : check},
                    dataType: 'json',
                    success: function(response) {
                    },
                    error: function(xhr, status, error) {
                        miniShop2.Message.error('Произошла ошибка при сохранении: ' + error);
                    }
                });
            });
        },
        mailing: function () {
            $(document).on('click', '.btn-add-mailing', function() {
                var $buttonMailing = '<div class="mailing-item"><div class="mailing d-flex flex-column p-4"><input type="hidden" name="action" value="subscription"><div class="form-group position-relative"><label class="form-title d-block form-label mute">Укажите Вашу почту для рассылок</label><input class="form-control" type="email" name="emails[]" placeholder="email@email.ru" value=""><button type="button" class="delete-mailing"><img src="theme/images/minus.svg" class="img-svg"></button></div><hr class="opacity-100"><div class="form-title d-block mute mb-3">Выберите категории рассылок</div><div class="d-flex flex-column"><label class="custom-checkbox-switch"><input type="hidden" name="webinar[]" value=""><input type="checkbox" value="1"><span>Вебинары</span></label><hr class="opacity-100"><label class="custom-checkbox-switch"><input type="hidden" name="partner_events[]" value=""><input type="checkbox" value="1"><span>Партнёрские мероприятия</span></label><hr class="opacity-100"><label class="custom-checkbox-switch"><input type="hidden" name="news[]" value=""><input type="checkbox" value="1"><span>Новости</span></label><hr class="opacity-100"><label class="custom-checkbox-switch"><input type="hidden" name="software_updates[]" value=""><input type="checkbox" value="1"><span>Обновления ПО</span></label></div></div>';
                $('.block-mailing-item').append($buttonMailing);
                $('.nav-item-link button').addClass('disabledd');
                $('button[type="submit"]').addClass('disabledd');
                
            });
            $(document).on('click', '.delete-mailing', function() {
                $(this).parents('.mailing-item').remove();
                checkMailingItems();
                checkDuplicateEmails();
            });
            $(document).on('click', '.mailing-item input[type="checkbox"]', function() {
                if ($(this).is(':checked')) {
                    $(this).prev().val($(this).val());
                } else {
                    $(this).prev().val('');
                }
            });
            $(document).on('change', '#personal_data', function() {
                if ($(this).is(':checked')) {
                    $(this).prev().val($(this).val());
                } else {
                    $(this).prev().val('');
                }
                checkPolitics();
            });
            $(document).on('input', '.mailing-item [type="email"]', function() {
                var email = $(this).val();
                checkDuplicateEmails();
                if (!validateEmail(email)) {
                    $(this).addClass('is-invalid');
                    $('.nav-item-link button').addClass('disabledd');
                    $('button[type="submit"]').addClass('disabledd');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                    $('.nav-item-link button').removeClass('disabledd');
                    $('button[type="submit"]').removeClass('disabledd');
                    checkMailingItems();
                }
                
            });
            $(document).on('click','.mailing-item input[type="checkbox"]', function() {
                checkPolitics();
                checkMailingItems();
                checkDuplicateEmails();
            });
            $(document).on('click', '[type="submit"]', function() {
                $('.mailing-item [type="email"]').each(function() {
                    $(this).prop('readonly', true);
                });
                
            });
            
            function validateEmail(email) {
                var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return re.test(email);
            }
            function checkMailingItems() {
                $('.mailing-item').each(function() {
                    var anyChecked = $(this).find('input[type="checkbox"]:checked').length > 0;
                    if (!anyChecked) {
                        miniShop2.Message.error('В одном из блоков не выбран ни один чекбокс!');
                        $('button[type="submit"]').addClass('disabledd');
                        $('.nav-item-link button').addClass('disabledd')
                        return false; // Прекращаем выполнение если найден блок без включенного чекбокса
                    } else {
                        $('.nav-item-link button').removeClass('disabledd')
                        $('button[type="submit"]').removeClass('disabledd');
                    }
                });
            }
            function checkDuplicateEmails() {
                var emails = [];
                var hasDuplicate = false;
                $('.mailing-item [type="email"]').each(function() {
                    var email = $(this).val();
                    if (email) {
                        if (emails.includes(email)) {
                            $(this).addClass('is-invalid').removeClass('is-valid');
                            $('.nav-item-link button').addClass('disabledd')
                            $('button[type="submit"]').addClass('disabledd');
                            miniShop2.Message.error('В одном из блоков повторяется адрес почты!');
                            hasDuplicate = true;
                        } else {
                            emails.push(email);
                            $(this).removeClass('is-invalid').addClass('is-valid');
                            $('.nav-item-link button').removeClass('disabledd')
                            $('button[type="submit"]').removeClass('disabledd');
                        }
                    }
                });
                return !hasDuplicate;
            }
            function checkPolitics() {
                if ($('[name="personal_data"]').val() == 'true') {
                    $('.nav-item-link button').removeClass('disabledd');
                    $('button[type="submit"]').removeClass('disabledd');
                } else {
                    miniShop2.Message.error('Нужно дать согласие на обработку персональных данных');
                    $('.nav-item-link button').addClass('disabledd');
                    $('button[type="submit"]').addClass('disabledd');
                }
            }
            // $(document).on('click', '#mailing-tab', function() {
            //     $('.nav-item-link button').addClass('disabledd');
            // });
            // $('.mailing [type="submit"]').click(function(event) {
            //     event.preventDefault();
            //     if ($('[name="personal_data"]').prop('checked')) {
            //         var formData = $('.mailing').serialize();
            //         $.ajax({
            //             type: 'POST',
            //             url: 'ajax/',
            //             data: formData,
            //             dataType: 'json',
            //             success: function(response) {
            //                 if (response.success) {
            //                     miniShop2.Message.success('Ваши данные успешно сохранены');
            //                 } else {
            //                     miniShop2.Message.error(response.message);
            //                 }
            //             },
            //             error: function(xhr, status, error) {
            //                 miniShop2.Message.error('Произошла ошибка при сохранении: ' + error);
            //             }
            //         });
            //     } else {
            //         miniShop2.Message.error('Вы должны согласиться на обработку персональных данных.');
            //     }
            // });
        },
        menuCollapse: function () {
            var self = this;
            var mobileQuery = '(max-width: 992px)';

            function isMobileMenuMode() {
                if (window.matchMedia) {
                    return window.matchMedia(mobileQuery).matches;
                }
                return $(window).width() <= 992;
            }

            function closeMobileMenu() {
                $('.burger-toggle').removeClass('open');
                $('.mobile-menu').removeClass('open');
                $('body').removeClass('mobile-menu-open');
            }

            function toggleMobileMenu($button) {
                var willOpen = !$button.hasClass('open');
                $button.toggleClass('open', willOpen);
                $('.mobile-menu').toggleClass('open', willOpen);
                $('body').toggleClass('mobile-menu-open', willOpen);
            }

            $(document)
                .off('click.appMenuCollapseBurger')
                .on('click.appMenuCollapseBurger', '.burger-toggle', function (e) {
                    e.preventDefault();

                    if (isMobileMenuMode()) {
                        toggleMobileMenu($(this));
                        return;
                    }

                    var body = $('body');
                    var collapsed = body.hasClass('menu-Collapsed');

                    $.ajax({
                        type: 'POST',
                        url: 'ajax/',
                        data: {"action" : "collapsedmenu", "collapsed" : collapsed},
                        dataType: 'json',
                        success: function(response) {
                            if (response.collapsed == 1) {
                                body.addClass('menu-Collapsed').removeClass('show-menu');
                                $('.aside .nav').removeClass('pe-1');
                            } else {
                                body.removeClass('menu-Collapsed');
                                setTimeout(function() {
                                    body.addClass('show-menu');
                                    $('.aside .nav').addClass('pe-1');
                                }, 300);
                            }
                        },
                        error: function(xhr, status, error) {
                            miniShop2.Message.error('Произошла ошибка при сохранении: ' + error);
                        }
                    });
                });

            $(document)
                .off('click.appMobileMenuLinkClose')
                .on('click.appMobileMenuLinkClose', '.mobile-menu .nav-link[href]:not([href="#"]), .mobile-menu .sumbenu a[href]:not([href="#"])', function () {
                    if (isMobileMenuMode()) {
                        closeMobileMenu();
                    }
                });

            $(window)
                .off('resize.appMenuCollapseMode')
                .on('resize.appMenuCollapseMode', function () {
                    if (!isMobileMenuMode()) {
                        closeMobileMenu();
                    }
                });

            if ($(document).width() > 992) {
                $(document).ready(function() {
                    if ($('body').hasClass('menu-Collapsed')) {
                        $('.aside .nav').removeClass('pe-1');
                    }
                });
            }
        },
        swiperProjects: function () {
            $('.projects-slider .swiper-container').each((index, element) => {

                let pagination = $(element).parents('.projects-user').find('.swiper-pagination')[0];
                let prev = $(element).parents('.projects-user').find('.swiper-prev')[0];
                let next = $(element).parents('.projects-user').find('.swiper-next')[0];
                
                console.log(pagination);
                
                new Swiper(element, {
                    loop: false,
                    watchOverflow: true,
                    spaceBetween: 0,
                    mousewheel: {
                        forceToAxis: true,
                        invert: true
                    },
                    slidesPerView: 1,
                    pagination: {
                        el: pagination,
                        type: 'bullets',
                    },
                    navigation: {
                        nextEl: next,
                        prevEl: prev,
                    },
                    breakpoints: {
                        991: {
                            slidesPerView: 2
                        },
                        1200: {
                            slidesPerView: 4
                        }
                    }
                });
            });
        },
        formPhone: function () {
            if ($('.form-phone')[0]) {
                $.mask.definitions['9'] = '';
                $.mask.definitions['d'] = '[0-9]';

                var input = document.querySelectorAll(".form-phone");

                for (let i = 0; i < input.length; i++) {

                    let initialCountry = false;

                    if ($(input[i]).data('value')) {
                        $(input[i]).val($(input[i]).data('value'));
                    } else {
                        initialCountry = 'ru';
                        $(input[i]).mask("+7 ddd ddd-dd-dd");
                        $.fn.setCursorPosition = function (pos) {
                            if ($(this).get(0).setSelectionRange) {
                                $(this).get(0).setSelectionRange(pos, pos);
                            } else if ($(this).get(0).createTextRange) {
                                var range = $(this).get(0).createTextRange();
                                range.collapse(true);
                                range.moveEnd('character', pos);
                                range.moveStart('character', pos);
                                range.select();
                            }
                        };
                        $(input[i]).click(function () {
                            if ($(this).val().indexOf('_') !== -1) {
                                $(this).setCursorPosition(3);  // set position number
                            }
                        });
                        $(input[i]).on('keyup', function () {
                            if ($(this).val().indexOf('_') !== -1) {
                                $(this).setCursorPosition($(this).val().indexOf('_'));
                            }
                        });
                    }

                    intlTelInput(input[i], {
                        placeholderNumberType: "MOBILE",
                        initialCountry: initialCountry,
                        onlyCountries: ['ru', 'az', 'am', 'by', 'ge', 'kz', 'kg', 'md', 'tj', 'tm', 'uz', 'ua', 'lt', 'lv', 'ee', 'de', 'pl', 'cz', 'sk'],
                        localizedCountries: {
                            'ru': 'Россия',
                            'az': 'Азербайджан',
                            'am': 'Армения',
                            'by': 'Белоруссия',
                            'ge': 'Грузия',
                            'kz': 'Казахстан',
                            'kg': 'Киргизия',
                            'md': 'Молдавия',
                            'tj': 'Таджикистан',
                            'tm': 'Туркмения',
                            'uz': 'Узбекистан',
                            'ua': 'Украина',
                            'lt': 'Литва',
                            'lv': 'Латвия',
                            'ee': 'Эстония',
                            'de': 'Германия',
                            'pl': 'Польша',
                            'cz': 'Чехия',
                            'sk': 'Словакия'
                        },
                        autoHideDialCode: true,
                        separateDialCode: true,
                        utilsScript: "/theme/vendor/intl-tel-input/js/utils.js",
                        customPlaceholder: function (selectedCountryPlaceholder, selectedCountryData) {

                            if ($(input[i]).data('value')) {
                                $(input[i]).val($(input[i]).data('value'));
                                $(input[i]).data('value', '');
                            }

                            return '+' + selectedCountryData.dialCode + ' ' + selectedCountryPlaceholder.replace(/[0-9]/g, '_');
                        }
                    });
                }

                $('.form-phone').on("close:countrydropdown", function () {
                    $(this).val('');
                    $(this).mask($(this).attr('placeholder').replace(/[_]/g, 'd'));
                });
            }
        },
        profileImage: function () {
            $(document).on('click', '#office-user-photo-remove', function () {
                var image = $('.avatar img').data('gravatar');
                $('.avatar img').attr('src', image);
            });
        },
        profileForm: function () {
            $(document).on('change', '.profile-form select', function() {
                let company = $(this).val();
                let inn = $(this).find('option:selected').data('inn');
                console.log(company);
                console.log(inn);
                $('#field_inn').val(inn);
                $('.field_inn').text(inn);
                $('.company_profile').text(company);
            });
        },
        paginationQuestions: function() {
            var currentPage = 1;
        
            function getTotalPages() {
                // считаем именно количество <li>, а не текст
                var count = $('.nav-questions ul li').length;
                console.log('totalPages:', count);
                return count;
            }
        
            function validateFields() {
                var isFilled = true;
                $('.block-questions.active input[type="text"]:not(.your_version), .block-questions.active input[type="hidden"]').each(function() {
                    if ($(this).val() === '') {
                        isFilled = false;
                        $(this).addClass('is-invalid');
                        $(this).parents('.selected_item').find('.form-label').addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                        $(this).parents('.selected_item').find('.form-label').removeClass('is-invalid');
                    }
                });
                return isFilled;
            }
        
            function updateButtons() {
                var totalPages = getTotalPages();
                
                if (totalPages <= 1) {
                    $('.nav-questions').hide(); 
                    $('.btn-back').hide();
                    $('.btn-next').hide();
                    $('.btn-submit').show();
                    return;
                }
        
                // обычная логика, если страниц больше одной
                if (currentPage === 1) {
                    $('.btn-back').hide();
                } else {
                    $('.btn-back').show();
                }
        
                if (currentPage === totalPages) {
                    $('.btn-next').hide();
                    $('.btn-submit').show();
                } else {
                    $('.btn-next').show();
                    $('.btn-submit').hide();
                }
            }
        
            updateButtons();
            
            $('.btn-back').click(function(e) {
                e.preventDefault();
                currentPage--;
                $('.block-questions').removeClass('active');
                $('.nav-questions li').removeClass('active');
                $('.block-questions:nth-child(' + currentPage + ')').addClass('active');
                $('.nav-questions li:nth-child(' + currentPage + ')').addClass('active');
                let target = $('#keises');
                $('html, body').animate({
                    scrollTop: target.offset().top - 100
                }, 1000);
                updateButtons();
            });
        
            $('.btn-next').click(function(e) {
                e.preventDefault();
                if (validateFields()) {
                    currentPage++;
                    $('.block-questions').removeClass('active');
                    $('.nav-questions li').removeClass('active');
                    $('.block-questions:nth-child(' + currentPage + ')').addClass('active');
                    $('.nav-questions li:nth-child(' + currentPage + ')').addClass('active');
                    let target = $('#keises');
                    $('html, body').animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                    updateButtons();
                }
            });
        
            $('.btn-submit').click(function() {
                if (validateFields()) {
                    // отправка формы
                }
            });
        
            $(document).on('click', '.option', function() {
                if ($(this).hasClass('selected')) {
                    $(this).removeClass('selected');
                    $(this).parents('.selected_item').find('.value_item').val('');
                } else {
                    $(this).parents('.selected_item').find('.option').removeClass('selected');
                    $(this).addClass('selected');
                    $(this).parents('.selected_item').find('.your_version').val('');
                    $(this).parents('.selected_item').find('.value_item').val($(this).text());
                }
            });
        
            $('.your_version').on('input', function() {
                $(this).parents('.selected_item').find('.option').removeClass('selected');
                $(this).parents('.selected_item').find('.value_item').val($(this).val());
            });
        },
        ajaxForms: function() {
            if ($('#callback').length && $('#thenks').length) {
                const callback = new bootstrap.Modal('#callback', {
                    keyboard: false
                })
                const thenks = new bootstrap.Modal('#thenks', {
                    keyboard: false
                })
                $(document).on('af_complete', function (event, response) {
                    var form = response.form;
                    if (response.success === true) {
                        switch (form.attr('id')) {
                            case "callback-form":
                                console.log(response);
                                callback.hide();
                                thenks.show();
                            break;
                            case "keises":
                                $('#keises .btn').hide();
                                $('.survey-user').html('<div class="d-flex block-questions active item-questions p-4 h-100 message justify-content-center align-items-center">' + message + '</div>');
                                setTimeout(function() {
                                    location.reload(true);
                                }, 5000);
                                let target = $('#keises');
                                $('html, body').animate({
                                    scrollTop: target.offset().top - 100
                                }, 1000);
                            break;
                            default:
                        }
                    } else {
                        miniShop2.Message.error('Произошла ошибка при отправке: ' + response.message);
                        console.log(response.message);
                    }
                });
            }
        },
        /*maskedInn: function () {
            var $numberInput = $('#office-auth-register-inn_company');
            
            console.log($numberInput);
            if ($numberInput.length) {
                $numberInput.mask('0000000000#', {
                    translation: {
                        '0': { pattern: /\d/ },
                        '#': { pattern: /\d/, optional: true }
                    },
                    onKeyPress: function(val, e, field, options) {
                        if (val.length >= 11) {
                            $(field).mask('00000000000#', options);
                        } else if (val.length >= 10) {
                            $(field).mask('0000000000#', options);
                        } else {
                            $(field).mask('0000000000#', options);
                        }
                    }
                });
            }
        },*/
        collapsedHistory: function () {
            $(document).on('click', '.header-history', function () {
                $(this).toggleClass('collapse');
                $(this).next().toggleClass('collapse');
            });
        },
        getCookie: function(name) {
            var matches = document.cookie.match(new RegExp(
                "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
            ));
            return matches ? decodeURIComponent(matches[1]) : undefined;
        },
        setCookie: function(name, value, options) {
            options = options || {};

            var expires = options.expires;

            if (typeof expires == "number" && expires) {
                var d = new Date();
                d.setTime(d.getTime() + expires * 1000);
                expires = options.expires = d;
            }
            if (expires && expires.toUTCString) {
                options.expires = expires.toUTCString();
            }

            value = encodeURIComponent(value);

            var updatedCookie = name + "=" + value;

            for (var propName in options) {
                updatedCookie += "; " + propName;
                var propValue = options[propName];
                if (propValue !== true) {
                    updatedCookie += "=" + propValue;
                }
            }

            document.cookie = updatedCookie;
        },
        deleteCookie: function(name) {
            App.setCookie(name, "", {
                expires: -1
            });
        }
    }   
})();    
App.init();
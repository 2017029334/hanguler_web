jQuery(function($) {
    $(document.body).on('bookly_packages.schedule_dialog', {},
        function (event, package_id, callback, deprecated, use_wp_timezone) {
            if (use_wp_timezone === undefined) {
                use_wp_timezone = true;
            }
            var $modal          = $('#bookly-package-schedule-dialog').off(),
                $form           = $('form', $modal).off(),
                $template       = $('#schedule_entry_template'),
                $schedule       = $('#bookly-package-schedule-body', $modal),
                $modal_footer   = $('.modal-footer', $modal),
                $ignore_expired = $('[name="bookly-ignore-expired"]', $modal),
                $staff_template = $('.bookly-js-schedule-edit-staff select', $template),
                data            = {},
                timeZone        = use_wp_timezone === false && typeof Intl === 'object' ? Intl.DateTimeFormat().resolvedOptions().timeZone : undefined,
                timeZoneOffset  = use_wp_timezone === false ? new Date().getTimezoneOffset() : undefined
            ;

            function modalLoading(state) {
                $('.modal-body .bookly-loading', $modal).toggle(state);
                $('.modal-body .bookly-js-modal-body', $modal).toggle(!state);
                $modal_footer.toggle(!state);
            }

            function initPagination(appointments_count) {
                var $pagination             = $('.pagination', $modal),
                    $prev_page              = $('li', $pagination).first(),
                    $next_page              = $('li', $pagination).last(),
                    appointments_per_page   = 10,
                    pages                   = Math.ceil(appointments_count / appointments_per_page);
                if (appointments_count > appointments_per_page) {
                    $('li[data-type="page"]', $pagination).remove();
                    for (i = 1; i <= pages; i++) {
                        $next_page.before('<li data-page="' + i + '" data-type="page" class="page-item"><a href="#" class="page-link">' + i + '</a></li>');
                    }
                    $pagination.on('click', 'li:not(.disabled)', function (e) {
                        e.preventDefault();
                        var page  = $(this).data('page'),
                            $page = $('li[data-type="page"][data-page="' + page + '"]');
                        $('li[data-type="page"]', $pagination).removeClass('active');
                        $page.addClass('active');
                        $prev_page.data('page', page - 1);
                        $next_page.data('page', page + 1);
                        if ((page <= 1 && !$prev_page.hasClass('disabled')) || (page > 1 && $prev_page.hasClass('disabled'))) {
                            $prev_page.toggleClass('disabled');
                        }
                        if ((page >= pages && !$next_page.hasClass('disabled')) || (page < pages && $next_page.hasClass('disabled'))) {
                            $next_page.toggleClass('disabled');
                        }
                        $('.list-group-item', $schedule).each(function (index) {
                            if (Math.ceil((index + 1) / appointments_per_page) == page) {
                                $(this).show();
                            } else {
                                $(this).hide();
                            }
                        });
                    });
                    $('li[data-type="page"][data-page="1"]').click();
                    $pagination.show();
                } else {
                    $pagination.hide();
                }
            }

            function updateTimeSelect($row) {
                var dateTime     = moment($('.bookly-js-schedule-edit-date input', $row).val(), BooklyL10nPackageScheduleDialog.datePicker.format),
                    current_slot = $('.bookly-js-schedule-edit-time select', $row).val(),
                    staff_id     = $('.bookly-js-schedule-edit-staff select', $row).val(),
                    exclude      = []
                ;
                $('.bookly-js-error-alert .bookly-js-error').hide();
                $('.bookly-js-error-alert').hide();
                $('.bookly-js-schedule-entry .bookly-js-schedule-edit-time select', $modal).each(function () {
                    var slot = $(this).val();
                    if (slot && slot != current_slot) {
                        exclude.push(slot);
                    }
                });
                $.ajax({
                    url : BooklyL10nPackageScheduleDialog.ajaxurl,
                    type: 'POST',
                    data: {
                        action          : 'bookly_packages_get_day_schedule',
                        csrf_token      : BooklyL10nPackageScheduleDialog.csrf_token,
                        staff_id        : staff_id,
                        service_id      : data.package.service_id,
                        location_id     : data.package.location_id,
                        date            : dateTime.format('DD-MM-YYYY'),
                        exclude         : exclude,
                        time_zone       : timeZone,
                        time_zone_offset: timeZoneOffset
                    },
                    dataType: 'json',
                    success: function (response) {
                        var current_time = $('.bookly-js-schedule-time', $row).text(),
                            $time        = $('<select class="form-control custom-select"/>'),
                            options      = []
                        ;
                        if(response.data.length) {
                            options = response.data[0].options;
                            $.each(options, function (index, option) {
                                var $option = $('<option/>');
                                $option.text(option.title).val(option.value);
                                if (option.disabled) {
                                    $option.attr('disabled', 'disabled');
                                }
                                $time.append($option);
                                if (option.title == current_time) {
                                    $time.val(option.value);
                                }
                            });
                            $('.bookly-js-schedule-apply-btn', $row).show();
                        } else {
                            $('.bookly-js-schedule-no-slot-error').show();
                            $('.bookly-js-error-alert').show();
                        }
                        $('.bookly-js-schedule-edit-time', $row).html($time);
                    }
                });
            }
            $modal.on('show.bs.modal', function (event) {
                modalLoading(true);
                $ignore_expired.prop('checked', false);
                $('.bookly-js-error-alert', $modal).hide();
                $('.bookly-js-warning-alert', $modal).hide();
                $.ajax({
                    url : BooklyL10nPackageScheduleDialog.ajaxurl,
                    type: 'POST',
                    data: {
                        action         : 'bookly_packages_get_package_appointments',
                        package_id     : package_id,
                        csrf_token     : BooklyL10nPackageScheduleDialog.csrf_token,
                        use_wp_timezone: use_wp_timezone === false ? 0 : 1
                    },
                    dataType: 'json',
                    success: function (response) {
                        data = response;
                        $schedule.html('');
                        $staff_template.html('');
                        if (response.staff.length) {
                            $staff_template.removeClass('collapse');
                            response.staff.forEach(function(staff, index){
                                $staff_template.append('<option value="' + staff.id + '">' + staff.full_name + '</option>')
                            });
                        }
                        $('.bookly-js-package-name', $modal).text(response.package.title);
                        response.appointments.forEach(function(appointment, index){
                            $schedule.append(
                                $template.clone().show().html()
                                    .replace(/{{id}}/g, appointment.id)
                                    .replace(/{{number}}/g, index + 1)
                                    .replace(/{{date}}/g, moment(appointment.start_date).format(BooklyL10nPackageScheduleDialog.mjsDateFormat))
                                    .replace(/{{time}}/g, moment(appointment.start_date).format(BooklyL10nPackageScheduleDialog.mjsTimeFormat))
                                    .replace(/{{staff}}/g, appointment.staff_full_name)
                            );
                        });
                        $('.bookly-js-schedule-edit-btn', $schedule).remove();
                        for (i = response.appointments.length; i < response.package.package_size; i++) {
                            $schedule.append(
                                $template.clone().show().html()
                                    .replace(/{{id}}/g, 0)
                                    .replace(/{{number}}/g, i + 1)
                                    .replace(/{{date}}/g, '')
                                    .replace(/{{time}}/g, '')
                                    .replace(/{{staff}}/g, '')
                            );
                        }
                        $('.bookly-js-schedule-edit-date input', $modal).daterangepicker({
                            parentEl        : '#bookly-package-schedule-dialog',
                            singleDatePicker: true,
                            showDropdowns   : true,
                            autoUpdateInput : true,
                            locale          : BooklyL10nPackageScheduleDialog.datePicker
                        });
                        initPagination(response.package.package_size);
                        $modal.on('click', '.bookly-js-schedule-edit-btn', function (e) {
                            e.preventDefault();
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            $('.bookly-js-schedule-view', $row).hide();
                            $('.bookly-js-schedule-edit', $row).show();
                            $('.bookly-js-schedule-apply-btn', $row).hide();
                            if (data.package.staff_id != null) {
                                $('.bookly-js-schedule-edit-staff', $row).hide();
                            }
                            $('#bookly-save', $modal_footer).prop('disabled', true);
                            $row.data('cur_date', $('.bookly-js-schedule-edit-date input', $row).val());
                            $row.data('cur_time', $('.bookly-js-schedule-edit-time select', $row).clone());
                            $row.data('cur_staff', $('.bookly-js-schedule-edit-staff select', $row).val());
                            if ($('.bookly-js-schedule-edit-date input', $row).val()) {
                                updateTimeSelect($row);
                            }
                        }).on('click', '.bookly-js-schedule-apply-btn', function (e) {
                            e.preventDefault();
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            $('.bookly-js-schedule-date', $row).text(moment($('.bookly-js-schedule-edit-date input', $row).val(), BooklyL10nPackageScheduleDialog.datePicker.format).format(BooklyL10nPackageScheduleDialog.mjsDateFormat));
                            $('.bookly-js-schedule-time', $row).text($('.bookly-js-schedule-edit-time select option:selected', $row).text());
                            $('.bookly-js-schedule-staff', $row).text($('.bookly-js-schedule-edit-staff select option:selected', $row).text());
                            $('.bookly-js-schedule-view', $row).show();
                            $('.bookly-js-schedule-edit', $row).hide();
                            $('#bookly-save', $modal_footer).prop('disabled', $('.bookly-js-schedule-edit:visible', $modal).length != 0);
                        }).on('click', '.bookly-js-schedule-cancel-btn', function (e) {
                            e.preventDefault();
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            $('.bookly-js-schedule-edit-date input', $row).val($row.data('cur_date'));
                            $('.bookly-js-schedule-edit-time select', $row).html($row.data('cur_time'));
                            $('.bookly-js-schedule-edit-staff select', $row).val($row.data('cur_staff'));
                            $('.bookly-js-schedule-view', $row).show();
                            $('.bookly-js-schedule-edit', $row).hide();
                            $('#bookly-save', $modal_footer).prop('disabled', $('.bookly-js-schedule-edit:visible', $modal).length != 0);
                        }).on('click', '.bookly-js-schedule-clear-btn', function (e) {
                            e.preventDefault();
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            $row.data('deleted', true);
                            $row.data('cur_date', $('.bookly-js-schedule-date', $row).text());
                            $row.data('cur_time', $('.bookly-js-schedule-time', $row).text());
                            $('.bookly-js-schedule-date', $row).text('');
                            $('.bookly-js-schedule-edit-date input', $row).val('');
                            $('.bookly-js-schedule-time', $row).text('');
                            $('.bookly-js-schedule-edit-time select', $row).val(null);
                            $('.bookly-js-schedule-staff', $row).text('');
                        }).on('change', '.bookly-js-schedule-edit-date input', function () {
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            updateTimeSelect($row);
                        }).on('change', '.bookly-js-schedule-edit-staff select', function () {
                            var $row = $(this).closest('.bookly-js-schedule-entry');
                            $('.bookly-js-schedule-edit-date input', $row).val('');
                            $('.bookly-js-schedule-edit-time select', $row).html('');
                            $('.bookly-js-schedule-apply-btn', $row).hide();
                        });
                        $form.on('submit', function(){
                            modalLoading(true);
                            var schedule = [];
                            $('.bookly-js-schedule-entry', $modal).each(function () {
                                var $staff =  $(this).find('.bookly-js-schedule-edit-staff select');
                                var $slot =  $(this).find('.bookly-js-schedule-edit-time select');
                                if ($slot.val() && $staff.val()) {
                                    schedule.push({staff: $staff.val(), slot: $slot.val()});
                                }
                            });
                            var deleted = [];
                            $('.bookly-js-schedule-entry', $modal).each(function () {
                                if ($(this).data('id') != 0 && $(this).data('deleted') === true) {
                                    deleted.push($(this).data('id'));
                                }
                            });
                            $.ajax({
                                url     : BooklyL10nPackageScheduleDialog.ajaxurl,
                                type    : 'POST',
                                data: {
                                    action          : 'bookly_packages_save_schedule',
                                    csrf_token      : BooklyL10nPackageScheduleDialog.csrf_token,
                                    package_id      : package_id,
                                    schedule        : schedule,
                                    deleted         : deleted,
                                    ignore_expired  : $ignore_expired.prop('checked'),
                                    notification    : $('#bookly-packages-schedule-notification', $modal).prop('checked') ? 'yes' : 'no',
                                    time_zone       : timeZone,
                                    time_zone_offset: timeZoneOffset
                                },
                                dataType: 'json',
                                success: function (response) {
                                    if (response.success) {
                                        // Save successful.
                                        if (callback) {
                                            // Call callback.
                                            callback(deleted);
                                        }
                                        $modal.booklyModal('hide');
                                        if (response.queue && response.queue.length) {
                                            $(document.body).trigger('bookly.queue_dialog', [response.queue]);
                                        }
                                    } else {
                                        // Save with errors.
                                        $('.bookly-js-error', $modal).hide();
                                        $('.bookly-js-schedule-entry .bookly-js-schedule-view', $modal).removeClass('text-success').removeClass('text-danger');
                                        if (response.errors != [] || response.warnings != []) {
                                            var index = 0;
                                            $('.bookly-js-schedule-entry .bookly-js-schedule-edit-time select', $modal).each(function () {
                                                if ($(this).val()) {
                                                    if ($.inArray(index, response.warning_slots) != -1) {
                                                        $(this).closest('.bookly-js-schedule-entry').find('.bookly-js-schedule-view').addClass('text-success');
                                                    }
                                                    if ($.inArray(index, response.error_slots) != -1) {
                                                        $(this).closest('.bookly-js-schedule-entry').find('.bookly-js-schedule-view').addClass('text-danger');
                                                    }
                                                    index++;
                                                }
                                            });
                                            $('.bookly-js-schedule-entry', $modal).each(function () {
                                                var $row = $(this);
                                                if ($row.data('id') && $.inArray($row.data('id').toString(), response.not_deleted) != -1) {
                                                    $row.addClass('text-danger');
                                                    $row.data('deleted', false);
                                                    $('.bookly-js-schedule-date', $row).text($row.data('cur_date'));
                                                    $('.bookly-js-schedule-time', $row).text($row.data('cur_time'));
                                                }
                                            });
                                            if (response.warnings.expired) {
                                                $('.bookly-js-expired-warning-alert', $modal).show();
                                            }
                                            if (response.errors.length != 0) {
                                                $('.bookly-js-error-alert', $modal).show();
                                            }
                                            if (response.errors.expired) {
                                                $('.bookly-js-expired-error-alert', $modal).show();
                                            }
                                            if (response.errors.occupied) {
                                                $('.bookly-js-occupied-alert', $modal).show();
                                            }
                                            if (response.errors.time_prior_booking) {
                                                $('.bookly-js-time-prior-booking-alert', $modal).show();
                                            }
                                            if (response.errors.time_prior_cancel) {
                                                $('.bookly-js-time-prior-cancel-alert', $modal).show();
                                            }
                                            if (response.errors.outdated) {
                                                $('.bookly-js-outdated-alert', $modal).show();
                                            }
                                        }
                                        modalLoading(false);
                                    }
                                    if (response.alert_errors) {
                                        booklyAlert({error: response.alert_errors});
                                    }
                                }
                            });
                            return false;
                        });
                        modalLoading(false);
                    }
                });
            }).booklyModal();
        }
    );
});
jQuery(function($) {

    let
        $packages_list      = $('#bookly-packages-list'),
        $check_all_button   = $('#bookly-check-all'),
        $id_filter          = $('#bookly-filter-id'),
        $date_filter        = $('#bookly-filter-date'),
        $staff_filter       = $('#bookly-filter-staff'),
        $customer_filter    = $('#bookly-filter-customer'),
        $package_filter     = $('#bookly-filter-package'),
        $service_filter     = $('#bookly-filter-service'),
        $add_button         = $('#bookly-new-package'),
        $delete_button      = $('#bookly-delete'),
        isMobile            = false,
        columns             = [],
        order               = []
    ;

    try {
        document.createEvent("TouchEvent");
        isMobile = true;
    } catch (e) {

    }
    $('.bookly-js-select').val(null);
    $.each(BooklyPackagesL10n.datatables.packages.settings.filter, function (field, value) {
        if (value != '') {
            $('#bookly-filter-' + field).val(value);
        }
        // check if select has correct values
        if ($('#bookly-filter-' + field).prop('type') == 'select-one') {
            if ($('#bookly-filter-' + field +' option[value="' + value + '"]').length == 0) {
                $('#bookly-filter-' + field).val(null);
            }
        }
    });

    /**
     * Init Columns.
     */
    $.each(BooklyPackagesL10n.datatables.packages.settings.columns, function (column, show) {
        if (show) {
            switch (column) {
                case 'customer_phone':
                    columns.push({
                        data: 'customer.phone',
                        render: function (data, type, row, meta) {
                            if (isMobile) {
                                return '<a href="tel:' + data + '">' + $.fn.dataTable.render.text().display(data) + '</a>';
                            } else {
                                return $.fn.dataTable.render.text().display(data);
                            }
                        }
                    });
                    break;
                case 'customer_full_name':
                    columns.push({data: 'customer.full_name', render: $.fn.dataTable.render.text()});
                    break;
                case 'created_at':
                    columns.push({data: 'created_at', render: $.fn.dataTable.render.text()});
                    break;
                default:
                    columns.push({data: column.replace('_', '.'), render: $.fn.dataTable.render.text()});
                    break;
            }
        }
    });

    columns.push({
        responsivePriority: 1,
        orderable: false,
        width: 180,
        render: function (data, type, row, meta) {
            return '<div style="white-space: nowrap;"><button type="button" class="btn btn-default mr-1 bookly-js-edit-package-schedule" title="' + BooklyPackagesL10n.scheduleAppointments + '"><i class="far fa-fw fa-calendar-alt mr-1"></i>' + BooklyPackagesL10n.schedule + '…</button><button type="button" class="btn btn-default bookly-js-edit-package" title="' + BooklyPackagesL10n.editPackage + '"><i class="far fa-fw fa-edit mr-1"></i>' + BooklyPackagesL10n.edit + '…</button></div>';
        }
    });

    columns.push({
        responsivePriority: 1,
        orderable: false,
        render: function (data, type, row, meta) {
            return '<div class="custom-control custom-checkbox">' +
                '<input value="' + row.id + '" id="bookly-dt-' + row.id + '" type="checkbox" class="custom-control-input">' +
                '<label for="bookly-dt-' + row.id + '" class="custom-control-label"></label>' +
                '</div>';
        }
    });

    $.each(BooklyPackagesL10n.datatables.packages.settings.order, function (_, value) {
        const index = columns.findIndex(function (c) { return c.data === value.column; });
        if (index !== -1) {
            order.push([index, value.order]);
        }
    });

    /**
     * Init DataTables.
     */
    var dt = $packages_list.DataTable({
        order     : order,
        info      : false,
        paging    : false,
        searching : false,
        processing: true,
        responsive: true,
        serverSide: true,
        ajax      : {
            url : ajaxurl,
            type: 'POST',
            data: function (d) {
                return $.extend({action: 'bookly_packages_get_packages', csrf_token: BooklyPackagesL10n.csrf_token}, {
                    filter: {
                        id      : $id_filter.val(),
                        date    : $date_filter.data('date'),
                        staff   : $staff_filter.val(),
                        customer: $customer_filter.val(),
                        package : $package_filter.val(),
                        service : $service_filter.val()
                    }
                }, d);
            }
        },
        columns   : columns,
        language  : {
            zeroRecords: BooklyPackagesL10n.zeroRecords,
            processing : BooklyPackagesL10n.processing
        }
    });

    /**
     * Add package.
     */
    $add_button.on('click', function () {
        showPackageDialog(
            null,
            function(event) {
                dt.ajax.reload();
            }
        )
    });

    $packages_list.on('click', 'button.bookly-js-edit-package', function() {
        showPackageDialog(
            dt.row($(this).closest('td')).data().id,
            function(event) {
                dt.ajax.reload();
            }
        )
    });

    $packages_list.on('click', 'button.bookly-js-edit-package-schedule', function () {
        $(document.body).trigger('bookly_packages.schedule_dialog', [dt.row($(this).closest('td')).data().id, function (event) {
            dt.ajax.reload();
        }]);
    });

    /**
     * Select all packages.
     */
    $check_all_button.on('change', function () {
        $packages_list.find('tbody input:checkbox').prop('checked', this.checked);
    });

    /**
     * On appointment select.
     */
    $packages_list.on('change', 'tbody input:checkbox', function () {
        $check_all_button.prop('checked', $packages_list.find('tbody input:not(:checked)').length == 0);
    });

    /**
     * Delete appointments.
     */
    $delete_button.on('click', function () {
        var ladda = Ladda.create(this);
        ladda.start();

        var data = [];
        var $checkboxes = $packages_list.find('tbody input[type="checkbox"]:checked');
        $checkboxes.each(function () {
            data.push(this.value);
        });

        $.ajax({
            url  : ajaxurl,
            type : 'POST',
            data : {
                action     : 'bookly_packages_delete_packages',
                csrf_token : BooklyPackagesL10n.csrf_token,
                data       : data,
                notify     : $('#bookly-delete-notify').prop('checked') ? 1 : 0,
                reason     : $('#bookly-delete-reason').val()
            },
            dataType : 'json',
            success  : function(response) {
                ladda.stop();
                $('#bookly-delete-dialog').booklyModal('hide');
                if (response.success) {
                    if (response.data && response.data.queue && response.data.queue.length) {
                        $(document.body).trigger('bookly.queue_dialog', [response.data.queue]);
                    }
                    dt.draw(false);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    /**
     * Init date range picker.
     */
    var picker_ranges = {};
    picker_ranges[BooklyPackagesL10n.dateRange.yesterday] = [moment().subtract(1, 'days'), moment().subtract(1, 'days')];
    picker_ranges[BooklyPackagesL10n.dateRange.today]     = [moment(), moment()];
    picker_ranges[BooklyPackagesL10n.dateRange.tomorrow]  = [moment().add(1, 'days'), moment().add(1, 'days')];
    picker_ranges[BooklyPackagesL10n.dateRange.last_7]    = [moment().subtract(7, 'days'), moment()];
    picker_ranges[BooklyPackagesL10n.dateRange.last_30]   = [moment().subtract(30, 'days'), moment()];
    picker_ranges[BooklyPackagesL10n.dateRange.thisMonth] = [moment().startOf('month'), moment().endOf('month')];
    picker_ranges[BooklyPackagesL10n.dateRange.nextMonth] = [moment().add(1, 'month').startOf('month'), moment().add(1, 'month').endOf('month')];

    $date_filter.daterangepicker(
        {
            parentEl : $date_filter.parent(),
            startDate: moment().startOf('month'),
            endDate  : moment().endOf('month'),
            ranges   : picker_ranges,
            showDropdowns: true,
            linkedCalendars: false,
            locale: $.extend({},BooklyPackagesL10n.dateRange, BooklyPackagesL10n.datePicker)
        },
        function(start, end) {
            var format = 'YYYY-MM-DD';
            $date_filter
                .data('date', start.format(format) + ' - ' + end.format(format))
                .find('span')
                .html(start.format(BooklyPackagesL10n.dateRange.format) + ' - ' + end.format(BooklyPackagesL10n.dateRange.format));
        }
    );

    /**
     * On filters change.
     */
    $('.bookly-js-select')
        .select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#bookly-tbs',
            allowClear: true,
            placeholder: '',
            language  : {
                noResults: function() { return BooklyPackagesL10n.no_result_found; }
            },
            matcher: function (params, data) {
                const term = $.trim(params.term).toLowerCase();
                if (term === '' || data.text.toLowerCase().indexOf(term) !== -1) {
                    return data;
                }

                let result = null;
                const search = $(data.element).data('search');
                search &&
                search.find(function(text) {
                    if (result === null && text.toLowerCase().indexOf(term) !== -1) {
                        result = data;
                    }
                });

                return result;
            }
        });

    $('.bookly-js-select-ajax')
        .select2({
            width: '100%',
            theme: 'bootstrap4',
            dropdownParent: '#bookly-tbs',
            allowClear: true,
            placeholder: '',
            language  : {
                noResults: function() { return BooklyPackagesL10n.no_result_found; },
                searching: function () { return BooklyPackagesL10n.searching; }
            },
            ajax: {
                url: ajaxurl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    params.page = params.page || 1;
                    return {
                        action: this.action === undefined ? $(this).data('ajax--action') : this.action,
                        filter: params.term,
                        page: params.page,
                        csrf_token : BooklyPackagesL10n.csrf_token
                    };
                }
            },
        });

    $id_filter.on('keyup', function () { dt.ajax.reload(); });
    $date_filter.on('apply.daterangepicker', function () { dt.ajax.reload(); });
    $staff_filter.on('change', function () { dt.ajax.reload(); });
    $customer_filter.on('change', function () { dt.ajax.reload(); });
    $package_filter.on('change', function () { dt.ajax.reload(); });
    $service_filter.on('change', function () { dt.ajax.reload(); });
});
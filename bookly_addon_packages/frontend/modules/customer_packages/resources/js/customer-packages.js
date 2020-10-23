(function ($) {
    window.booklyCustomerPackages = function (Options) {
        var $container     = $('.bookly-js-customer-packages-' + Options.form_id),
            $packages_list = $('#bookly-packages-list', $container);

        /**
         * Init DataTables.
         */
        var dt = $packages_list.DataTable({
            order       : [[1, 'desc']],
            info        : false,
            lengthChange: false,
            pageLength  : 25,
            pagingType  : 'numbers',
            searching   : false,
            processing  : true,
            responsive  : true,
            serverSide  : true,
            ajax        : {
                url : Options.ajaxurl,
                type: 'POST',
                data: function (d) {
                    return $.extend({action: 'bookly_packages_get_packages', csrf_token: BooklyCustomerPackagesL10n.csrf_token}, {
                        filter: {}
                    }, d);
                }
            },
            columns     : [
                {data: 'package.title', responsivePriority: 1},
                {data: 'created_at', responsivePriority: 2},
                {data: 'expires', responsivePriority: 2, orderable: false},
                {data: 'staff.name', responsivePriority: 3, render: $.fn.dataTable.render.text()},
                {data: 'service.title', responsivePriority: 2},
                {data: 'package.size', responsivePriority: 3},
                {
                    responsivePriority: 1,
                    orderable         : false,
                    render            : function (data, type, row, meta) {
                        return '<button type="button" class="btn btn-default bookly-js-edit-package-schedule" title="' + BooklyCustomerPackagesL10n.scheduleAppointments + '"><i class="far fa-fw fa-calendar-alt"></button>';
                    }
                }
            ],
            dom         : "<'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12'p>>",
            language    : {
                zeroRecords: BooklyCustomerPackagesL10n.zeroRecords,
                processing : BooklyCustomerPackagesL10n.processing
            }
        });

        $packages_list.on('click', 'button.bookly-js-edit-package-schedule', function () {
            $(document.body).trigger('bookly_packages.schedule_dialog', [dt.row($(this).closest('td')).data().id, function (event) {
                dt.ajax.reload();
            }, null, BooklyCustomerPackagesL10n.useClientTimeZone != '1']);
        });
    }
})(jQuery);
;(function() {

    var module = booklyAngular.module('packageDialog', ['customerDialog']);

    /**
     * DataSource service.
     */
    module.factory('dataSource', function($q, $rootScope, $filter) {
        var ds = {
            loaded : false,
            data : {
                staff         : [],
                customers     : [],
                status        : {
                    items: [],
                    default: null
                }
            },
            form : {
                screen          : null,
                id              : null,
                staff           : null,
                service         : null,
                location        : null,
                customer        : null,
                notification    : null,
                schedule        : false
            },
            loadData : function() {
                var deferred = $q.defer();
                if (!ds.loaded) {
                    jQuery.get(
                        ajaxurl,
                        { action : 'bookly_get_data_for_appointment_form', csrf_token : BooklyPackagesL10n.csrf_token, type : 'package' },
                        function(data) {
                            ds.loaded = true;
                            ds.data = data;

                            if (data.staff.length) {
                                ds.form.staff = data.staff[0];
                            }
                            if (data.customers === false) {
                                ds.data.customers = [];
                                ds.data.customers_remote = true;
                                // Init select2 remote.
                                jQuery('#bookly-select2').select2({
                                    theme: 'bootstrap4',
                                    dropdownParent: '#bookly-tbs',
                                    allowClear: false,
                                    language: {
                                        noResults: function () {
                                            return BooklyPackagesL10n.no_result_found;
                                        },
                                        searching: function () {
                                            return BooklyPackagesL10n.searching;
                                        }
                                    },
                                    ajax      : {
                                        url           : ajaxurl,
                                        dataType      : 'json',
                                        delay         : 250,
                                        data          : function (params) {
                                            params.page = params.page || 1;
                                            return {
                                                action    : 'bookly_get_customers_list',
                                                filter    : params.term,
                                                page      : params.page,
                                                csrf_token: BooklyPackagesL10n.csrf_token
                                            };
                                        },
                                        processResults: function (data) {
                                            data.results.forEach(function (customer) {
                                                if (!ds.findCustomer(customer.id)) {
                                                    customer.custom_fields = [];
                                                    customer.extras = [];
                                                    customer.extras_consider_duration = ds.data.extras_consider_duration;
                                                    customer.extras_multiply_nop = ds.data.extras_multiply_nop;
                                                    customer.number_of_persons = 1;
                                                    customer.compound_token = null;
                                                    customer.payment_id = null;
                                                    customer.payment_type = null;
                                                    customer.payment_title = null;
                                                    ds.data.customers.push(customer);
                                                }
                                            });
                                            return {
                                                results   : data.results.map(function (item) {
                                                    return {id: item.id, text: item.name}
                                                }),
                                                pagination: data.pagination
                                            };
                                        }
                                    },
                                }).on('select2:select', function () {
                                    jQuery(this).trigger('change');
                                }).on("select2:selecting", function (data) {
                                    data.preventDefault();
                                    var $scope = booklyAngular.element(jQuery('#bookly-package-dialog')[0]).scope();
                                    $scope.$apply(function ($scope) {
                                        $scope.form.customer = $scope.dataSource.data.customers.find(function(x) { return x.id === data.params.args.data.id; });
                                    });
                                    jQuery(this).select2('close').trigger('change');
                                });
                            } else {
                                jQuery('#bookly-select2').select2({
                                    theme: 'bootstrap4',
                                    dropdownParent: '#bookly-tbs',
                                    allowClear: false,
                                    language  : {
                                        noResults: function() { return BooklyPackagesL10n.no_result_found; }
                                    }
                                });
                            }

                            deferred.resolve();
                        },
                        'json'
                    );
                } else {
                    deferred.resolve();
                }

                return deferred.promise;
            },
            findStaff : function(id) {
                var result = null;
                jQuery.each(ds.data.staff, function(key, item) {
                    if (item.id == id) {
                        result = item;
                        return false;
                    }
                });
                return result;
            },
            findService : function(staff_id, id) {
                var result = null,
                    staff  = ds.findStaff(staff_id);

                if (staff !== null) {
                    jQuery.each(staff.services, function(key, item) {
                        if (item.id == id) {
                            result = item;
                            return false;
                        }
                    });
                }
                return result;
            },
            findLocation : function(staff_id, id) {
                var result = null,
                    staff  = ds.findStaff(staff_id);

                if (staff !== null) {
                    jQuery.each(staff.locations, function(key, item) {
                        if (item.id == id) {
                            result = item;
                            return false;
                        }
                    });
                }
                return result;
            },
            findCustomer : function(id) {
                var result = null;
                jQuery.each(ds.data.customers, function(key, item) {
                    if (item.id == id) {
                        result = item;
                        return false;
                    }
                });
                return result;
            },
            resetCustomers : function() {
                ds.data.customers.forEach(function(customer) {
                    customer.custom_fields     = [];
                    customer.extras            = [];
                    customer.status            = ds.data.status.default;
                    customer.number_of_persons = 1;
                    customer.compound_token    = null;
                    customer.payment_id        = null;
                    customer.payment_type      = null;
                    customer.payment_title     = null;
                });
            }
        };

        return ds;
    });

    /**
     * Controller for 'create/edit package' dialog form.
     */
    module.controller('packageDialogCtrl', function($scope, $element, dataSource, $filter) {
        // Set up initial data.
        $scope.$calendar = null;
        // Set up data source.
        $scope.dataSource = dataSource;
        $scope.form = dataSource.form;  // shortcut
        // Error messages.
        $scope.errors = {};
        // Callback to be called after editing package.
        var callback = null;

        /**
         * Prepare the form for new event.
         *
         * @param int staff_id
         * @param moment start_date
         * @param function _callback
         */
        $scope.configureNewForm = function(_callback) {
            jQuery.extend($scope.form, {
                screen          : 'main',
                id              : null,
                staff           : null,
                service         : null,
                location        : null,
                customer        : null,
                internal_note   : null,
                schedule        : false
            });
            $scope.errors = {};
            callback = _callback;

            $scope.dataSource.resetCustomers();
        };

        /**
         * Prepare the form for editing an event.
         */
        $scope.configureEditForm = function(package_id, _callback) {
            $scope.loading = true;
            jQuery.post(
                ajaxurl,
                {action: 'bookly_packages_get_data_for_package', id: package_id, csrf_token : BooklyPackagesL10n.csrf_token},
                function(response) {
                    $scope.$apply(function($scope) {
                        if (response.success) {
                            jQuery.extend($scope.form, {
                                screen          : 'main',
                                id              : package_id,
                                staff           : $scope.dataSource.findStaff(response.data.staff_id),
                                service         : $scope.dataSource.findService(response.data.staff_id, response.data.service_id),
                                location        : $scope.dataSource.findLocation(response.data.staff_id, response.data.location_id),
                                customer        : $scope.dataSource.findCustomer(response.data.customer_id),
                                internal_note   : response.data.internal_note,
                                schedule        : false
                            });

                            $scope.dataSource.resetCustomers();

                        }
                        $scope.loading = false;
                    });
                },
                'json'
            );
            $scope.errors = {};
            callback = _callback;
        };

        $scope.onServiceChange = function () {
            $scope.checkFormErrors();
        };

        $scope.onLocationChange = function () {
            $scope.checkFormErrors();
        };

        $scope.onStaffChange = function() {
            if ($scope.form.staff.services.length == 1) {
                $scope.form.service = $scope.form.staff.services[0];
                $scope.onServiceChange();
            } else {
                $scope.form.service = null;
            }
            $scope.form.location = $scope.form.staff.locations.length == 1 ? $scope.form.staff.locations[0] : null;
            $scope.checkFormErrors();
        };

        $scope.checkFormErrors = function() {
            $scope.errors.location_service_combination = $scope.form.staff && $scope.form.staff['id'] == null && $scope.form.location && $scope.form.service && jQuery.inArray($scope.form.service['id'], $scope.form.location['services']) == -1
        };

        $scope.processForm = function() {
            $scope.loading = true;

            $scope.errors = {};

            jQuery.post(
                ajaxurl,
                {
                    action        : 'bookly_packages_save_package_form',
                    csrf_token    : BooklyPackagesL10n.csrf_token,
                    id            : $scope.form.id,
                    staff_id      : $scope.form.staff ? $scope.form.staff.id : null,
                    service_id    : $scope.form.service ? $scope.form.service.id : null,
                    location_id   : $scope.form.location ? $scope.form.location.id : null,
                    customer_id   : $scope.form.customer ? $scope.form.customer.id : null,
                    notification  : $scope.form.notification,
                    internal_note : $scope.form.internal_note
                },
                function (response) {
                    $scope.$apply(function($scope) {
                        if (response.success) {
                            if (callback) {
                                // Call callback.
                                callback(response.data);
                            }
                            if (response.queue && response.queue.length) {
                                if ($scope.form.schedule) {
                                    jQuery(document.body).trigger('bookly.queue_dialog', [response.queue, function () {
                                        jQuery(document.body).trigger('bookly_packages.schedule_dialog', [response.package_id]);
                                    }]);
                                } else {
                                    jQuery(document.body).trigger('bookly.queue_dialog', [response.queue]);
                                }
                            } else if ($scope.form.schedule) {
                                jQuery(document.body).trigger('bookly_packages.schedule_dialog', [response.package_id]);
                            }
                            // Close the dialog.
                            $element.children().booklyModal('hide');
                        } else {
                            $scope.errors = response.errors;
                        }
                        $scope.loading = false;
                    });
                },
                'json'
            );
        };

        // On 'Save & Schedule' button click.
        $scope.saveAndSchedule = function () {
            $scope.form.schedule = true;
            $scope.processForm();
        };

        // On 'Cancel' button click.
        $scope.closeDialog = function () {
            // Close the dialog.
            $element.children().booklyModal('hide');
        };

        $scope.statusToString = function (status) {
            return dataSource.data.status.items[status];
        };

        /**************************************************************************************************************
         * New customer                                                                                               *
         **************************************************************************************************************/

        /**
         * Create new customer.
         * @param customer
         */
        $scope.createCustomer = function(customer) {
            // Add new customer to the list.
            var new_customer = {
                id                : customer.id.toString(),
                name              : customer.full_name,
                custom_fields     : customer.custom_fields,
                extras            : customer.extras,
                status            : customer.status,
                number_of_persons : 1,
                compound_token    : null,
                payment_id        : null,
                payment_type      : null,
                payment_title     : null
            };

            if (customer.email || customer.phone){
                new_customer.name += ' (' + [customer.email, customer.phone].filter(Boolean).join(', ') + ')';
            }

            dataSource.data.customers.push(new_customer);
            dataSource.form.customer = new_customer;
        };

        $scope.removeCustomer = function(customer) {
            $scope.form.customer = null;
        };

        $scope.openNewCustomerDialog = function() {
            var $dialog = jQuery('#bookly-customer-dialog');
            $dialog.booklyModal({show: true});
        };
    });

    /**
     * Directive for slide up/down.
     */
    module.directive('mySlideUp', function() {
        return function(scope, element, attrs) {
            element.hide();
            // watch the expression, and update the UI on change.
            scope.$watch(attrs.mySlideUp, function(value) {
                if (value) {
                    element.delay(0).slideDown();
                } else {
                    element.slideUp();
                }
            });
        };
    });

    /**
     * Directive for Popover jQuery plugin.
     */
    module.directive('popover', function() {
        return function(scope, element, attrs) {
            element.booklyPopover({
                trigger : 'hover',
                content : function() { return this.getAttribute('popover'); },
                html    : true,
                placement: 'top',
                template: '<div class="popover" style="width: 220px" role="tooltip"><div class="arrow"></div><h3 class="popover-header"></h3><div class="popover-body"></div></div>'
            });
        };
    });
})();

/**
 * @param int package_id
 * @param function callback
 */
var showPackageDialog = function (package_id, callback) {
    var $dialog = jQuery('#bookly-package-dialog');
    var $scope = booklyAngular.element($dialog[0]).scope();
    $scope.form.customer = null;
    $scope.$apply(function ($scope) {
        $scope.loading = true;
        $dialog
            .find('.modal-title')
            .text(package_id ? BooklyPackagesL10n.edit_package : BooklyPackagesL10n.new_package);
        // Populate data source.
        $scope.dataSource.loadData().then(function() {
            $scope.loading = false;
            if (package_id) {
                $scope.configureEditForm(package_id, callback);
            } else {
                $scope.configureNewForm(callback);
            }
        });
    });

    // hide new customer dialog, if it remained opened.
    if (jQuery('#bookly-customer-dialog').hasClass('in')) {
        jQuery('#bookly-customer-dialog').booklyModal('hide');
    }

    $dialog.booklyModal('show');

    jQuery(document)
        // Required because Bootstrap blocks all focusin calls from elements outside the dialog
        .on('focusin', function (e) {
            if (jQuery(e.target).closest('.select2-search__field').length) {
                e.stopImmediatePropagation();
            }
        });
};
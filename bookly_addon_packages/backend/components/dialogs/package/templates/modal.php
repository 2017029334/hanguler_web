<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
use Bookly\Backend\Components\Dialogs;
use Bookly\Lib\Config;
?>
<div ng-bookly-app="packageDialog" ng-controller="packageDialogCtrl">
    <div id="bookly-package-dialog" class="bookly-modal bookly-fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <form ng-submit=processForm()>
                    <div class="modal-header">
                        <h5 class="modal-title"><?php esc_html_e( 'New package', 'bookly' ) ?></h5>
                        <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div ng-show=loading class="modal-body">
                        <div class="bookly-loading"></div>
                    </div>
                    <div ng-hide="loading || form.screen != 'main'" class="modal-body">
                        <div class=form-group>
                            <label for="bookly-provider"><?php esc_html_e( 'Provider', 'bookly' ) ?></label>
                            <select id="bookly-provider" class="form-control custom-select" ng-model="form.staff" ng-options="s.full_name for s in dataSource.data.staff" ng-change="onStaffChange()"></select>
                            <small class="form-text text-muted"><?php esc_html_e( 'Select service provider to see the packages provided. Or select unassigned package to see packages with no particular provider.', 'bookly' ) ?></small>
                        </div>

                        <div class=form-group>
                            <label for="bookly-service"><?php esc_html_e( 'Package', 'bookly' ) ?></label>
                            <select id="bookly-service" class="form-control custom-select" ng-model="form.service"
                                    ng-options="s.title for s in form.staff.services" ng-change="onServiceChange()">
                                <option value=""><?php esc_html_e( '-- Select a package --', 'bookly' ) ?></option>
                            </select>
                            <small class="form_text text-danger" my-slide-up="errors.service_required">
                                <?php esc_html_e( 'Please select a package', 'bookly' ) ?>
                            </small>
                        </div>

                        <?php if ( Config::locationsActive() ): ?>
                            <div class="form-group">
                                <label for="bookly-package-location"><?php esc_html_e( 'Location', 'bookly' ) ?></label>
                                <select id="bookly-package-location" class="form-control custom-select" ng-model="form.location"
                                        ng-options="l.name for l in form.staff.locations" ng-change="onLocationChange()">
                                    <option value=""></option>
                                </select>
                            </div>
                            <small class="form-text text-success" my-slide-up="errors.location_service_combination">
                                <?php esc_html_e( 'Incorrect location and package combination', 'bookly' ) ?>
                            </small>
                        <?php endif ?>

                        <div class=form-group>
                            <label for="bookly-select2"><?php esc_html_e( 'Customers', 'bookly' ) ?></label>
                            <div>
                                <div class="form-group">
                                    <div class="input-group">
                                        <select id="bookly-select2"  data-placeholder="<?php esc_attr_e( '-- Search customers --', 'bookly' ) ?>"
                                                class="form-control"
                                                ng-model="form.customer" ng-options="c.name for c in dataSource.data.customers"
                                                >
                                        </select>
                                        <div class="input-group-append">
                                            <button class="btn btn-success" type="button" ng-click="openNewCustomerDialog()">
                                                <i class="fas fa-fw fa-plus mr-1"></i><?php esc_html_e( 'New customer', 'bookly' ) ?>
                                            </button>
                                        </div>
                                    </div>
                                    <small class="form-text text-danger" my-slide-up="errors.customers_required">
                                        <?php esc_html_e( 'Please select a customer', 'bookly' ) ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class=form-group>
                            <label for="bookly-internal-note"><?php esc_html_e( 'Internal note', 'bookly' ) ?></label>
                            <textarea class="form-control" ng-model=form.internal_note id="bookly-internal-note"></textarea>
                        </div>
                        <div class=form-group>
                            <?php Inputs::renderCheckBox( __( 'Send notifications', 'bookly' ), null, null, array(
                                'ng-model'       => 'form.notification',
                                'ng-true-value'  => '1',
                                'ng-false-value' => '0',
                                'ng-init'        => 'form.notification = ' . ( get_user_meta( get_current_user_id(), 'bookly_packages_form_send_notifications', true ) ?: 0 ),
                            ) ) ?>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div ng-hide=loading>
                            <?php Buttons::render( 'bookly-save-schedule', 'btn-success', __( 'Save & schedule', 'bookly' ), array( 'ng-click' => 'saveAndSchedule()' ) ) ?>
                            <?php Buttons::renderSubmit() ?>
                            <?php Buttons::renderCancel( null, array( 'ng-click' => 'closeDialog()' ) ) ?>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div customer-dialog=createCustomer(customer)></div>
    <div payment-details-dialog="completePayment(payment_id, payment_title)"></div>

    <?php Dialogs\Customer\Edit\Dialog::render() ?>
</div>
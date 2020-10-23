<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id="bookly-package-schedule-dialog" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form>
                <div class="modal-header">
                    <h5 class="modal-title"><?php esc_html_e( 'Schedule classes', 'bookly' ) ?></h5>                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <h6 class="modal-title"><?php esc_html_e( 'Hello world', 'bookly' ) ?></h6>                    <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <div class="modal-body">
                    <div class="bookly-loading"></div>
                    <div class="bookly-js-modal-body">
                        <div>
                            <label for="bookly-package-schedule-body" class="bookly-js-package-name"></label>
                            <ul class="list-group" id="bookly-package-schedule-body"></ul>
                        </div>
                        <div class="alert alert-success bookly-js-warning-alert bookly-js-expired-warning-alert collapse mt-2">
                            <?php esc_html_e( 'Selected appointment date exceeds the period when the customer can use a package of services.', 'bookly' ) ?>
                            <?php Inputs::renderCheckBox( __( 'Ignore', 'bookly' ), null, null, array( 'name' => 'bookly-ignore-expired' ) ) ?>
                        </div>
                        <div class="alert alert-danger bookly-js-error-alert collapse mt-2">
                            <div class="bookly-js-error bookly-js-schedule-no-slot-error collapse"><?php esc_html_e( 'There are no time slots for selected date.', 'bookly' ) ?></div>
                            <div class="bookly-js-error bookly-js-expired-error-alert collapse"><?php esc_html_e( 'Selected appointment date exceeds the period when the customer can use a package of services.', 'bookly' ) ?></div>
                            <div class="bookly-js-error bookly-js-occupied-alert collapse"><?php esc_html_e( 'Selected period is occupied by another appointment', 'bookly' ) ?></div>
                            <div class="bookly-js-error bookly-js-time-prior-cancel-alert collapse"><?php esc_html_e( 'Unfortunately, you\'re not able to cancel the appointment because the required time limit prior to canceling has expired.', 'bookly' ) ?></div>
                            <div class="bookly-js-error bookly-js-time-prior-booking-alert collapse"><?php esc_html_e( 'Unfortunately, you\'re not able to book an appointment because the required time limit prior to booking has expired.', 'bookly' ) ?></div>
                            <div class="bookly-js-error bookly-js-outdated-alert collapse"><?php esc_html_e( 'You are trying to schedule an appointment in the past. Please select another time slot.', 'bookly' ) ?></div>
                        </div>
                        <nav class="mt-3">
                            <ul class="pagination">
                                <li class="page-item disabled" data-page="1">
                                    <a href="#" class="page-link"><span>«</span></a>
                                </li>
                                <li class="page-item" data-page="2">
                                    <a href="#" class="page-link"><span>»</span></a>
                                </li>
                            </ul>
                        </nav>
                        <?php if ( is_admin() ) : ?>
                            <div class=form-group >
                                <?php Inputs::renderCheckBox( __( 'Send notifications', 'bookly' ), null, get_user_meta( get_current_user_id(), 'bookly_packages_schedule_form_send_notifications', true ) == 'yes', array( 'id' => 'bookly-packages-schedule-notification' ) ) ?>
                            </div>
                        <?php endif ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <div>
                        <?php Buttons::renderSubmit() ?>
                        <?php Buttons::renderCancel() ?>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="schedule_entry_template" class="collapse">
    <li class="list-group-item">
        <div class="form-row bookly-js-schedule-entry" data-id="{{id}}">
            <div class="col-1"><b>{{number}}</b></div>
            <div class="col-8 mr-auto">
                <div class="form-row">
                    <!-- <div class="col-sm-5 bookly-js-schedule-view bookly-js-schedule-staff">{{staff}}</div> -->
                    <div class="col-sm-4 bookly-js-schedule-view bookly-js-schedule-date">{{date}}</div>
                    <div class="col-sm-3 bookly-js-schedule-view bookly-js-schedule-time">{{time}}</div>
                    <div class="col-sm-3 bookly-js-schedule-view bookly-js-schedule-time">{{time}}</div>
                    <div class="col-sm-12 bookly-js-schedule-edit collapse">
                        <div class="form-group bookly-js-schedule-edit-staff">
                            <select class="form-control custom-select"></select>
                        </div>
                        <div class="form-row">
                            <div class="col-sm-7 bookly-js-schedule-edit collapse bookly-js-schedule-edit-date form-group"><input type="text" class="form-control" autocomplete="off" value="{{date}}" placeholder="<?php esc_attr_e( 'Select appointment date', 'bookly' ) ?>"/></div>
                            <div class="col-sm-5 bookly-js-schedule-edit collapse bookly-js-schedule-edit-time form-group"><select class="form-control custom-select"></select></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-2 bookly-js-schedule-view text-right">
                <a href class="bookly-js-schedule-edit-btn" title="<?php esc_attr_e( 'Edit package appointment', 'bookly' ) ?>"><i class="far fa-fw fa-edit"></i></a>
                <a href class="bookly-js-schedule-clear-btn text-danger" title="<?php esc_attr_e( 'Delete package appointment', 'bookly' ) ?>"><i class="far fa-fw fa-trash-alt"></i></a>
            </div>
            <div class="col-2 bookly-js-schedule-edit text-right collapse">
                <a href class="bookly-js-schedule-apply-btn"><i class="fas fa-fw fa-check"></i></a>
                <a href class="bookly-js-schedule-cancel-btn text-danger"><i class="fas fa-fw fa-times"></i></a>
            </div>
        </div>
    </li>
</div>

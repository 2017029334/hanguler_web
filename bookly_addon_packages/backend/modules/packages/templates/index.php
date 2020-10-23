<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls;
use Bookly\Backend\Components\Dialogs\TableSettings;
use Bookly\Backend\Components\Dialogs\Appointment;
use Bookly\Backend\Components\Dialogs\Queue;
use Bookly\Backend\Components\Support;
use BooklyPackages\Backend\Components\Dialogs;
use Bookly\Lib\Utils\DateTime;
/** @var array $datatables */
?>
<div id="bookly-tbs" class="wrap">
    <div class="form-row align-items-center mb-3">
        <h4 class="col m-0"><?php esc_html_e( 'Packages', 'bookly' ) ?></h4>
        <?php Support\Buttons::render( $self::pageSlug() ) ?>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="form-row justify-content-end">
                <div class="col-12 col-sm-auto">
                    <?php Controls\Buttons::render( 'bookly-new-package', 'btn-success w-100 mb-3', __( 'New package', 'bookly' ), array(), '<i class="fa fa-fw fa-plus mr-1"></i>{caption}…' ) ?>
                </div>
                <?php TableSettings\Dialog::renderButton( 'packages', 'BooklyPackagesL10n' ) ?>
            </div>
            <div class="form-row">
                <div class="col-md-3 col-lg-1">
                    <div class="form-group">
                        <input class="form-control" type="text" id="bookly-filter-id" placeholder="<?php esc_attr_e( 'No.', 'bookly' ) ?>"/>
                    </div>
                </div>
                <div class="col-md-3 col-lg-3">
                    <button type="button" class="btn btn-default mb-3 w-100 text-left text-truncate" id="bookly-filter-date"
                            data-date="<?php echo date( 'Y-m-d', strtotime( 'first day of' ) ) ?> - <?php echo date( 'Y-m-d', strtotime( 'last day of' ) ) ?>">
                        <i class="far fa-calendar-alt mr-1"></i>
                        <span>
                            <?php echo DateTime::formatDate( 'first day of this month' ) ?> - <?php echo DateTime::formatDate( 'last day of this month' ) ?>
                        </span>
                    </button>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-staff" data-placeholder="<?php echo esc_attr( get_option( 'bookly_l10n_label_employee' ) ) ?>">
                            <option value="0"><?php esc_attr_e( 'Unassigned', 'bookly' ) ?></option>
                            <?php foreach ( $staff_members as $staff ) : ?>
                                <option value="<?php echo $staff['id'] ?>"><?php echo esc_html( $staff['full_name'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="clearfix visible-md-block"></div>
                <div class="col-md-4 col-lg-2">
                    <div class="form-group">
                        <select class="form-control <?php echo $customers === false ? 'bookly-js-select-ajax' : 'bookly-js-select' ?>" id="bookly-filter-customer"
                                data-placeholder="<?php esc_attr_e( 'Customer', 'bookly' ) ?>" <?php echo $customers === false ? 'data-ajax--action' : 'data-action' ?>="bookly_get_customers_list">
                        <?php if ( $customers !== false ) : ?>
                            <?php foreach ( $customers as $customer_id => $customer ) : ?>
                                <option value="<?php echo $customer_id ?>" data-search='<?php echo esc_attr( json_encode( array_values( $customer ) ) ) ?>'><?php echo esc_html( $customer['full_name'] ) ?></option>
                            <?php endforeach ?>
                        <?php endif ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-package" data-placeholder="<?php esc_attr_e( 'Package', 'bookly' ) ?>">
                            <?php foreach ( $packages as $package ) : ?>
                                <option value="<?php echo $package['id'] ?>"><?php echo esc_html( $package['title'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 col-lg-2">
                    <div class="form-group">
                        <select class="form-control bookly-js-select" id="bookly-filter-service" data-placeholder="<?php echo esc_attr( get_option( 'bookly_l10n_label_service' ) ) ?>">
                            <?php foreach ( $services as $service ) : ?>
                                <option value="<?php echo $service['id'] ?>"><?php echo esc_html( $service['title'] ) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                </div>
            </div>
            <table id="bookly-packages-list" class="table table-striped w-100">
                <thead>
                <tr>
                    <?php foreach ( $datatables['packages']['settings']['columns'] as $column => $show ) : ?>
                        <?php if ( $show ) : ?>
                            <th><?php echo $datatables['packages']['titles'][ $column ] ?></th>
                        <?php endif ?>
                    <?php endforeach ?>
                    <th></th>
                    <th width="16"><?php Controls\Inputs::renderCheckBox( null, null, null, array( 'id' => 'bookly-check-all' ) ) ?></th>
                </tr>
                </thead>
            </table>

            <div class="text-right mt-3">
                <?php Controls\Buttons::renderDelete( null, null, null, array( 'data-toggle' => 'bookly-modal', 'data-target' => '#bookly-delete-dialog' ) ) ?>
            </div>
        </div>
    </div>
    <?php Appointment\Delete\Dialog::render() ?>
    <?php Dialogs\Schedule\Dialog::render() ?>
    <?php Dialogs\Package\Dialog::render() ?>
    <?php Queue\Dialog::render() ?>
    <?php TableSettings\Dialog::render() ?>
</div>
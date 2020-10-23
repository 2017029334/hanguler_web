<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * @var array  $service_collection
 * @var array  $service
 */
?>
<div id="bookly-js-service-packages">
    <div class="form-group">
        <label for="package_service"><?php esc_html_e( 'Service', 'bookly' ) ?></label>
        <select class="form-control custom-select bookly-js-package-sub-service" id="package_service" name="package_service">
            <option value="0"><?php esc_html_e( 'Select service', 'bookly' ) ?></option>
            <?php foreach ( $service_collection as $_service ) : ?>
                <?php if ( $_service['id'] != $service['id'] && $_service['type'] == \Bookly\Lib\Entities\Service::TYPE_SIMPLE && $_service['units_max'] == 1 ) : ?>
                    <option value="<?php echo $_service['id'] ?>"<?php if ( isset( $service['sub_services'][0] ) ) {selected( $_service['id'], $service['sub_services'][0]['sub_service_id'] );} ?>><?php echo esc_html( $_service['title'] ) ?></option>
                <?php endif ?>
            <?php endforeach ?>
        </select>
        <input type="hidden" id="bookly-js-package-service-changed" name="package_service_changed" value="0" />
    </div>
    <div class="form-group">
        <label><?php esc_html_e( 'Unassigned', 'bookly' ) ?></label>
        <div class="custom-control custom-radio">
            <input type="radio" id="bookly-package-unassigned-0" name="package_unassigned" value="0"<?php checked( $service['package_unassigned'], 0 ) ?> class="custom-control-input" />
            <label for="bookly-package-unassigned-0" class="custom-control-label"><?php esc_html_e( 'Disabled', 'bookly' ) ?></label>
        </div>
        <div class="custom-control custom-radio">
            <input type="radio" id="bookly-package-unassigned-1" name="package_unassigned" value="1"<?php checked( $service['package_unassigned'], 1 ) ?> class="custom-control-input" />
            <label for="bookly-package-unassigned-1" class="custom-control-label"><?php esc_html_e( 'Enabled', 'bookly' ) ?></label>
        </div>
        <small class="form-text text-muted"><?php esc_html_e( 'Enable this setting so that the package can be displayed and available for booking when clients have not specified a particular provider.', 'bookly' ) ?></small>
    </div>
    <div class="form-group">
        <label for="package_size"><?php esc_html_e( 'Quantity', 'bookly' ) ?></label>
        <input id="package_size" class="form-control" type="number" min="1" step="1" name="package_size" value="<?php echo esc_attr( $service['package_size'] ? : 10 ) ?>">
    </div>
    <div class="form-group">
        <label for="package_life_time"><?php esc_html_e( 'Life Time', 'bookly' ) ?></label>
        <input id="package_life_time" class="form-control" type="number" min="0" step="1" name="package_life_time" value="<?php echo esc_attr( $service['package_life_time'] ? : 30 ) ?>">
        <small class="form-text text-muted"><?php esc_html_e( 'The period in days when the customer can use a package of services.', 'bookly' ) ?></small>
    </div>
</div>
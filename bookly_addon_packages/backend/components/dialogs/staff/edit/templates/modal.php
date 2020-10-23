<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use Bookly\Backend\Components\Controls\Buttons;
use Bookly\Backend\Components\Controls\Inputs;
?>
<div id="bookly-packages-tip" class="bookly-modal bookly-fade" tabindex=-1 role="dialog">
    <div class="modal-dialog modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php esc_html_e( 'Packages', 'bookly' ) ?></h5>
                <button type="button" class="close" data-dismiss="bookly-modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <p><?php esc_html_e( 'When you select a package the associated services will also be selected. Disabling the service will disable the package.', 'bookly' ) ?></p>
                <?php Inputs::renderCheckBox( __( 'don\'t show this notification again', 'bookly' ), null, null, array( 'class' => 'bookly-js-dont-show-packages-tip' ) ); ?>
            </div>
            <div class="modal-footer">
                <div>
                    <?php Buttons::renderCancel( __( 'Close', 'bookly' ) ) ?>
                </div>
            </div>
        </div>
    </div>
</div>


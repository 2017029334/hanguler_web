<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if ( is_user_logged_in() ) : ?>
    <div id="bookly-tbs">
        <div>
            <?php esc_html_e( 'You don\'t have permissions to view this content.', 'bookly' ) ?>
        </div>
    </div>
<?php else : ?>
    <?php wp_login_form() ?>
<?php endif ?>
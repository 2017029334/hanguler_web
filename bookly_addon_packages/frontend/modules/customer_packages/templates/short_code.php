<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
use BooklyPackages\Backend\Components\Dialogs;
use Bookly\Lib\Utils\Common;
?>
<div id="bookly-tbs" class="wrap bookly-js-customer-packages-<?php echo $form_id ?> ">
    
    <table id="bookly-packages-list" class="table table-striped w-100">
        <thead>
        <tr>
            <th><?php esc_html_e( 'Package', 'bookly' ) ?></th>
            <th><?php esc_html_e( 'Creation Date', 'bookly' ) ?></th>
            <th><?php esc_html_e( 'Expires', 'bookly' ) ?></th>
            <th><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ) ?></th>
            <th><?php echo esc_html( Common::getTranslatedOption( 'bookly_l10n_label_service' ) ) ?></th>
            <th><?php esc_html_e( 'Quantity', 'bookly' ) ?></th>
            <th><?php esc_html_e( 'Schedule', 'bookly' ) ?></th>
        </tr>
        </thead>
    </table>
    <?php Dialogs\Package\Dialog::render() ?>
    <?php Dialogs\Schedule\Dialog::render() ?>
</div>

<script type="text/javascript">
    (function (win, fn) {
        var done = false, top = true,
            doc = win.document,
            root = doc.documentElement,
            modern = doc.addEventListener,
            add = modern ? 'addEventListener' : 'attachEvent',
            rem = modern ? 'removeEventListener' : 'detachEvent',
            pre = modern ? '' : 'on',
            init = function(e) {
                if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                if (!done) { done = true; fn.call(win, e.type || e); }
            },
            poll = function() {
                try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                init('poll');
            };
        if (doc.readyState == 'complete') fn.call(win, 'lazy');
        else {
            if (!modern) if (root.doScroll) {
                try { top = !win.frameElement; } catch(e) { }
                if (top) poll();
            }
            doc[add](pre + 'DOMContentLoaded', init, false);
            doc[add](pre + 'readystatechange', init, false);
            win[add](pre + 'load', init, false);
        }
    })(window, function() {
        var a = document.getElementsByClassName("bookly-js-customer-packages-<?php echo $form_id ?>")[0];
        while (a) {
            try {
                if (getComputedStyle(a).zIndex !== 'auto') {
                    a.style.zIndex = "auto";
                }
            } catch (e) {
            }
            a = a.parentNode;
        }
        window.booklyCustomerPackages({
            ajaxurl : <?php echo json_encode( $ajax_url ) ?>,
            form_id : <?php echo json_encode( $form_id ) ?>
        });
    });
</script>
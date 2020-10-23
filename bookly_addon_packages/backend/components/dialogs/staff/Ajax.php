<?php
namespace BooklyPackages\Backend\Components\Dialogs\Staff;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Lib;

/**
 * Class Ajax
 * @package BooklyPackages\Backend\Components\Dialogs\Staff
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * Hide modal with tip in staff services.
     */
    public static function hideStaffServicesTip()
    {
        update_user_meta( get_current_user_id(), 'bookly_packages_hide_staff_services_tip', 1 );

        wp_send_json_success();
    }
}
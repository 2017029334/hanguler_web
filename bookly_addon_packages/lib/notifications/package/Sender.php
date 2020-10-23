<?php
namespace BooklyPackages\Lib\Notifications\Package;

use Bookly\Lib\Entities\Customer;
use Bookly\Lib\Entities\Staff;
use Bookly\Lib\Notifications\Base;
use BooklyPackages\Lib;
use BooklyPackages\Lib\Notifications\Assets\Package\Codes;

/**
 * Class Package
 * @package BooklyPackages\Lib\Notifications\Instant
 */
abstract class Sender extends Base\Sender
{
    /**
     * Send email/sms with packages details.
     *
     * @param Lib\Entities\Package $package
     * @param string               $type
     * @param string               $reason
     * @param bool|array           $queue
     */
    public static function send( $package, $type = 'created', $reason = '', &$queue = false )
    {
        $codes   = new Codes( $package, $type, $reason );
        $notifications = static::getNotifications( $type == 'created' ? 'new_package' : 'package_deleted' );

        $customer = Customer::find( $package->getCustomerId() );
        $staff    = Staff::find( $package->getStaffId() );

        // Reply to customer.
        $reply_to = null;
        if ( get_option( 'bookly_email_reply_to_customers' ) ) {
            $reply_to = array( 'email' => $customer->getEmail(), 'name' => $customer->getFullName() );
        }

        // Notify client.
        foreach ( $notifications['client'] as $notification ) {
            static::sendToClient( $customer, $notification, $codes, null, $queue );
        }

        // Notify staff & admins.
        foreach ( $notifications['staff'] as $notification ) {
            if ( $staff ) {
                static::sendToStaff( $staff, $notification, $codes, null, $reply_to, $queue );
            }
            static::sendToAdmins( $notification, $codes, null, $reply_to, $queue );
        }
    }
}
<?php
namespace BooklyPackages\Backend\Modules\Notifications\ProxyProviders;

use Bookly\Backend\Modules\Notifications\Proxy;

/**
 * Class Shared
 * @package BooklyPackages\Backend\Modules\Notifications\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareNotificationCodes( array $codes, $type )
    {
        $codes['package'] = array(
            'package_name'        => __( 'name of package', 'bookly' ),
            'package_size'        => __( 'package size', 'bookly' ),
            'package_price'       => __( 'price of package', 'bookly' ),
            'package_life_time'   => __( 'package life time', 'bookly' ),
            'cancellation_reason' => __( 'reason you mentioned while deleting package', 'bookly' ),
        );

        return $codes;
    }

    /**
     * @inheritdoc
     */
    public static function buildNotificationCodesList( array $codes, $notification_type, array $codes_data )
    {
        switch ( $notification_type ) {
            case 'new_package':
            case 'package_deleted':
                $codes = array_merge(
                    $codes_data['category'],
                    $codes_data['company'],
                    $codes_data['customer'],
                    $codes_data['package'],
                    $codes_data['service'],
                    $codes_data['staff']
                );
                if ( $notification_type != 'package_deleted' ) {
                    unset ( $codes['cancellation_reason'] );
                }
                break;
        }

        return $codes;
    }

}
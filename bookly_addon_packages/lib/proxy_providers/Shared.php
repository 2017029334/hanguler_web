<?php
namespace BooklyPackages\Lib\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities\Notification;

/**
 * Class Shared
 * @package BooklyPackages\Lib\Shared
 */
class Shared extends BooklyLib\Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareNotificationTitles( array $titles )
    {
        $titles[ Notification::TYPE_NEW_PACKAGE ]     = __( 'Notification about new package creation', 'bookly' );
        $titles[ Notification::TYPE_PACKAGE_DELETED ] = __( 'Notification about package deletion', 'bookly' );

        return $titles;
    }

    /**
     * @inheritdoc
     */
    public static function prepareNotificationTypes( array $types, $gateway )
    {
        $types[] = Notification::TYPE_NEW_PACKAGE;
        $types[] = Notification::TYPE_PACKAGE_DELETED;

        return $types;
    }

    /**
     * @inheritDoc
     */
    public static function prepareTableColumns( $columns, $table )
    {
        if ( $table == BooklyLib\Utils\Tables::PACKAGES ) {
            $columns = array_merge( $columns, array(
                'id'                 => esc_html__( 'No.', 'bookly' ),
                'created_at'         => esc_html__( 'Creation date', 'bookly' ),
                'staff_name'         => esc_html( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_employee' ) ),
                'customer_full_name' => esc_html__( 'Customer name', 'bookly' ),
                'customer_phone'     => esc_html__( 'Customer phone', 'bookly' ),
                'customer_email'     => esc_html__( 'Customer email', 'bookly' ),
                'package_title'      => esc_html__( 'Package', 'bookly' ),
                'service_title'      => esc_html( BooklyLib\Utils\Common::getTranslatedOption( 'bookly_l10n_label_service' ) ),
                'package_size'       => esc_html__( 'Quantity', 'bookly' ),
            ) );
        }

        return $columns;
    }
}
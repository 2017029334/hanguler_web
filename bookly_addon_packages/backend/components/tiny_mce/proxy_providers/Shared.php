<?php
namespace BooklyPackages\Backend\Components\TinyMce\ProxyProviders;

use Bookly\Backend\Components\TinyMce\Proxy;

/**
 * Class Shared
 * @package BooklyPackages\Backend\Components\TinyMce\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function renderMediaButtons( $version )
    {
        if ( $version < 3.5 ) {
            // show button for v 3.4 and below
            echo '<a href="#" id="add-customer-packages-list" title="' . esc_attr__( 'Add customer packages list', 'bookly' ) . '">' . __( 'Add customer packages list', 'bookly' ) . '</a>';
        } else {
            // display button matching new UI
            $img = '<span class="bookly-media-icon"></span> ';
            echo '<button href="#" id="add-customer-packages-list" class="thickbox button bookly-media-button" title="' . esc_attr__( 'Add customer packages list', 'bookly' ) . '">' . $img . __( 'Add customer packages list', 'bookly' ) . '</button>';
        }
    }

    /**
     * @inheritdoc
     */
    public static function renderPopup()
    {
        self::renderTemplate( 'popup' );
    }
}
<?php
namespace BooklyPackages\Frontend\Modules\CustomerPackages;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Lib;

/**
 * Class ShortCode
 * @package BooklyPackages\Frontend\Modules\CustomerPackages
 */
class ShortCode extends BooklyLib\Base\Component
{
    /**
     * Init component.
     */
    public static function init()
    {
        // Register shortcodes.
        add_shortcode( 'bookly-packages-list', array( __CLASS__, 'render' ) );

        // Assets.
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkStyles' ) );
        add_action( 'wp_enqueue_scripts', array( __CLASS__, 'linkScripts' ) );
    }

    /**
     * Link styles.
     */
    public static function linkStyles()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-packages-list' )
        ) {
            $bookly_ver = BooklyLib\Plugin::getVersion();
            $bookly_url = plugins_url( '', BooklyLib\Plugin::getMainFile() );
            $packages   = plugins_url( '', Lib\Plugin::getMainFile() );

            wp_enqueue_style( 'bookly-fontawesome-all.min.css', $bookly_url . '/backend/resources/css/fontawesome-all.min.css', array(), $bookly_ver );
            wp_enqueue_style( 'bookly-ladda.min.css', $bookly_url . '/frontend/resources/css/ladda.min.css', array(), $bookly_ver );
            wp_enqueue_style( 'bookly-bootstrap.min.css', $bookly_url . '/backend/resources/bootstrap/css/bootstrap.min.css', array(), $bookly_ver );
            wp_enqueue_style( 'bookly-customer-packages.css', $packages . '/frontend/modules/customer_packages/resources/css/customer-packages.css', array( 'bookly-bootstrap.min.css' ), Lib\Plugin::getVersion() );
        }
    }

    /**
     * Link scripts.
     */
    public static function linkScripts()
    {
        if (
            get_option( 'bookly_gen_link_assets_method' ) == 'enqueue' ||
            BooklyLib\Utils\Common::postsHaveShortCode( 'bookly-packages-list' )
        ) {
            $bookly_ver = BooklyLib\Plugin::getVersion();
            $bookly_url = plugins_url( '', BooklyLib\Plugin::getMainFile() );
            $packages   = plugins_url( '', Lib\Plugin::getMainFile() );

            wp_enqueue_script( 'bookly-spin.min.js', $bookly_url . '/frontend/resources/js/spin.min.js', array( 'jquery' ), $bookly_ver );
            wp_enqueue_script( 'bookly-ladda.min.js', $bookly_url . '/frontend/resources/js/ladda.min.js', array( 'bookly-spin.min.js' ), $bookly_ver );
            wp_enqueue_script( 'bookly-bootstrap.min.js', $bookly_url . '/backend/resources/bootstrap/js/bootstrap.min.js', array( 'jquery' ), $bookly_ver );
            wp_enqueue_script( 'bookly-range-tools.js', $bookly_url . '/backend/resources/js/range-tools.js', array( 'jquery' ), $bookly_ver );
            wp_enqueue_script( 'bookly-moment.min.js', $bookly_url . '/backend/resources/js/moment.min.js', array(), $bookly_ver );
            wp_enqueue_script( 'bookly-daterangepicker.js', $bookly_url . '/backend/resources/js/daterangepicker.js', array( 'jquery', 'bookly-moment.min.js' ), $bookly_ver );
            wp_enqueue_script( 'bookly-datatables.min.js', $bookly_url . '/backend/resources/js/datatables.min.js', array( 'jquery' ), $bookly_ver );
            wp_enqueue_script( 'bookly-customer-packages.js', $packages . '/frontend/modules/customer_packages/resources/js/customer-packages.js', array( 'bookly-datatables.min.js' ), Lib\Plugin::getVersion() );

            wp_localize_script( 'bookly-customer-packages.js', 'BooklyCustomerPackagesL10n', array(
                'zeroRecords'          => __( 'No packages for selected period and criteria.', 'bookly' ),
                'scheduleAppointments' => __( 'Package schedule', 'bookly' ),
                'processing'           => __( 'Processing...', 'bookly' ),
                'useClientTimeZone'    => BooklyLib\Config::useClientTimeZone(),
                'csrf_token'           => BooklyLib\Utils\Common::getCsrfToken(),
            ) );
        }
    }

    /**
     * @param $attributes
     * @return string
     */
    public static function render( $attributes )
    {
        // Disable caching.
        BooklyLib\Utils\Common::noCache();

        $customer = new BooklyLib\Entities\Customer();
        $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
        if ( $customer->getId() ) {
            // Prepare URL for AJAX requests.
            $ajax_url = admin_url( 'admin-ajax.php' );

            $form_id = uniqid();

            return self::renderTemplate( 'short_code', compact( 'ajax_url', 'customer', 'form_id' ), false );
        } else {
            return self::renderTemplate( 'permission', array(), false );
        }
    }
}
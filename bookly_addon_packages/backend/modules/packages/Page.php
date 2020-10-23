<?php
namespace BooklyPackages\Backend\Modules\Packages;

use Bookly\Lib as BooklyLib;
use Bookly\Lib\Entities;
use Bookly\Lib\Utils;
use Bookly\Lib\DataHolders;

/**
 * Class Controller
 *
 * @package BooklyPackages\Backend\Modules\Packages
 */
class Page extends BooklyLib\Base\Component
{
    /**
     * Render page.
     */
    public static function render()
    {
        self::enqueueStyles( array(
            'bookly' => array(
                'backend/resources/bootstrap/css/bootstrap.min.css',
                'frontend/resources/css/ladda.min.css',
            ),
        ) );

        self::enqueueScripts( array(
            'bookly' => array(
                'backend/resources/bootstrap/js/bootstrap.min.js' => array( 'jquery' ),
                'backend/resources/js/datatables.min.js'          => array( 'jquery' ),
                'backend/resources/js/moment.min.js'              => array( 'jquery' ),
                'backend/resources/js/daterangepicker.js'         => array( 'jquery' ),
                'backend/resources/js/select2.min.js'             => array( 'jquery' ),
                'frontend/resources/js/spin.min.js'               => array( 'jquery' ),
                'frontend/resources/js/ladda.min.js'              => array( 'jquery' ),
            ),
            'module' => array( 'js/packages.js' => array( 'jquery' ), ),
        ) );

        $datatables = BooklyLib\Utils\Tables::getSettings( 'packages' );

        wp_localize_script( 'bookly-packages.js', 'BooklyPackagesL10n', array(
            'csrf_token'           => Utils\Common::getCsrfToken(),
            'new_package'          => __( 'New package', 'bookly' ),
            'edit_package'         => __( 'Edit package', 'bookly' ),
            'datePicker'           => Utils\DateTime::datePickerOptions(),
            'dateRange'            => Utils\DateTime::dateRangeOptions(),
            'are_you_sure'         => __( 'Are you sure?', 'bookly' ),
            'zeroRecords'          => __( 'No packages for selected period and criteria.', 'bookly' ),
            'scheduleAppointments' => __( 'Package schedule', 'bookly' ),
            'editPackage'          => __( 'Edit package', 'bookly' ),
            'processing'           => __( 'Processing...', 'bookly' ),
            'edit'                 => __( 'Edit', 'bookly' ),
            'schedule'             => __( 'Schedule', 'bookly' ),
            'no_result_found'      => __( 'No result found', 'bookly' ),
            'searching'            => __( 'Searching', 'bookly' ),
            'datatables'           => $datatables,
        ) );

        // Filters data
        $staff_members = Entities\Staff::query( 's' )->select( 's.id, s.full_name' )->fetchArray();
        $customers     = BooklyLib\Entities\Customer::query()->count() < BooklyLib\Entities\Customer::REMOTE_LIMIT
            ? array_map( function ( $row ) {
                unset( $row['id'] );

                return $row;
            }, BooklyLib\Entities\Customer::query( 'c' )->select( 'c.id, c.full_name, c.email, c.phone' )->indexBy( 'id' )->fetchArray() )
            : false;
        $packages      = Entities\Service::query( 's' )->select( 's.id, s.title' )->where( 'type', Entities\Service::TYPE_PACKAGE )->fetchArray();
        $services      = Entities\Service::query( 's' )->select( 's.id, s.title' )->where( 'type', Entities\Service::TYPE_SIMPLE )->fetchArray();

        self::renderTemplate( 'index', compact( 'staff_members', 'customers', 'packages', 'services', 'datatables' ) );
    }
}
<?php
namespace BooklyPackages\Backend\Components\Dialogs\Package;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Lib;

/**
 * Class Ajax
 * @package BooklyPackages\Backend\Components\Dialogs\Package
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array( '_default' => 'supervisor' );
    }
    /**
     * Get package data when editing an package.
     */
    public static function getDataForPackage()
    {
        $response = array( 'success' => false, 'data' => array() );

        $package = new Lib\Entities\Package();
        if ( $package->load( self::parameter( 'id' ) ) ) {
            $response['success']               = true;
            $response['data']['staff_id']      = $package->getStaffId();
            $response['data']['service_id']    = $package->getServiceId();
            $response['data']['internal_note'] = $package->getInternalNote();
            $response['data']['location_id']   = $package->getLocationId();
            $response['data']['customer_id']   = $package->getCustomerId();
        }
        wp_send_json( $response );
    }

    /**
     * Save package form (for both create and edit).
     */
    public static function savePackageForm()
    {
        $response = array( 'success' => false );

        $package_id    = (int) self::parameter( 'id', 0 );
        $staff_id      = self::parameter( 'staff_id' ) ?: null;
        $service_id    = (int) self::parameter( 'service_id' );
        $location_id   = self::parameter( 'location_id' ) ?: null;
        $customer_id   = (int) self::parameter( 'customer_id' );
        $internal_note = self::parameter( 'internal_note' );
        $notification  = self::parameter( 'notification' );

        $staff_service = new BooklyLib\Entities\StaffService();
        $staff_service->loadBy( array(
            'staff_id'    => $staff_id,
            'service_id'  => $service_id,
            'location_id' => BooklyLib\Proxy\Locations::prepareStaffLocationId( $location_id, $staff_id ) ?: null,
        ) );

        if ( ! $service_id ) {
            $response['errors']['service_required'] = true;
        }
        if ( ! $customer_id ) {
            $response['errors']['customers_required'] = true;
        }

        $queue = array();

        // If no errors then try to save the package.
        if ( ! isset ( $response['errors'] ) ) {
            $package = new Lib\Entities\Package();
            if ( $package_id ) {
                // Edit.
                $package->load( $package_id );
            }
            $package
                ->setLocationId( $location_id )
                ->setStaffId( $staff_id )
                ->setServiceId( $service_id )
                ->setCustomerId( $customer_id )
                ->setInternalNote( $internal_note );
            if ( ! $package_id ) {
                $package->setCreatedAt( current_time( 'mysql' ) );
            }

            if ( $package->save() !== false ) {
                $response['success']    = true;
                $response['package_id'] = $package->getId();
                if ( $notification ) {
                    Lib\Notifications\Package\Sender::send( $package, 'created', '', $queue );
                }
            } else {
                $response['errors'] = array( 'db' => __( 'Could not save package in database.', 'bookly' ) );
            }
        }

        update_user_meta( get_current_user_id(), 'bookly_packages_form_send_notifications', $notification );

        $response['queue'] = $queue;

        wp_send_json( $response );
    }
}
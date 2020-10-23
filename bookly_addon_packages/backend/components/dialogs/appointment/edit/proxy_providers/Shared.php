<?php
namespace BooklyPackages\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Appointment\Edit\Proxy;
use BooklyPackages\Backend\Components\Dialogs;

/**
 * Class Shared
 * @package BooklyPackages\Backend\Components\Dialogs\Appointment\Edit\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareDataForPackage( $data )
    {
        $staff_any = array( 'id' => null, 'full_name' => __( 'Unassigned', 'bookly' ), 'services' => array(), 'locations' => array() );
        $locations_any = array();
        foreach ( $data['staff'] as $staff ) {
            foreach ( $staff['services'] as $staff_service ) {
                $service = BooklyLib\Entities\Service::find( $staff_service['id'] );
                if ( $service->getPackageUnassigned() && ! ( isset( $staff_any['services'] ) && in_array( $staff_service['id'], array_map( function ( $service ) { return $service['id']; }, $staff_any['services'] ) ) ) ) {
                    $staff_any['services'][] = $staff_service;
                }
            }
            $staff_services_ids = array_map( function ( $service ) { return $service['id']; }, $staff['services'] );
            foreach ( $staff['locations'] as $location ) {
                $location['services'] = $staff_services_ids;
                if ( isset( $locations_any[ $location['id'] ] ) ) {
                    $location['services'] = array_values( array_unique( array_merge( $location['services'], $locations_any[ $location['id'] ]['services'] ) ) );
                }
                $locations_any[ $location['id'] ] = $location;
            }
        }
        $staff_any['locations'] = array_values( $locations_any );
        array_unshift( $data['staff'], $staff_any );

        return $data;
    }

    /**
     * @inheritdoc
     */
    public static function renderComponents()
    {
        Dialogs\Schedule\Dialog::render();
    }
}
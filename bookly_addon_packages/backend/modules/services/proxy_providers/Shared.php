<?php
namespace BooklyPackages\Backend\Modules\Services\ProxyProviders;

use Bookly\Lib as BooklyLib;
use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Shared
 * @package BooklyPackages\Backend\Modules\Services\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function enqueueAssetsForServices()
    {
        self::enqueueScripts( array(
            'module' => array( 'js/packages.js' => array( 'jquery' ), ),
        ) );
    }

    /**
     * @inheritdoc
     */
    public static function prepareUpdateService( array $data )
    {
        if ( $data['type'] == BooklyLib\Entities\Service::TYPE_PACKAGE ) {
            if ( array_key_exists( 'sub_services', $data ) && is_array( $data['sub_services'] ) && count( $data['sub_services'] ) == 1 && $data['sub_services'][0] > 0 ) {
                // Update SubService.
                $rows = BooklyLib\Entities\SubService::query()->where( 'service_id', $data['id'] )->fetchArray();
                if ( count( $rows ) != 1 ) {
                    BooklyLib\Entities\SubService::query()->delete()->where( 'service_id', $data['id'] )->execute();
                    $sub_service = new BooklyLib\Entities\SubService();
                    $sub_service
                        ->setServiceId( $data['id'] )
                        ->setSubServiceId( $data['sub_services'][0] )
                        ->setPosition( 0 )
                        ->save();
                } else {
                    if ( $rows[0]['sub_service_id'] != $data['sub_services'][0] ) {
                        $sub_service = new BooklyLib\Entities\SubService( $rows[0] );
                        $sub_service
                            ->setSubServiceId( $data['sub_services'][0] )
                            ->save();
                    }
                }
                // Copy needful information from SubService.
                $sub_service_entity    = BooklyLib\Entities\Service::find( $data['sub_services'][0] );
                $data['category_id']   = $sub_service_entity->getCategoryId();
                $data['duration']      = $sub_service_entity->getDuration();
                $data['capacity_min']  = $sub_service_entity->getCapacityMin();
                $data['capacity_max']  = $sub_service_entity->getCapacityMax();
                $data['padding_left']  = $sub_service_entity->getPaddingLeft();
                $data['padding_right'] = $sub_service_entity->getPaddingRight();
            }
        } elseif ( $data['type'] == BooklyLib\Entities\Service::TYPE_SIMPLE ) {
            $rows = BooklyLib\Entities\Service::query( 's' )
                ->innerJoin( 'SubService', 'ss', 'ss.service_id = s.id' )
                ->where( 's.type', BooklyLib\Entities\Service::TYPE_PACKAGE )
                ->where( 'ss.sub_service_id', $data['id'] )
                ->fetchArray();
            foreach ( $rows as $row ) {
                $service = new BooklyLib\Entities\Service( $row );
                $service
                    ->setCategoryId( $data['category_id'] )
                    ->setDuration( $data['duration'] )
                    ->setCapacityMin( $data['capacity_min'] )
                    ->setCapacityMax( $data['capacity_max'] )
                    ->setPaddingLeft( $data['padding_left'] )
                    ->setPaddingRight( $data['padding_right'] )
                    ->save();
            }
        }

        return $data;
    }

    /**
     * @inheritdoc
     */
    public static function prepareServiceColors( array $colors, $service_id, $service_type )
    {
        if ( $service_type == BooklyLib\Entities\Service::TYPE_PACKAGE ) {
            $row = BooklyLib\Entities\SubService::query( 'ss' )
                ->select( 's.color' )
                ->innerJoin( 'Service', 's', 'ss.sub_service_id = s.id' )
                ->where( 'ss.service_id', $service_id )
                ->fetchRow();
            $color  = isset( $row['color'] ) ? $row['color'] : '-1';
            $colors = array( $color, $color, $color );
        }

        return $colors;
    }

    /**
     * @inheritdoc
     */
    public static function prepareServiceIcons( $icons )
    {
        $icons[ BooklyLib\Entities\Service::TYPE_PACKAGE ] = 'far fa-calendar-alt';

        return $icons;
    }

    /**
     * @inheritdoc
     */
    public static function prepareServiceTypes( $types )
    {
        $types[ BooklyLib\Entities\Service::TYPE_PACKAGE ] = __( 'Package', 'bookly' );

        return $types;
    }

    /**
     * @inheritdoc
     */
    public static function updateService( array $alert, BooklyLib\Entities\Service $service, array $_post )
    {
        $staff_ids = isset( $_post['staff_ids'] ) ? $_post['staff_ids'] : array();
        // Update package records when simple service has been updated.
        if ( $service->getType() == BooklyLib\Entities\Service::TYPE_SIMPLE ) {
            $packages     = BooklyLib\Entities\SubService::query( 'ss' )
                ->select( 'ss.service_id' )
                ->where( 'ss.sub_service_id', $service->getId() )
                ->fetchArray();
            $packages_ids = array_map( function ( $packages ) { return $packages['service_id']; }, $packages );

            // Delete staff-services for all packages related to simple service.
            BooklyLib\Entities\StaffService::query()
                ->delete()
                ->whereIn( 'service_id', $packages_ids )
                ->whereNotIn( 'staff_id', $staff_ids )
                ->execute();
            if ( isset( $_post['update_staff'] ) && $_post['update_staff'] ) {
                // Update capacity for all packages related to simple service.
                BooklyLib\Entities\StaffService::query()
                    ->update()
                    ->set( 'capacity_min', $_post['capacity_min'] )
                    ->set( 'capacity_max', $_post['capacity_max'] )
                    ->whereIn( 'service_id', $packages_ids )
                    ->execute();
            }
        }

        // Update package staff services.
        // Update simple service when related package service has been updated.
        if ( $service->getType() == BooklyLib\Entities\Service::TYPE_PACKAGE ) {
            // Update price & capacity for existing package's staff services.
            if ( isset( $_post['update_staff'] ) && $_post['update_staff'] ) {
                $query = BooklyLib\Entities\StaffService::query()
                    ->update()
                    ->set( 'price', $_post['price'] );
                // Update capacity if package's sub service has been changed.
                if ( isset( $_post['package_service_changed'] ) && $_post['package_service_changed'] ) {
                    $query
                        ->set( 'capacity_min', $_post['capacity_min'] )
                        ->set( 'capacity_max', $_post['capacity_max'] );
                }
                $query
                    ->where( 'service_id', $service->getId() )
                    ->execute();
            }
            $sub_service = current( $service->getSubServices() );
            if ( $sub_service ) {
                // Create staff services for package.
                $existing_staff = BooklyLib\Entities\StaffService::query()
                    ->select( 'staff_id' )
                    ->where( 'service_id', $service->getId() )
                    ->fetchArray();
                $existing_staff_ids = array_map( function ( $existing_staff ) { return $existing_staff['staff_id']; }, $existing_staff );
                foreach ( $staff_ids as $staff_id ) {
                    $staff_sub_service = new BooklyLib\Entities\StaffService();
                    $staff_sub_service->loadBy( array( 'service_id' => $sub_service->getId(), 'staff_id' => $staff_id ) );
                    $staff_service = new BooklyLib\Entities\StaffService();
                    if ( in_array( $staff_id, $existing_staff_ids ) ) {
                        $staff_service->loadBy( array( 'staff_id' => $staff_id, 'service_id' => $service->getId() ) );
                    }
                    $staff_service
                        ->setStaffId( $staff_id )
                        ->setServiceId( $service->getId() )
                        ->setCapacityMin( $staff_sub_service ? $staff_sub_service->getCapacityMin() : $sub_service->getCapacityMin() )
                        ->setCapacityMax( $staff_sub_service ? $staff_sub_service->getCapacityMax() : $sub_service->getCapacityMax() );
                    if ( ! in_array( $staff_id, $existing_staff_ids ) || $_post['update_staff'] ) {
                        $staff_service->setPrice( $_post['price'] );
                    }
                    $staff_service->save();
                }
                // Create staff services for sub service.
                $existing_staff = BooklyLib\Entities\StaffService::query()
                    ->select( 'staff_id' )
                    ->where( 'service_id', $sub_service->getId() )
                    ->fetchArray();
                $existing_staff_ids = array_map( function ( $existing_staff ) { return $existing_staff['staff_id']; }, $existing_staff );
                foreach ( $staff_ids as $staff_id ) {
                    if ( ! in_array( $staff_id, $existing_staff_ids ) ) {
                        $staff_service = new BooklyLib\Entities\StaffService();
                        $staff_service->setStaffId( $staff_id )
                            ->setServiceId( $sub_service->getId() )
                            ->setPrice( $sub_service->getPrice() )
                            ->setCapacityMin( $sub_service->getCapacityMin() )
                            ->setCapacityMax( $sub_service->getCapacityMax() )
                            ->save();
                    }
                }
            } else {
                BooklyLib\Entities\StaffService::query()->delete()->where( 'service_id', $service->getId() )->execute();
            }
        }

        return $alert;
    }

    /**
     * Delete records for packages if service has been deleted
     *
     * @param integer $service_id
     */
    public static function serviceDeleted( $service_id )
    {
        $service = BooklyLib\Entities\Service::find( $service_id );
        if ( $service->getType() == BooklyLib\Entities\Service::TYPE_SIMPLE ) {
            $packages = BooklyLib\Entities\SubService::query( 'ss' )
                ->select( 's.id' )
                ->innerJoin( 'Service', 's', 'ss.service_id = s.id' )
                ->where( 'ss.sub_service_id', $service->getId() )
                ->where( 's.type', BooklyLib\Entities\Service::TYPE_PACKAGE )
                ->fetchArray();
            // Delete packages & staff-services related to service.
            BooklyLib\Entities\Service::query()
                ->delete()
                ->whereIn( 'id', array_map( function ( $packages ) { return $packages['id']; }, $packages ) )
                ->execute();
        }
    }
}
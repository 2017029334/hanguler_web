<?php
namespace BooklyPackages\Backend\Modules\Packages;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Lib\Entities;
use BooklyPackages\Lib;

/**
 * Class Ajax
 * @package BooklyPackages\Backend\Modules\Packages
 */
class Ajax extends BooklyLib\Base\Ajax
{
    /**
     * @inheritDoc
     */
    protected static function permissions()
    {
        return array(
            '_default'    => 'supervisor',
            'getPackages' => 'customer',
        );
    }

    /**
     * Get list of packages.
     */
    public static function getPackages()
    {
        $columns = self::parameter( 'columns' );
        $order   = self::parameter( 'order', array() );
        $filter  = self::parameter( 'filter' );

        $query = Entities\Package::query( 'p' )
            ->select( '
                p.id,
                p.location_id,
                p.staff_id,
                p.service_id,
                p.created_at,
                ps.title        AS package_title,
                ps.package_size AS package_size,
                s.title         AS service_title,
                c.full_name     AS customer_full_name,
                c.phone         AS customer_phone,
                c.email         AS customer_email,
                st.full_name    AS staff_name
            ' )
            ->leftJoin( 'Service', 'ps', 'ps.id = p.service_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'SubService', 'ss', 'ss.service_id = ps.id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Service', 's', 's.id = ss.sub_service_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Customer', 'c', 'c.id = p.customer_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'Staff', 'st', 'st.id = p.staff_id', '\Bookly\Lib\Entities' );

        $total = $query->count();

        if ( $filter['id'] != '' ) {
            $query->where( 'p.id', $filter['id'] );
        }

        if ( $filter['date'] ) {
            list ( $start, $end ) = explode( ' - ', $filter['date'], 2 );
            $end = date( 'Y-m-d', strtotime( $end ) + DAY_IN_SECONDS );
            $query->whereBetween( 'p.created_at', $start, $end );
        }

        if ( $filter['staff'] != '' ) {
            if ( $filter['staff'] == 0 ) {
                $query->where( 'p.staff_id', null );
            } else {
                $query->where( 'p.staff_id', $filter['staff'] );
            }
        }

        if ( ! BooklyLib\Utils\Common::isCurrentUserSupervisor() ) {
            $customer = new BooklyLib\Entities\Customer();
            $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
            $query->where( 'p.customer_id', $customer->getId() );
        } elseif ( $filter['customer'] != '' ) {
            $query->where( 'p.customer_id', $filter['customer'] );
        }

        if ( $filter['package'] != '' ) {
            $query->where( 'p.service_id', $filter['package'] );
        }

        if ( $filter['service'] != '' ) {
            $query->where( 's.id', $filter['service'] );
        }

        foreach ( $order as $sort_by ) {
            $query->sortBy( str_replace( '.', '_', $columns[ $sort_by['column'] ]['data'] ) )
                ->order( $sort_by['dir'] == 'desc' ? BooklyLib\Query::ORDER_DESCENDING : BooklyLib\Query::ORDER_ASCENDING );
        }

        $filtered = $query->count();

        $query->limit( self::parameter( 'length' ) )->offset( self::parameter( 'start' ) );

        $data = array();

        foreach ( $query->fetchArray() as $row ) {
            // Check inactive packages
            $package = new Lib\Entities\Package();
            $package->load( $row['id'] );
            $expired_date = $package->getExpiredDate();
            if ( $expired_date && $expired_date < date_create( current_time( 'mysql' ) ) ) {
                $row['package_title'] .= sprintf( ' (%s)', __( 'Expired', 'bookly' ) );
            }
            $data[] = array(
                'id'         => $row['id'],
                'created_at' => BooklyLib\Utils\DateTime::formatDateTime( $row['created_at'] ),
                'expires'    => $expired_date ? BooklyLib\Utils\DateTime::formatDateTime( $expired_date->format( 'Y-m-d H:i:s' ) ) : '',
                'staff'      => array(
                    'name' => $row['staff_name'] ?: __( 'Unassigned', 'bookly' ),
                ),
                'customer'   => array(
                    'full_name' => $row['customer_full_name'],
                    'phone'     => $row['customer_phone'],
                    'email'     => $row['customer_email'],
                ),
                'service'    => array(
                    'title' => $row['service_title'],
                ),
                'package'    => array(
                    'title' => $row['package_title'],
                    'size'  => $row['package_size'],
                )
            );
        }

        unset( $filter['date'] );

        BooklyLib\Utils\Tables::updateSettings( 'packages', $columns, $order, $filter );

        wp_send_json( array(
            'draw'            => ( int ) self::parameter( 'draw' ),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ) );
    }

    /**
     * Delete customer appointments.
     */
    public static function deletePackages()
    {
        $notification = self::parameter( 'notify' );

        $queue = array();
        /** @var Entities\Package $package */
        foreach ( Entities\Package::query()->whereIn( 'id', (array) self::parameter( 'data' ) )->find() as $package ) {
            $package->delete();
            if ( $notification ) {
                Lib\Notifications\Package\Sender::send( $package, 'deleted', self::parameter( 'reason' ), $queue );
            }
        }

        wp_send_json_success( compact( 'queue' ) );
    }

}
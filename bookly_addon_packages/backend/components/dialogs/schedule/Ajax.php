<?php
namespace BooklyPackages\Backend\Components\Dialogs\Schedule;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Lib;

/**
 * Class Ajax
 * @package BooklyPackages\Backend\Components\Dialogs\Schedule
 */
class Ajax extends BooklyLib\Base\Ajax
{

    protected static function permissions()
    {
        return array(
            'getDaySchedule'         => array( 'customer', 'supervisor' ),
            'getPackageAppointments' => array( 'customer', 'supervisor' ),
            'saveSchedule'           => array( 'customer', 'supervisor' ),
        );
    }

    /**
     * Get schedule.
     */
    public static function getDaySchedule()
    {
        $date        = self::parameter( 'date' );
        $exclude     = self::parameter( 'exclude', array() );
        $service_id  = self::parameter( 'service_id' );
        $staff_id    = self::parameter( 'staff_id' );
        $location_id = self::parameter( 'location_id' ) ?: null;

        $service = new BooklyLib\Entities\Service();
        $service->load( $service_id );

        $sub_services = $service->getSubServices();
        $sub_service  = $sub_services[0];

        $chain_item = new BooklyLib\ChainItem();
        $chain_item
            ->setStaffIds( array( $staff_id ) )
            ->setServiceId( $sub_service->getId() )
            ->setNumberOfPersons( 1 )
            ->setQuantity( 1 )
            ->setLocationId( $location_id );

        $chain = new BooklyLib\Chain();
        $chain->add( $chain_item );

        $params = array( 'every' => 1 );
        if ( self::parameter( 'time_zone_offset' ) ) {
            $params['time_zone_offset'] = self::parameter( 'time_zone_offset' );
            $params['time_zone'] = self::parameter( 'time_zone' );
        }
        $scheduler = new BooklyLib\Scheduler( $chain, date_create( $date )->format( 'Y-m-d 00:00' ), date_create( $date )->format( 'Y-m-d' ), 'daily', $params, $exclude, false );

        wp_send_json_success( $scheduler->scheduleForFrontend( 1 ) );
    }

    /**
     * Get package data when editing an package.
     */
    public static function getPackageAppointments()
    {
        $response = array( 'success' => true, 'package' => array(), 'appointments' => array() );

        $package_id = self::parameter( 'package_id' );

        $package = Lib\Entities\Package::query( 'p' )
            ->select( 's.title, s.package_size, p.service_id, ss.sub_service_id, p.staff_id, p.location_id, p.customer_id' )
            ->leftJoin( 'Service', 's', 's.id = p.service_id', '\Bookly\Lib\Entities' )
            ->leftJoin( 'SubService', 'ss', 'ss.service_id = p.service_id', '\Bookly\Lib\Entities' )
            ->where( 'p.id', $package_id )
            ->fetchRow();

        $has_access = true;
        if ( ! BooklyLib\Utils\Common::isCurrentUserSupervisor() ) {
            $customer = new BooklyLib\Entities\Customer();
            $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
            if ( $package['customer_id'] != $customer->getId() ) {
                $has_access = false;
            }
        }

        if ( $has_access ) {
            if ( $package['staff_id'] ) {
                $staffs = BooklyLib\Entities\Staff::query( 'st' )
                    ->select( 'st.id, st.full_name')
                    ->where( 'id', $package['staff_id'] )
                    ->limit( 1 )
                    ->fetchArray();
            } else {
                $query = BooklyLib\Entities\Staff::query( 'st' )
                    ->select( 'st.id, st.full_name' )
                    ->leftJoin( 'StaffService', 'sts', 'sts.staff_id = st.id', '\Bookly\Lib\Entities' )
                    ->where( 'sts.service_id', $package['service_id'] );
                if ( $package['location_id'] ) {
                    $query
                        ->innerJoin( 'StaffLocation', 'stl', 'stl.staff_id = st.id', '\BooklyLocations\Lib\Entities' )
                        ->where( 'stl.location_id', $package['location_id'] );
                }
                $staffs = $query->fetchArray();
            }
            $response['staff'] = array_map( function ( $item ) { $item['full_name'] = esc_html( $item['full_name'] ); return $item; }, $staffs );
            $response['package'] = $package;

            $appointments = BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                ->select( 'a.start_date, s.title, ca.id, st.id as staff_id, st.full_name as staff_full_name, ca.time_zone, ca.time_zone_offset' )
                ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id', '\Bookly\Lib\Entities' )
                ->leftJoin( 'Service', 's', 's.id = a.service_id', '\Bookly\Lib\Entities' )
                ->leftJoin( 'Staff', 'st', 'st.id = a.staff_id', '\Bookly\Lib\Entities' )
                ->where( 'ca.package_id', self::parameter( 'package_id' ) )
                ->whereNotIn( 'ca.status', BooklyLib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                    BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED,
                    BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED
                ) ) )
                ->sortBy( 'a.start_date' )
                ->fetchArray();

            foreach ( $appointments as $appointment ) {
                if ( ! self::parameter( 'use_wp_timezone', true ) ) {
                    $appointment['start_date'] = BooklyLib\Utils\DateTime::applyTimeZone( $appointment['start_date'], $appointment['time_zone'], $appointment['time_zone_offset'] ) ?: $appointment['start_date'];
                }
                $appointment['staff_full_name'] = esc_html( $appointment['staff_full_name'] );
                $response['appointments'][] = $appointment;
            }
        }

        wp_send_json( $response );
    }

    /**
     * Save schedule.
     */
    public static function saveSchedule()
    {
        $response         = array( 'success' => true, 'errors' => array(), 'warnings' => array(), 'warning_slots' => array(), 'error_slots' => array(), 'not_deleted' => array() );
        $package_id       = (int) self::parameter( 'package_id' );
        $schedule         = self::parameter( 'schedule', array() );
        $deleted          = self::parameter( 'deleted', array() );
        $notification     = self::parameter( 'notification' );
        $ignore_expired   = self::parameter( 'ignore_expired', false ) == 'true';
        $time_zone        = self::parameter( 'time_zone' );
        $time_zone_offset = self::parameter( 'time_zone_offset' );
        $is_admin         = BooklyLib\Utils\Common::isCurrentUserSupervisor();

        $package = new Lib\Entities\Package();
        $package->load( $package_id );

        if ( ! $is_admin ) {
            $customer = new BooklyLib\Entities\Customer();
            $customer->loadBy( array( 'wp_user_id' => get_current_user_id() ) );
            if ( $package->getCustomerId() != $customer->getId() ) {
                $response['success']          = false;
                $response['errors']['access'] = true;

                wp_send_json( $response );
            }
        }

        $service = new BooklyLib\Entities\Service();
        $service->load( $package->getServiceId() );

        $sub_service = current( $service->getSubServices() );

        $customer = new BooklyLib\Entities\Customer();
        $customer->load( $package->getCustomerId() );

        // Check errors
        $schedule_dates = array();
        if ( ! empty( $schedule ) ) {
            // Calculate expired date for the package.
            $db_expired_date = $package->getExpiredDate();
            $min_start_date  = null;
            foreach ( $schedule as $slot ) {
                $slot             = json_decode( $slot['slot'] );
                $start_date       = date_create( $slot[0][2] );
                $schedule_dates[] = array( 'start_date' => $slot[0][2], 'end_date' => date( 'Y-m-d H:i:s', strtotime( $slot[0][2] ) + $sub_service->getDuration() ) );
                if ( ! $min_start_date || $min_start_date > $start_date ) {
                    $min_start_date = $start_date;
                }
            }
            $expired_date = $min_start_date->modify( sprintf( '+ %s days', $service->getPackageLifeTime() ) );
            $expired_date = $db_expired_date ? min( $expired_date, $db_expired_date ) : $expired_date;
            foreach ( $schedule_dates as $index => $date ) {
                // Check if appointment not in package life time range.
                if ( ! ( $is_admin && $ignore_expired ) && date_create( $date['start_date'] ) > $expired_date ) {
                    $response['success'] = false;
                    if ( $is_admin ) {
                        $response['warnings']['expired'] = true;
                        $response['warning_slots'][]     = $index;
                    } else {
                        $response['errors']['expired'] = true;
                        $response['error_slots'][]     = $index;
                    }
                }
                // Check if time slot is not occupied by other appointments.
                $bound_start = date_create( $date['start_date'] )->modify( '-' . (int) $sub_service->getPaddingLeft() . ' sec' );
                $bound_end   = date_create( $date['end_date'] )->modify( (int) $sub_service->getPaddingRight() . ' sec' );
                $staff_id = $package->getStaffId() === null ? $schedule[ $index ]['staff'] : $package->getStaffId();
                $query = BooklyLib\Entities\CustomerAppointment::query( 'ca' )
                    ->select( sprintf( 'ss.capacity_max, SUM(ca.number_of_persons) AS total_number_of_persons,
                        DATE_SUB(a.start_date, INTERVAL %s SECOND) AS bound_left,
                        DATE_ADD(a.end_date,   INTERVAL (%s + IF(ca.extras_consider_duration, a.extras_duration, 0)) SECOND) AS bound_right',
                        BooklyLib\Proxy\Shared::prepareStatement( 0, 'COALESCE(s.padding_left,0)', 'Service' ),
                        BooklyLib\Proxy\Shared::prepareStatement( 0, 'COALESCE(s.padding_right,0)', 'Service' ) ) )
                    ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
                    ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
                    ->leftJoin( 'Service', 's', 's.id = a.service_id' )
                    ->where( 'a.staff_id', $staff_id )
                    ->whereIn( 'ca.status', BooklyLib\Proxy\CustomStatuses::prepareBusyStatuses( array(
                        BooklyLib\Entities\CustomerAppointment::STATUS_PENDING,
                        BooklyLib\Entities\CustomerAppointment::STATUS_APPROVED,
                        BooklyLib\Entities\CustomerAppointment::STATUS_WAITLISTED
                    ) ) )
                    ->groupBy( 'a.service_id, a.start_date' )
                    ->havingRaw( '%s > bound_left AND bound_right > %s AND ( total_number_of_persons + %d ) > ss.capacity_max',
                        array( $bound_end->format( 'Y-m-d H:i:s' ), $bound_start->format( 'Y-m-d H:i:s' ), 1 ) )
                    ->limit( 1 );
                $rows  = $query->execute();

                if ( $rows != 0 ) {
                    // Exist intersect appointment, time not available.
                    $response['success']            = false;
                    $response['errors']['occupied'] = true;
                    $response['error_slots'][]      = $index;
                }
                if ( ! $is_admin ) {
                    if ( strtotime( $date['start_date'] ) < current_time( 'timestamp' ) ) {
                        // Check appointments in the past
                        $response['success']            = false;
                        $response['errors']['outdated'] = true;
                        $response['error_slots'][]      = $index;
                    } elseif ( BooklyLib\Slots\DatePoint::now()->modify( BooklyLib\Proxy\Pro::getMinimumTimePriorBooking() )->toClientTz()->value() > $bound_start ) {
                        // Check minimum time requirement prior to booking
                        $response['success']                      = false;
                        $response['errors']['time_prior_booking'] = true;
                        $response['error_slots'][]                = $index;
                    } elseif ( strtotime( $date['start_date'] ) > current_time( 'timestamp' ) + BooklyLib\Config::getMaximumAvailableDaysForBooking() * DAY_IN_SECONDS ) {
                        // Check max available days for booking
                        $response['success']                    = false;
                        $response['errors']['max_booking_date'] = true;
                        $response['error_slots'][]              = $index;
                    }
                }
            }
        }
        if ( ! $is_admin ) {
            $minimum_time_prior_cancel = (int) BooklyLib\Proxy\Pro::getMinimumTimePriorCancel();
            // Check Minimum time requirement prior to canceling
            /** @var BooklyLib\Entities\CustomerAppointment $ca */
            foreach ( BooklyLib\Entities\CustomerAppointment::query()->whereIn( 'id', $deleted )->find() as $ca ) {
                $appointment               = new BooklyLib\Entities\Appointment();
                if ( $appointment->load( $ca->getAppointmentId() ) ) {
                    $allow_cancel_time = strtotime( $appointment->getStartDate() ) - $minimum_time_prior_cancel;
                    if ( current_time( 'timestamp' ) > $allow_cancel_time ) {
                        $response['success']                     = false;
                        $response['errors']['time_prior_cancel'] = true;
                        $response['not_deleted']                 = $ca->getId();
                    }
                }
            }
        }

        $queue = array();

        if ( empty( $response['errors'] ) && empty( $response['warnings'] ) ) {
            /** @var BooklyLib\Entities\CustomerAppointment $ca */
            foreach ( BooklyLib\Entities\CustomerAppointment::query()->whereIn( 'id', $deleted )->find() as $ca ) {
                if ( ! $is_admin ) {
                    $ca->cancel();
                } else {
                    if ( $notification != 'no' ) {
                        switch ( $ca->getStatus() ) {
                            case BooklyLib\Entities\CustomerAppointment::STATUS_PENDING:
                            case BooklyLib\Entities\CustomerAppointment::STATUS_WAITLISTED:
                                $ca->setStatus( BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED );
                                break;
                            case BooklyLib\Entities\CustomerAppointment::STATUS_APPROVED:
                                $ca->setStatus( BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED );
                                break;
                            default:
                                $busy_statuses = (array) BooklyLib\Proxy\CustomStatuses::prepareBusyStatuses( array() );
                                if ( in_array( $ca->getStatus(), $busy_statuses ) ) {
                                    $ca->setStatus( BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED );
                                }
                        }
                        BooklyLib\Notifications\Booking\Sender::sendForCA( $ca, null, array(), false, $queue );
                    }
                    $ca->deleteCascade();
                }
            }

            foreach ( $schedule_dates as $index => $date ) {
                $staff_id = $package->getStaffId() === null ? $schedule[ $index ]['staff'] : $package->getStaffId();
                $appointment = BooklyLib\Entities\Appointment::query( 'a' )
                    ->leftJoin( 'CustomerAppointment', 'ca', 'ca.appointment_id = a.id' )
                    ->where( 'a.staff_id', $staff_id )
                    ->whereNotIn( 'ca.status', BooklyLib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                        BooklyLib\Entities\CustomerAppointment::STATUS_CANCELLED,
                        BooklyLib\Entities\CustomerAppointment::STATUS_REJECTED
                    ) ) )
                    ->whereLt( 'start_date', $date['end_date'] )
                    ->whereGt( 'end_date', $date['start_date'] )
                    ->findOne();

                if ( ! $appointment ) {
                    // Create appointment.
                    $appointment = new BooklyLib\Entities\Appointment();
                    $appointment
                        ->setLocationId( $package->getLocationId() )
                        ->setStaffId( $staff_id )
                        ->setService( $sub_service )
                        ->setStartDate( $date['start_date'] )
                        ->setEndDate( $date['end_date'] )
                        ->setInternalNote( $package->getInternalNote() )
                        ->save();
                }

                $customer_appointment = new BooklyLib\Entities\CustomerAppointment();
                $customer_appointment
                    ->setCustomerId( $package->getCustomerId() )
                    ->setAppointment( $appointment )
                    ->setPackage( $package )
                    ->setNumberOfPersons( 1 )
                    ->setTimeZone( $time_zone )
                    ->setTimeZoneOffset( $time_zone_offset )
                    ->setCreatedFrom( 'backend' )
                    ->setStatus( BooklyLib\Proxy\CustomerGroups::takeDefaultAppointmentStatus( get_option( 'bookly_gen_default_appointment_status' ), $customer->getGroupId() ) )
                    ->setCreatedAt( current_time( 'mysql' ) )
                    ->save();

                $response['alert_errors'] = BooklyLib\Proxy\Shared::syncOnlineMeeting( array(), $appointment, $sub_service );
                BooklyLib\Proxy\Pro::syncGoogleCalendarEvent( $appointment );
                BooklyLib\Proxy\OutlookCalendar::syncEvent( $appointment );

                if ( ! $is_admin ) {
                    BooklyLib\Notifications\Booking\Sender::sendForCA( $customer_appointment, $appointment );
                } elseif ( $notification != 'no' ) {
                    BooklyLib\Notifications\Booking\Sender::sendForCA( $customer_appointment, $appointment, array(), false, $queue );
                }
            }
        }
        update_user_meta( get_current_user_id(), 'bookly_packages_schedule_form_send_notifications', $notification );

        $response['queue'] = $queue;

        wp_send_json( $response );
    }
}
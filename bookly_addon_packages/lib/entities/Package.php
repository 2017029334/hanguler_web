<?php
namespace BooklyPackages\Lib\Entities;

use Bookly\Lib;

/**
 * Class Packages
 * @package BooklyPackages\Lib\Entities
 */
class Package extends Lib\Base\Entity
{
    /** @var  int */
    protected $location_id;
    /** @var  int */
    protected $staff_id;
    /** @var  int */
    protected $service_id;
    /** @var  int */
    protected $customer_id;
    /** @var  string */
    protected $internal_note;
    /** @var  string */
    protected $created_at;

    protected static $table = 'bookly_packages';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'location_id'   => array( 'format' => '%d' ),
        'staff_id'      => array( 'format' => '%s', 'reference' => array( 'entity' => 'Staff', 'namespace' => '\Bookly\Lib\Entities'  ) ),
        'service_id'    => array( 'format' => '%d', 'reference' => array( 'entity' => 'Service', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'customer_id'   => array( 'format' => '%d', 'reference' => array( 'entity' => 'Customer', 'namespace' => '\Bookly\Lib\Entities' ) ),
        'internal_note' => array( 'format' => '%s' ),
        'created_at'    => array( 'format' => '%s' )
    );

    /**
     * Get expired date
     *
     * @return null|\DateTime
     */
    public function getExpiredDate()
    {
        $service = Lib\Entities\Service::query()
            ->where( 'id', $this->getServiceId() )
            ->findOne();
        $res = Lib\Entities\CustomerAppointment::query( 'ca' )
            ->select( 'MIN(a.start_date) AS min_start_date' )
            ->innerJoin( 'Appointment', 'a', 'ca.appointment_id = a.id' )
            ->where( 'ca.package_id', $this->getId() )
            ->whereNotIn( 'ca.status', Lib\Proxy\CustomStatuses::prepareFreeStatuses( array(
                Lib\Entities\CustomerAppointment::STATUS_CANCELLED,
                Lib\Entities\CustomerAppointment::STATUS_REJECTED
            ) ) )
            ->fetchRow();

        return $res['min_start_date'] ? date_create( $res['min_start_date'] )->modify( sprintf( '+ %s days', $service->getPackageLifeTime() ) ) : null;
    }

    /**************************************************************************
     * Entity Fields Getters & Setters                                        *
     **************************************************************************/

    /**
     * Gets location_id
     *
     * @return int
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    /**
     * Sets location
     *
     * @param \BooklyLocations\Lib\Entities\Location $location
     * @return $this
     */
    public function setLocation( \BooklyLocations\Lib\Entities\Location $location )
    {
        return $this->setLocationId( $location->getId() );
    }

    /**
     * Sets location_id
     *
     * @param int $location_id
     * @return $this
     */
    public function setLocationId( $location_id )
    {
        $this->location_id = $location_id;

        return $this;
    }

    /**
     * Gets staff_id
     *
     * @return int
     */
    public function getStaffId()
    {
        return $this->staff_id;
    }

    /**
     * Sets staff
     *
     * @param Lib\Entities\Staff $staff
     * @return $this
     */
    public function setStaff( Lib\Entities\Staff $staff )
    {
        return $this->setStaffId( $staff->getId() );
    }

    /**
     * Sets staff_id
     *
     * @param int $staff_id
     * @return $this
     */
    public function setStaffId( $staff_id )
    {
        $this->staff_id = $staff_id;

        return $this;
    }

    /**
     * Gets service_id
     *
     * @return int
     */
    public function getServiceId()
    {
        return $this->service_id;
    }

    /**
     * Sets service
     *
     * @param Lib\Entities\Service $service
     * @return $this
     */
    public function setService( Lib\Entities\Service $service )
    {
        return $this->setServiceId( $service->getId() );
    }

    /**
     * Sets service_id
     *
     * @param int $service_id
     * @return $this
     */
    public function setServiceId( $service_id )
    {
        $this->service_id = $service_id;

        return $this;
    }

    /**
     * Gets customer_id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Sets customer
     *
     * @param Lib\Entities\Customer $customer
     * @return $this
     */
    public function setCustomer( Lib\Entities\Customer $customer )
    {
        return $this->setCustomerId( $customer->getId() );
    }

    /**
     * Sets customer_id
     *
     * @param int $customer_id
     * @return $this
     */
    public function setCustomerId( $customer_id )
    {
        $this->customer_id = $customer_id;

        return $this;
    }

    /**
     * Gets internal_note
     *
     * @return string
     */
    public function getInternalNote()
    {
        return $this->internal_note;
    }

    /**
     * Sets internal_note
     *
     * @param string $internal_note
     * @return $this
     */
    public function setInternalNote( $internal_note )
    {
        $this->internal_note = $internal_note;

        return $this;
    }

    /**
     * Gets created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Sets created_at
     *
     * @param string $created_at
     * @return $this
     */
    public function setCreatedAt( $created_at )
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**************************************************************************
     * Overridden Methods                                                     *
     **************************************************************************/
}
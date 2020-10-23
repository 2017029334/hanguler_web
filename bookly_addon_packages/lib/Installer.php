<?php
namespace BooklyPackages\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Installer
 * @package BooklyPackages\Lib
 */
class Installer extends Base\Installer
{
    /** @var array */
    protected $notifications = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Notifications email & sms.
        $default_settings = '{"status":"any","option":2,"services":{"any":"any","ids":[]},"offset_hours":2,"perform":"before","at_hour":9,"before_at_hour":18,"offset_before_hours":-24,"offset_bidirectional_hours":0}';
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => 'new_package',
            'name'        => __( 'Notification to customer about purchased package', 'bookly' ),
            'subject'     => __( 'Your package at {company_name}', 'bookly' ),
            'message'     => __( "Dear {client_name}.\n\nThis is a confirmation that you have booked {package_name}.\nWe are waiting you at {company_address}.\n\nThank you for choosing our company.\n\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => 'new_package',
            'name'        => __( 'Notification to staff member about purchased package', 'bookly' ),
            'subject'     => __( 'New package booking', 'bookly' ),
            'message'     => __( "Hello.\n\nYou have new package booking.\n\nPackage: {package_name}\n\nClient name: {client_name}\n\nClient phone: {client_phone}\n\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => 'new_package',
            'name'        => __( 'Notification to customer about purchased package', 'bookly' ),
            'subject'     => __( 'Your package at {company_name}', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nThis is a confirmation that you have booked {package_name}.\nWe are waiting you at {company_address}.\nThank you for choosing our company.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => 'new_package',
            'name'        => __( 'Notification to staff member about purchased package', 'bookly' ),
            'subject'     => __( 'New package booking', 'bookly' ),
            'message'     => __( "Hello.\nYou have new package booking.\nPackage: {package_name}\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => 'package_deleted',
            'name'        => __( 'Notification to customer about package deactivation', 'bookly' ),
            'subject'     => __( 'Service package is deactivated', 'bookly' ),
            'message'     => __( "Dear {client_name}.\n\nYour package of services {package_name} has been deactivated.\n\nThank you for choosing our company.\n\nIf you have any questions, please contact us.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'email',
            'type'        => 'package_deleted',
            'name'        => __( 'Notification to staff member about package deactivation', 'bookly' ),
            'subject'     => __( 'Service package is deactivated', 'bookly' ),
            'message'     => __( "Hello.\n\nThe following Package of services {package_name} has been deactivated.\n\nClient name: {client_name}\n\nClient phone: {client_phone}\n\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => 'package_deleted',
            'name'        => __( 'Notification to customer about package deactivation', 'bookly' ),
            'subject'     => __( 'Service package is deactivated', 'bookly' ),
            'message'     => __( "Dear {client_name}.\nYour package of services {package_name} has been deactivated.\nThank you for choosing our company.\nIf you have any questions, please contact us.\n{company_name}\n{company_phone}\n{company_website}", 'bookly' ),
            'active'      => 1,
            'to_customer' => 1,
            'settings'    => $default_settings,
        );
        $this->notifications[] = array(
            'gateway'     => 'sms',
            'type'        => 'package_deleted',
            'name'        => __( 'Notification to staff member about package deactivation', 'bookly' ),
            'subject'     => __( 'Service package is deactivated', 'bookly' ),
            'message'     => __( "Hello.\nThe following Package of services {package_name} has been deactivated.\nClient name: {client_name}\nClient phone: {client_phone}\nClient email: {client_email}", 'bookly' ),
            'active'      => 1,
            'to_staff'    => 1,
            'settings'    => $default_settings,
        );
    }

    public function createTables()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $charset_collate = $wpdb->has_cap( 'collation' )
            ? $wpdb->get_charset_collate()
            : 'DEFAULT CHARACTER SET = utf8 COLLATE = utf8_general_ci';

        $wpdb->query(
            'CREATE TABLE IF NOT EXISTS `' . Entities\Package::getTableName() . '` (
                `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                `location_id`       INT UNSIGNED DEFAULT NULL,
                `staff_id`          INT UNSIGNED DEFAULT NULL,
                `service_id`        INT UNSIGNED NOT NULL,
                `customer_id`       INT UNSIGNED NOT NULL,
                `internal_note`     TEXT DEFAULT NULL,
                `created_at`        DATETIME NOT NULL,
                CONSTRAINT
                    FOREIGN KEY (staff_id)
                    REFERENCES ' . BooklyLib\Entities\Staff::getTableName() . '(id)
                    ON DELETE SET NULL
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (service_id)
                    REFERENCES ' . BooklyLib\Entities\Service::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE,
                CONSTRAINT
                    FOREIGN KEY (customer_id)
                    REFERENCES ' . BooklyLib\Entities\Customer::getTableName() . '(id)
                    ON DELETE CASCADE
                    ON UPDATE CASCADE
            ) ENGINE = INNODB
            ' . $charset_collate
        );

        $wpdb->query(
            'ALTER TABLE `' . BooklyLib\Entities\CustomerAppointment::getTableName() . '` 
             ADD CONSTRAINT 
                FOREIGN KEY (package_id)
                REFERENCES ' . Entities\Package::getTableName() . '(id)
                ON DELETE SET NULL
                ON UPDATE CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function loadData()
    {
        parent::loadData();

        // Insert notifications.
        foreach ( $this->notifications as $data ) {
            $notification = new BooklyLib\Entities\Notification();
            $notification->setFields( $data )->save();
        }
    }

    /**
     * @inheritdoc
     */
    public function removeData()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        parent::removeData();

        $ca_table = $wpdb->prefix . 'bookly_customer_appointments';
        $result   = $wpdb->query( "SELECT table_name FROM information_schema.tables WHERE table_name = '$ca_table' AND TABLE_SCHEMA=SCHEMA()" );
        if ( $result == 1 ) {
            @$wpdb->query( "UPDATE `{$ca_table}` SET package_id = null" );
        }

        // Remove user meta.
        $meta_names = array(
            $this->getPrefix() . 'form_send_notifications',
            $this->getPrefix() . 'schedule_form_send_notifications',
            $this->getPrefix() . 'table_settings',
            $this->getPrefix() . 'hide_staff_services_tip',
        );
        $wpdb->query( $wpdb->prepare( sprintf( 'DELETE FROM `' . $wpdb->usermeta . '` WHERE meta_key IN (%s)',
            implode( ', ', array_fill( 0, count( $meta_names ), '%s' ) ) ), $meta_names ) );

        $notifications_table = $wpdb->prefix . 'bookly_notifications';
        $result              = $wpdb->query( "SELECT table_name FROM information_schema.tables WHERE table_name = '$notifications_table' AND TABLE_SCHEMA=SCHEMA()" );
        if ( $result == 1 ) {
            foreach ( $this->notifications as $notification ) {
                @$wpdb->query( "DELETE FROM `{$notifications_table}` WHERE `type` = '{$notification['type']}'" );
            }
        }
    }
}
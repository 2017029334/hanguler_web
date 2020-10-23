<?php
namespace BooklyPackages\Lib;

use Bookly\Lib as BooklyLib;

/**
 * Class Updates
 * @package BooklyPackages\Lib
 */
class Updater extends BooklyLib\Base\Updater
{
    function update_3_5()
    {
        $this->alterTables( array(
            'bookly_packages' => array(
                'ALTER TABLE `%s` CHANGE COLUMN `created` `created_at` DATETIME NOT NULL',
            ),
        ) );
    }

    function update_2_9()
    {
        global $wpdb;

        $meta_names = array(
            'bookly_packages_filter_packages_list',
        );
        $wpdb->query( $wpdb->prepare( sprintf( 'DELETE FROM `' . $wpdb->usermeta . '` WHERE meta_key IN (%s)',
            implode( ', ', array_fill( 0, count( $meta_names ), '%s' ) ) ), $meta_names ) );
    }

    public function update_2_5()
    {
        $this->upgradeCharsetCollate( array(
            'bookly_packages',
        ) );
    }

    function update_2_1()
    {
        /** @global \wpdb $wpdb */
        global $wpdb;

        $self = $this;
        $notifications_table  = $this->getTableName( 'bookly_notifications' );
        $notifications = array(
            'client_package_deleted'   => array( 'type' => 'package_deleted', 'name' => __( 'Notification to customer about package deactivation', 'bookly' ) ),
            'client_package_purchased' => array( 'type' => 'new_package', 'name' => __( 'Notification to customer about purchased package', 'bookly' ) ),
            'staff_package_deleted'    => array( 'type' => 'package_deleted', 'name' => __( 'Notification to staff member about package deactivation', 'bookly' ) ),
            'staff_package_purchased'  => array( 'type' => 'new_package', 'name' => __( 'Notification to staff member about purchased package', 'bookly' ) ),
        );

        // Set name
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-1', function () use ( $self, $wpdb, $notifications_table, $notifications ) {
            if ( ! $self->existsColumn( 'bookly_notifications', 'name' ) ) {
                $self->alterTables( array(
                    'bookly_notifications' => array(
                        'ALTER TABLE `%s` ADD COLUMN `name` VARCHAR(255) NOT NULL DEFAULT "" AFTER `active`',
                    ),
                ) );
            }

            $update_name = 'UPDATE `' . $notifications_table . '` SET `name` = %s WHERE `type` = %s AND `name` = \'\'';
            foreach ( $notifications as $type => $value ) {
                $wpdb->query( $wpdb->prepare( $update_name, $value['name'], $type ) );
                switch ( substr( $type, 0, 6 ) ) {
                    case 'staff_':
                        $wpdb->query( sprintf( 'UPDATE `%s` SET `to_staff` = 1 WHERE `type` = "%s"', $notifications_table, $type ) );
                        break;
                    case 'client':
                        $wpdb->query( sprintf( 'UPDATE `%s` SET `to_customer` = 1 WHERE `type` = "%s"', $notifications_table, $type ) );
                        break;
                }
            }
        } );

        // WPML
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-2', function () use ( $self, $wpdb, $notifications_table, $notifications ) {
            $records = $wpdb->get_results( $wpdb->prepare( 'SELECT id, `type`, `gateway` FROM `' . $notifications_table . '` WHERE COALESCE( `settings`, \'[]\' ) = \'[]\' AND `type` IN (' . implode( ', ', array_fill( 0, count( $notifications ), '%s' ) ) . ')', array_keys( $notifications ) ), ARRAY_A );
            $strings = array();
            foreach ( $records as $record ) {
                $type = $record['type'];
                if ( isset( $notifications[ $type ]['type'] ) && $type != $notifications[ $type ]['type'] ) {
                    $key   = sprintf( '%s_%s_%d', $record['gateway'], $type, $record['id'] );
                    $value = sprintf( '%s_%s_%d', $record['gateway'], $notifications[ $type ]['type'], $record['id'] );
                    $strings[ $key ] = $value;
                    if ( $record['gateway'] == 'email' ) {
                        $strings[ $key . '_subject' ] = $value . '_subject';
                    }
                }
            }
            $self->renameL10nStrings( $strings, false );
        } );

        // Add settings for notifications
        $disposable_options[] = $this->disposable( __FUNCTION__ . '-3', function () use ( $wpdb, $notifications_table, $notifications ) {
            $update_settings  = 'UPDATE `' . $notifications_table . '` SET `type` = %s, `settings`= %s WHERE id = %d';
            $default_settings = '{"status":"any","option":2,"services":{"any":"any","ids":[]},"offset_hours":2,"perform":"before","at_hour":9,"before_at_hour":18,"offset_before_hours":-24,"offset_bidirectional_hours":0}';
            $records = $wpdb->get_results( $wpdb->prepare( 'SELECT id, `type`, `gateway`, `message`, `subject` FROM `' . $notifications_table . '` WHERE COALESCE( `settings`, \'[]\' ) = \'[]\' AND `type` IN (' . implode( ', ', array_fill( 0, count( $notifications ), '%s' ) ) . ')', array_keys( $notifications ) ), ARRAY_A );
            foreach ( $records as $record ) {
                if ( $notifications[ $record['type'] ]['type'] == $record['type'] ) {
                    continue;
                }
                $new_type = $notifications[ $record['type'] ]['type'];
                $wpdb->query( $wpdb->prepare( $update_settings, $new_type, $default_settings, $record['id'] ) );
            }
        } );

        foreach ( $disposable_options as $option_name ) {
            delete_option( $option_name );
        }
    }

    public function update_1_6()
    {
        global $wpdb;

        // Rename tables.
        $tables = array(
            'packages',
        );
        $query = 'RENAME TABLE ';
        foreach ( $tables as $table ) {
            $query .= sprintf( '`%s` TO `%s`, ', $this->getTableName( 'ab_' . $table ), $this->getTableName( 'bookly_' . $table ) );
        }
        $query = substr( $query, 0, -2 );
        $wpdb->query( $query );

        delete_option( 'bookly_packages_enabled' );
    }

    function update_1_2()
    {
        $this->alterTables( array(
            'ab_packages' => array(
                'ALTER TABLE `%s` CHANGE COLUMN `staff_id` `staff_id` INT UNSIGNED DEFAULT NULL',
                'ALTER TABLE `%s` DROP FOREIGN KEY `staff_id`',
                'ALTER TABLE `%s` ADD CONSTRAINT FOREIGN KEY (staff_id) REFERENCES ' . BooklyLib\Entities\Staff::getTableName() . '(id) ON DELETE SET NULL ON UPDATE CASCADE',
            ),
        ) );
    }
}
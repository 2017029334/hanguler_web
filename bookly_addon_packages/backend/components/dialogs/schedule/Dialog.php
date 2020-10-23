<?php
namespace BooklyPackages\Backend\Components\Dialogs\Schedule;

use Bookly\Lib as BooklyLib;

/**
 * Class Dialog
 * @package BooklyPackages\Backend\Components\Dialogs\Schedule
 */
class Dialog extends BooklyLib\Base\Component
{
    public static function render()
    {
        self::enqueueStyles( array(
            'bookly' => array(
                'frontend/resources/css/ladda.min.css',
                'backend/resources/bootstrap/css/bootstrap.min.css',
            ),
        ) );

        self::enqueueScripts( array(
            'bookly' => array(
                'backend/resources/js/alert.js'                   => array( 'jquery' ),
                'backend/resources/js/angular.min.js'             => array( 'jquery' ),
                'backend/resources/js/angular-daterangepicker.js' => array( 'bookly-angular.min.js' ),
                'backend/resources/js/moment.min.js'              => array( 'jquery' ),
                'frontend/resources/js/spin.min.js'               => array( 'jquery' ),
                'frontend/resources/js/ladda.min.js'              => array( 'jquery' ),
            ),
            'module' => array(
                'js/package-schedule-dialog.js' => array( 'bookly-angular-daterangepicker.js', 'bookly-alert.js' ),
            ),
        ) );

        wp_localize_script( 'bookly-package-schedule-dialog.js', 'BooklyL10nPackageScheduleDialog', array(
            'csrf_token'    => BooklyLib\Utils\Common::getCsrfToken(),
            'mjsDateFormat' => BooklyLib\Utils\DateTime::convertFormat( 'date', BooklyLib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'mjsTimeFormat' => BooklyLib\Utils\DateTime::convertFormat( 'time', BooklyLib\Utils\DateTime::FORMAT_MOMENT_JS ),
            'datePicker' => BooklyLib\Utils\DateTime::datePickerOptions( array(
                'minDate' => BooklyLib\Utils\Common::isCurrentUserAdmin() ? null : 0,
                'maxDate' => BooklyLib\Utils\Common::isCurrentUserAdmin() ? null : BooklyLib\Config::getMaximumAvailableDaysForBooking(),
            ) ),
            'ajaxurl'       => admin_url( 'admin-ajax.php' ),
        ) );

        self::renderTemplate( 'modal' );
    }
}
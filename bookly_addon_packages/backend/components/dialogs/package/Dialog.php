<?php
namespace BooklyPackages\Backend\Components\Dialogs\Package;

use Bookly\Lib as BooklyLib;

/**
 * Class Dialog
 * @package BooklyPackages\Backend\Components\Dialogs\Package
 */
class Dialog extends BooklyLib\Base\Component
{
    public static function render()
    {
        self::enqueueStyles( array(
            'bookly' => array(
                'frontend/resources/css/ladda.min.css',
            ),
        ) );

        self::enqueueScripts( array(
            'bookly' => array(
                'backend/resources/js/angular.min.js'           => array( 'jquery' ),
                'backend/resources/js/moment.min.js'            => array( 'jquery' ),
                'backend/resources/js/select2.min.js'           => array( 'jquery' ),
                'frontend/resources/js/spin.min.js'             => array( 'jquery' ),
                'frontend/resources/js/ladda.min.js'            => array( 'jquery' ),
            ),
            'module' => array(
                'js/package_dialog.js' => array( 'bookly-angular.min.js', 'bookly-select2.min.js' ),
            ),
        ) );

        self::renderTemplate( 'modal' );
    }
}
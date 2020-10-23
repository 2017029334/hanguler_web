<?php
namespace BooklyPackages\Backend\Components\Dialogs\Staff\Edit\ProxyProviders;

use Bookly\Backend\Modules\Staff;
use Bookly\Lib as BooklyLib;
use Bookly\Backend\Components\Dialogs\Staff\Edit\Proxy;

/**
 * Class Local
 *
 * @package BooklyPackages\Backend\Components\Dialogs\Staff\Edit\ProxyProviders
 */
class Local extends Proxy\Packages
{
    /**
     * @inheritDoc
     */
    public static function renderStaffServicesTip()
    {
        self::renderTemplate( 'modal' );
    }
}
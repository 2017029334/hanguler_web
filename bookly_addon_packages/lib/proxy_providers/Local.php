<?php
namespace BooklyPackages\Lib\ProxyProviders;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Backend\Modules\Packages\Page;

/**
 * Class Local
 * @package BooklyPackages\Lib\Shared
 */
class Local extends BooklyLib\Proxy\Packages
{
    /**
     * @inheritDoc
     */
    public static function addBooklyMenuItem()
    {
        $packages =  __( 'Packages', 'bookly' );

        add_submenu_page( 'bookly-menu', $packages, $packages, 'read',
            Page::pageSlug(), function () { Page::render(); } );
    }
}
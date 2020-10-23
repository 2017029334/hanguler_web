<?php
namespace BooklyPackages\Lib;

use Bookly\Lib as BooklyLib;
use BooklyPackages\Backend;
use BooklyPackages\Frontend;

/**
 * Class Plugin
 * @package BooklyPackages\Lib
 */
abstract class Plugin extends BooklyLib\Base\Plugin
{
    protected static $prefix;
    protected static $title;
    protected static $version;
    protected static $slug;
    protected static $directory;
    protected static $main_file;
    protected static $basename;
    protected static $text_domain;
    protected static $root_namespace;
    protected static $embedded;

    /**
     * @inheritdoc
     */
    protected static function init()
    {
        // Init ajax.
        Backend\Components\Dialogs\Package\Ajax::init();
        Backend\Components\Dialogs\Schedule\Ajax::init();
        Backend\Components\Dialogs\Staff\Ajax::init();
        Backend\Components\Gutenberg\PackagesList\Block::init();
        Backend\Modules\Packages\Ajax::init();


        // Init proxy.
        Backend\Components\Dialogs\Appointment\Edit\ProxyProviders\Shared::init();
        Backend\Components\Dialogs\Staff\Edit\ProxyProviders\Local::init();
        Backend\Components\TinyMce\ProxyProviders\Shared::init();
        Backend\Modules\Appointments\ProxyProviders\Shared::init();
        Backend\Modules\Calendar\ProxyProviders\Shared::init();
        Backend\Modules\Customers\ProxyProviders\Shared::init();
        Backend\Modules\Notifications\ProxyProviders\Shared::init();
        Backend\Modules\Services\ProxyProviders\Local::init();
        Backend\Modules\Services\ProxyProviders\Shared::init();
        Notifications\Assets\Test\ProxyProviders\Shared::init();
        ProxyProviders\Local::init();
        ProxyProviders\Shared::init();

        if ( ! is_admin() ) {
            // Init short code.
            Frontend\Modules\CustomerPackages\ShortCode::init();
        }
    }
}
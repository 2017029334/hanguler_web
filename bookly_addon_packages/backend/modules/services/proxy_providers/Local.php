<?php
namespace BooklyPackages\Backend\Modules\Services\ProxyProviders;

use Bookly\Backend\Modules\Services\Proxy;

/**
 * Class Local
 * @package BooklyPackages\Backend\Modules\Services\ProxyProviders
 */
class Local extends Proxy\Packages
{
    /**
     * @inheritdoc
     */
    public static function renderSubForm( array $service, array $service_collection )
    {
        self::renderTemplate( 'sub_form', compact( 'service', 'service_collection' ) );
    }
}
<?php
namespace BooklyPackages\Backend\Modules\Customers\ProxyProviders;

use Bookly\Backend\Modules\Customers\Proxy;
use BooklyPackages\Lib;

/**
 * Class Shared
 * @package BooklyPackages\Backend\Modules\Customers\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function mergeCustomers( $target_id, array $ids )
    {
        Lib\Entities\Package::query()
            ->update()
            ->set( 'customer_id', $target_id )
            ->whereIn( 'customer_id', $ids )
            ->execute();
    }
}
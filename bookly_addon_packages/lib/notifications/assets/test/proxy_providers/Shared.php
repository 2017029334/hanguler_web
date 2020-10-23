<?php
namespace BooklyPackages\Lib\Notifications\Assets\Test\ProxyProviders;

use Bookly\Lib\Notifications\Assets\Test\Codes;
use Bookly\Lib\Notifications\Assets\Test\Proxy;

/**
 * Class Shared
 * @package BooklyPackages\Lib\Notifications\Assets\Test\ProxyProviders
 */
abstract class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function prepareReplaceCodes( array $replace_codes, Codes $codes, $format )
    {
        $replace_codes['{package_name}']      = 'Package Name';
        $replace_codes['{package_size}']      = '10';
        $replace_codes['{package_life_time}'] = '30';
        $replace_codes['{package_price}']     = '90';

        return $replace_codes;
    }
}
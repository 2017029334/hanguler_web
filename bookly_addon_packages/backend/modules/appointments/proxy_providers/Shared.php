<?php
namespace BooklyPackages\Backend\Modules\Appointments\ProxyProviders;

use Bookly\Backend\Modules\Calendar\Proxy;
use BooklyPackages\Backend\Components;

/**
 * Class Shared
 * @package BooklyRecurringAppointments\Backend\Modules\Appointments\ProxyProviders
 */
class Shared extends Proxy\Shared
{
    /**
     * @inheritdoc
     */
    public static function renderAddOnsComponents()
    {
        Components\Dialogs\Schedule\Dialog::render();
    }
}
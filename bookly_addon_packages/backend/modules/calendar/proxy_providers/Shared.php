<?php
namespace BooklyPackages\Backend\Modules\Calendar\ProxyProviders;

use Bookly\Backend\Modules\Appointments\Proxy;
use BooklyPackages\Backend\Components;

/**
 * Class Shared
 * @package BooklyRecurringAppointments\Backend\Modules\Calendar\ProxyProviders
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
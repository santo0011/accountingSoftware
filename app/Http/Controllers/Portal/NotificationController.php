<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\NotificationController as BaseNotificationController;

class NotificationController extends BaseNotificationController
{
    protected function area(): string
    {
        return 'portal';
    }
}

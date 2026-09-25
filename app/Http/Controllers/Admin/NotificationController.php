<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\NotificationController as BaseNotificationController;

class NotificationController extends BaseNotificationController
{
    protected function area(): string
    {
        return 'admin';
    }
}

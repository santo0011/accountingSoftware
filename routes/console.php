<?php

use App\Services\ComplianceService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
| Compliance automation — refresh Upcoming / Due Soon / Overdue and send reminders.
| Run the scheduler every minute in production:
|   Linux:   * * * * * php /path/to/artisan schedule:run
|   Windows: Task Scheduler → php artisan schedule:run (every minute)
*/
Artisan::command('compliance:run', function (ComplianceService $compliance) {
    $changed = $compliance->refreshStatuses();
    $sent = $compliance->sendReminders();

    $this->info("Compliance statuses updated: {$changed}. Reminders sent: {$sent}.");
})->purpose('Refresh compliance statuses and send due-date reminders');

Schedule::command('compliance:run')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('queue:prune-failed --hours=168')->weekly();
Schedule::command('activitylog:clean')->monthly();

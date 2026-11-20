<?php

use Illuminate\Support\Facades\Schedule;

// Requires one cron entry on the server:  * * * * * php /path/to/artisan schedule:run
Schedule::command('golpobari:expire-pages')->hourly()->withoutOverlapping();
Schedule::command('golpobari:cleanup-drafts')->dailyAt('03:30');
// Future: Schedule::command('golpobari:expiry-reminders')->dailyAt('10:00');

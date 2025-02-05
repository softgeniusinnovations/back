<?php
return [
    'excluded_routes' => [
        'win.cron',
        'lose.cron',
        'refund.cron',
    ],
    'excluded_models' => [
        \App\Models\Log::class,
    ],
    'excluded_operations' => [

    ],
];

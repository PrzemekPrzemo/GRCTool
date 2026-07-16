<?php

return [
    'webhook_url' => env('SLACK_WEBHOOK_URL', ''),
    'channel' => env('SLACK_CHANNEL', '#security-alerts'),
    'enabled' => env('SLACK_ENABLED', false),
];

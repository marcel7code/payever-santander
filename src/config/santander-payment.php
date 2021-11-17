<?php

// Using way in classes (config('santander_payment.{name}'))

return [
    'enabled' => env("SANTANDER_ENABLED", false),
    'testing' => env("SANTANDER_TESTING", true),
    'client_id' => env("SANTANDER_CLIENT_ID"),
    'client_secret' => env("SANTANDER_CLIENT_SECRET"),
];

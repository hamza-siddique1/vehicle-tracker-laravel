<?php
// config/ndtc.php

return [
    'base_url' => env('NDTC_BASE_URL',
        'https://s10-sit-ws-s10-orderintegration-services.champtest.net'
    ),

    'auth_url' => env('NDTC_AUTH_URL',
        'https://s10-sit-keycloak.champtest.net/auth/realms/Core/protocol/openid-connect/token'
    ),

    'client_id'      => env('NDTC_CLIENT_ID'),
    'client_secret'  => env('NDTC_CLIENT_SECRET'),
    'webhook_secret' => env('NDTC_WEBHOOK_SECRET'),
    'nrb_number'     => env('NDTC_NRB_NUMBER'), // without NRB prefix e.g. "00247"
];

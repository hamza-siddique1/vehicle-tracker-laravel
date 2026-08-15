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
    'nrb_number'     => env('NDTC_NRB_NUMBER'),
    'acquiring_name'      => env('NDTC_ACQUIRING_NAME'),
    'acquiring_address1'  => env('NDTC_ACQUIRING_ADDRESS1'),
    'acquiring_city'      => env('NDTC_ACQUIRING_CITY'),
    'acquiring_state'     => env('NDTC_ACQUIRING_STATE'),
    'acquiring_zip'       => env('NDTC_ACQUIRING_ZIP'),
];

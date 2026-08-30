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

    'transaction_types' => [
        'TNL'  => 'Transfer No Lien',
        'TWL'  => 'Transfer With Lien',
        'TWEL' => 'Transfer With Electronic Lien',
        'DNT'  => 'Dealer No Title',
        'EOL'  => 'End of Lease',
        'RT'   => 'Recovered Theft',
        'RWT'  => 'Repossession With Title',
        'RWUT' => 'Repossession With Unfiled Title',
        'RWOT' => 'Repossession Without Title',
        'SNL'  => 'Salvage No Lien',
        'SNT'  => 'Salvage No Title',
        'SWL'  => 'Salvage With Lien',
        'SPR'  => 'Single Party Retitling',
        'SPS'  => 'Single Party Salvage',
        'UTNL' => 'Unrecovered Theft No Lien',
        'UTNT' => 'Unrecovered Theft No Title',
        'UTWL' => 'Unrecovered Theft With Lien',
    ],

    'no_title_transaction_types' => ['DNT', 'EOL', 'RWUT', 'RWOT'],
];

<?php

return [
    'IS_PRODUCTION' => 0,
    'DEMARCATION_DISTRICTS' => [
        '17' => 'ডিব্ৰুগড়',
        '08' => 'দৰং',
        '07' => 'কামৰূপ',
        '25' => 'ধেমাজি'
    ],
    'LANDHUB_BASE_URL' => 'https://landhub.assam.gov.in/apidemo/index.php/',
    'APP_FOLDER_NAME'  =>  "demarcation/",
    'SINGLESIGN_LINK' => "http://localhost/singlesignResurvey/index.php",
    'BARAK_VALLEY' => ['21', '22', '23'],
    'LANDHUB_BASE_URL_NEW' => 'https://landhub.assam.gov.in/api/index.php/',
    'LANDHUB_APIKEY' => env('LANDHUB_APIKEY'),
    'MAP_API' => 'https://landhub.assam.gov.in/api/index.php/BhunakshaApiController/getVillageGeoJson',
    'LANDHUB_DEMO_PVT_KEY' => env('LANDHUB_DEMO_PVT_KEY'),
    'BHUNAKSHA_PRIVATE_KEY' => env('BHUNAKSHA_PRIVATE_KEY'),
    'DATA_PRIVATE_KEY' => env('JWT_SECRET'),
];
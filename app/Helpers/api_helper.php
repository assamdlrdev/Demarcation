<?php

function callApi($url, $method, $data = null)
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        // CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_VERBOSE => 1,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded',
        ),
    ));

    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

    curl_close($curl);
    
    if ($httpcode != 200) {
        // log_message("error", 'API FAIL');
        return [
            'status' => 'n',
            'data' => $response,
            'error_code' => $httpcode
        ];
    }

    return [
        'status' => 'y',
        'data' => $response
    ];
}

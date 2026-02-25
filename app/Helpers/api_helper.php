<?php
use Illuminate\Support\Facades\Log;

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

    Log::info("API REQUEST", [
        'url' => $url,
        'method' => $method,
        'headers' => [
            'Content-Type: application/x-www-form-urlencoded'
        ],
        'data' => $data
    ]);

    // Check if the cURL request was successful or failed
    if(curl_errno($curl)) {
        $error_msg = curl_error($curl);
        Log::error("cURL Error: " . $error_msg);  // Log any cURL errors
    }

    curl_close($curl);
    Log::info("API RESPONSE: ", [
        'http_code' => $httpcode,
        'response' => $response
    ]);
    
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


function callLandhubAPIMerge($method, $url, $data)
{
    $curl = curl_init();
    $data['apikey'] = config('constants.LANDHUB_APIKEY');
    curl_setopt_array($curl, array(
        CURLOPT_URL => config('constants.LANDHUB_BASE_URL_NEW') . $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/x-www-form-urlencoded'
        ),
    ));
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        
    if (curl_errno($curl)) {
        $error = curl_error($curl);
        curl_close($curl);
        // log or handle error
        return  null;
    }

    if ($httpcode != 200) {
        curl_close($curl);
        return null;
    }
    curl_close($curl);
    return json_decode($response);
}
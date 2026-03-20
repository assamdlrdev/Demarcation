<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function jwtencode($payload) {
    $token = JWT::encode($payload, config('constants.DATA_PRIVATE_KEY'), 'HS256');
    return $token;
}

function jwtdecode($token) {
    $decodedToken = JWT::decode(
        $token,
        new Key(config('constants.DATA_PRIVATE_KEY'), 'HS256')
    );
    if(!isset($decodedToken) || empty($decodedToken)) {
        return false;
    }

    return $decodedToken;
}


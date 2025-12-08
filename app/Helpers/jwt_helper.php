<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function jwtencode($data) {
    
}

function jwtdecode($token) {
    $decodedToken = JWT::decode(
        $token,
        new Key(env('JWT_SECRET'), 'HS256')
    );
    if(!isset($decodedToken) || empty($decodedToken)) {
        return false;
    }

    return $decodedToken;
}
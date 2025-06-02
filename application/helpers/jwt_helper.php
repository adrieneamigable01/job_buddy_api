<?php
require './vendor/autoload.php';
use \Firebase\JWT\JWT;

function generate_jwt($payload, $key) {
    return JWT::encode($payload, $key);
}

function decode_jwt($jwt, $key) {
   
    try {
        return JWT::decode($jwt, $key, array('HS256'));
    } catch (Exception $e) {
        return null;
    }
}
?>
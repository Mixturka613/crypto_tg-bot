<?php

ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

// include handler in project
include_once __DIR__ . "/handler.php";

//  Realization Webhook
$data = file_get_contents("php://input");
$arr = json_decode($data, true);
// file_put_contents("logs/data.txt", print_r( $arr , true ) . "\n" , FILE_APPEND);

// set tocken
$tocken = "";

// Handler for messages
$handler = new Handler($tocken);

// Procesing messages
$handler -> responseToChanges($arr);
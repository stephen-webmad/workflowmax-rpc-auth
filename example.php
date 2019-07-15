<?php
/*

Workflowmax.com RPC connection authenticator, version 0.1

Copyright (c) 2007-2019 Webmad Ltd.
Author: Stephen Price
Licensed under the MIT license.

*/

include_once "rpc-auth.php";

// ini_set('display_errors',1);
// error_reporting(E_ALL);

$user="";   //$_POST['user'] or $_SESSION['user'] or whatever - you'll work it out - this is the email address used at sign-in
$pass="";   //$_POST['password'] or $_SESSION['password'] or whatever - you'll work it out - this is the password used at sign-in - DON'T hard code it please

$cookie_jar = tempnam(sys_get_temp_dir(),"cookie_");    //this can be used for future curl requests

// fetch the authenticated cokie to start using the RPC API and do stuff the public api just can't do (like mark time as billable or not etc).
$authcookie = rpc_auth($user,$pass,$cookie_jar);

//now we can use it to do things. ie:
//are there any existing live timers?
$ch = curl_init();

curl_setopt_array($ch, array(
    CURLOPT_URL => "https://my.workflowmax.com/rpc-bridge",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => '{"id":1,"jsonrpc":"2.0","method":"callApi","parameters":{"endpoint":"/timer-v1/days/'.date("Y-m-d").'","verb":"GET","body":{},"headers":{}}}',
    CURLOPT_HTTPHEADER => array(
        "Content-Type: application/json",
        "Cookie: $authcookie;",             //add our auth cookie
        "Origin: https://edge.xero.com",    //need this or else asks for it
    ),
));

$response = curl_exec($ch);
$err = curl_error($ch);

curl_close($ch);
$data = json_decode($response,ARRAY_A);

print_r($data);


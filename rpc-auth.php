<?php 
/*

Workflowmax.com RPC connection authenticator, version 0.1

Copyright (c) 2007-2019 Webmad Ltd.
Author: Stephen Price
Licensed under the MIT license.

*/

function rpc_auth($user,$pass,$cookie_jar){
    
    $ch = curl_init();
    file_put_contents($cookie_jar,"");  //reset the auth
    
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_jar);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_jar);
    curl_setopt($ch, CURLOPT_URL,"https://my.workflowmax.com/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36"); //make it look like we are someone with a browser
    $server_output = curl_exec ($ch);
    
    $formauth = substr($server_output,strpos($server_output,'__RequestVerificationToken"'));
    $formauth = substr($formauth,strpos($formauth,'value="')+7);
    $formauth = substr($formauth,0,strpos($formauth,'"'));
    
    $post = array(
        "Code"=>$_SESSION['user'],
        "__RequestVerificationToken"=>$formauth,
        "OffsetFromUtc"=>"-780",    //this is for NZ (+13 hours in winter) - may need altered for your timezone as applicable
        "Login"=>""
    );
    curl_setopt($ch, CURLOPT_URL,"https://practicemanager.xero.com/");
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post));
    $server_output = curl_exec($ch);
    
    curl_setopt($ch, CURLOPT_URL, "https://login.xero.com/openid/authorize?scope=openid&client_id=xero_practice_practicemanager_prod&redirect_uri=https%3a%2f%2fpracticemanager.xero.com%2fAccess%2fLogon%2fCompleteXeroOpenIdAuth%2f&username=".$user);
    curl_setopt($ch, CURLOPT_POST, 0);
    $server_output = curl_exec($ch);
    
    curl_setopt($ch, CURLOPT_URL,"https://login.xero.com/?scope=openid&client_id=xero_practice_practicemanager_prod&redirect_uri=https%3A%2F%2Fpracticemanager.xero.com%2FAccess%2FLogon%2FCompleteXeroOpenIdAuth%2F&username=".urlencode($user));
    curl_setopt($ch, CURLOPT_POST, 0);
    $server_output = curl_exec ($ch);
    
    $formauth = substr($server_output,strpos($server_output,'__RequestVerificationToken"'));
    $formauth = substr($formauth,strpos($formauth,'value="')+7);
    $formauth = substr($formauth,0,strpos($formauth,'"'));
    
    $post = array(
        "fragment"=>'',
        'userName'=>$user,
        'password'=>$pass,
        "__RequestVerificationToken"=>$formauth
    );
    curl_setopt($ch, CURLOPT_URL,"https://login.xero.com/?scope=openid&client_id=xero_practice_practicemanager_prod&redirect_uri=https%3A%2F%2Fpracticemanager.xero.com%2FAccess%2FLogon%2FCompleteXeroOpenIdAuth%2F&username=".urlencode($user));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,http_build_query($post));
    $server_output = curl_exec($ch);
    curl_close($ch);
    
    $output = substr($server_output,strpos($server_output,"Location: /my/overview.aspx"));
    $auth = explode("Set-Cookie: WorkFlowMax=",$output);
    $auth = substr($auth[1],0,strpos($auth[1],";"));
    
    //print_r($server_output);
    
    return "WorkFlowMax=".$auth;
}
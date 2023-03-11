<?php
//*************************************************************************************************
// FileName : oAuthCallback.php
// FilePath : pushHandler/Digikey/
// Author   : Christian Marty
// Date		: 06.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	global $enableDigikey;
	global $digikeyApiPath; 
	global $digikeyClientId;
	global $digikeyClientSecret;
	global $digikeyCallbackPath;
	
	$code = $_GET["code"];
	$state = $_GET["state"];
	
	echo var_dump($code);
	
	$digikeyLastError = $_GET["code"];
	
	$url   = $digikeyApiPath.'v1/oauth2/token';
	$post  = "code=".$code;
	$post .= "&client_id=".$digikeyClientId;
	$post .= "&client_secret=".$digikeyClientSecret;
	$post .= "&redirect_uri=".urlencode($digikeyCallbackPath);
	$post .= "&grant_type=authorization_code";
	
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
	curl_setopt($curl, CURLOPT_POST, 1);
	curl_setopt($curl, CURLOPT_POSTFIELDS,$post);

    $result = curl_exec($curl);
	
	curl_close($curl);

	$digikeyAuthData = json_decode($result,true);
	
	echo var_dump($digikeyAuthData);
	
	session_start();
	$_SESSION['digikeyAccessToken'] = $digikeyAuthData["access_token"];
	$_SESSION['digikeyRefreshToken'] = $digikeyAuthData["refresh_token"];
	$_SESSION['digikeyAccessTokenExpire'] = time()+intval($digikeyAuthData['expires_in']);
	$_SESSION['digikeyRefreshTokenExpire'] = time()+intval($digikeyAuthData['refresh_token_expires_in']);
	
	$_SESSION['digikeyLastError'] = $digikeyLastError;
	
	echo "done";
}

?>
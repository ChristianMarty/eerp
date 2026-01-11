<?php
//*************************************************************************************************
// FileName : oAuthCallback.php
// FilePath : pushHandler/Digikey/
// Author   : Christian Marty
// Date		: 06.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../core/database.php";

if($_SERVER['REQUEST_METHOD'] !== 'GET'){
    http_response_code(405);
    echo "Error 405 - Method Not Allowed";
    exit;
}

$database = new Database();

$query = <<< QUERY
    SELECT 
        ApiData 
    FROM vendor
    WHERE API = 'digikey'
QUERY;

$result = $database->query($query);
if($result instanceof \Error\Data){
    http_response_code(500);
    echo "Database error";
    exit;
}

if(count($result) !== 1){
    http_response_code(500);
    echo "Digi-Key api is not configured correctly";
    exit;
}

$apiJson = $result[0]->ApiData;
$apiData = json_decode($apiJson);

if($apiData === null){
    http_response_code(500);
    echo "Digi-Key api data is not configured correctly";
    exit;
}


$code = $_GET["code"] ?? "ERROR: no code";
$state = $_GET["state"] ?? "ERROR: no state";

$url   = $apiData->ApiPath.'v1/oauth2/token';
$post  = "code=".$code;
$post .= "&client_id=".$apiData->ClientId;
$post .= "&client_secret=".$apiData->ClientSecret;
$post .= "&redirect_uri=".urlencode($apiData->CallbackPath);
$post .= "&grant_type=authorization_code";

$curl = curl_init();
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS,$post);

$result = curl_exec($curl);

curl_close($curl);

$digikeyAuthData = json_decode($result);

if(session_status() === PHP_SESSION_NONE){
    session_start();
}

$_SESSION['digikeyAccessToken'] = $digikeyAuthData->access_token;
$_SESSION['digikeyRefreshToken'] = $digikeyAuthData->refresh_token;
$_SESSION['digikeyAccessTokenExpire'] = time()+intval($digikeyAuthData->expires_in);
$_SESSION['digikeyRefreshTokenExpire'] = time()+intval($digikeyAuthData->refresh_token_expires_in);

$_SESSION['digikeyLastError'] = $code;

var_dump($digikeyAuthData);
echo "<br>";
var_dump($code);
echo "<br>";
echo "done";


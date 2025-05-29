<?php
//*************************************************************************************************
// FileName : config.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2020
// Website  : www.christian-marty.ch
//*************************************************************************************************

$adServer = "ldap://192.168.1.34";
$ldapBase = "cn=users,dc=admin";

//$adServer = "ldap://192.168.1.200";
//$ldapBase = "ou=users,dc=example,dc=org";

//$adServer = "ldap://192.168.1.200:9389";
//$ldapBase = "DC=ldap,DC=goauthentik,DC=io";

$serverPath = "http://192.168.1.138:8461/";
$serverDataPath = "W:/eerp/data"; // Path without trailing /

$documentPath = "/documents";
$picturePath = "/pictures";
$ingestPath = "/ingest";
$assetPath = "/assets";


$databaseServerAddress = '192.168.1.34';
$databasePort = '3306';
$databaseName = 'BlueNova';
$databaseUser = 'BlueNova';
$databasePassword = 'tC?gt7=y*9P+BFbZ';
//*/
/*
$databaseServerAddress = '192.168.1.200';
$databasePort = '3366';
$databaseName = 'eerp';
$databaseUser = 'root';
$databasePassword = 'test1234';
//*/

$domainRootPath = "https://my domain.ch"; // Path without trailing /

$apiRootPath      =  $serverPath.'api.php';
$dataRootPath     =  $serverPath.'data.php';
$rendererRootPath =  $serverPath.'renderer.php';
$assetsRootPath   = $dataRootPath.'/assets';

$companyName = "Eigentum von Christian Marty";
$accountingCurrencyId = 1;

$defaultLocationBarcode = 'Loc-00000';
$vendorId = 161;
$addressId = 1;

?>
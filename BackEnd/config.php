<?php
//*************************************************************************************************
// FileName : config.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2020
// Website  : www.christian-marty.ch
//*************************************************************************************************

$ldapServer = "ldap://192.168.1.11";
$ldapBase = "cn=users,dc=admin";
$ldapUsernameAttribute = "uid";
$disablePasswords = true; // this disables ldap password checking -> for development and testing


$serverPath = "http://192.168.1.138:8080/";
$serverDataPath = "N:/eerp"; // Path without trailing /

$documentPath = "/documents";
$picturePath = "/pictures";
$ingestPath = "/ingest";
$assetPath = "/assets";


$databaseServerAddress = '192.168.1.11';
$databasePort = '3306';
$databaseName = 'eerp';
$databaseUser = 'eerp';
$databasePassword = '32FaP1sosu';


$domainRootPath = "https://my domain.ch"; // Path without trailing /

$apiRootPath      =  $serverPath.'api.php';
$dataRootPath     =  $serverPath.'data.php';
$rendererRootPath =  $serverPath.'renderer.php';
$assetsRootPath   = $dataRootPath.'/assets';

$companyName = "Christian Marty";
$accountingCurrencyId = 1;

$defaultLocationBarcode = 'Loc-00000';
$vendorId = 161;
$addressId = 1;

$lmStudioUrl = "http://127.0.0.1:1234/";
$lmStudioToken = "sk-lm-qa8WOxy3:1WnDZsxaa7IfAoJ7L7uU";
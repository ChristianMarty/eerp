<?php
//*************************************************************************************************
// FileName : config.php
// FilePath : /
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

$adServer = "ldap://localhost";

// All paths without trailing /

$serverDataPath = "/volume1/web/eerp/data"; 

$documentPath = "/documents";
$picturePath = "/pictures";
$ingestPath = "/ingest";
$assetPath = "/assets";


$databaseServerAddress = 'localhost';
$databasePort = '3306';
$databaseName = 'eerp';
$databaseUser = 'eerp';
$databasePassword = 'My Safe DB Password'; 

$domainRootPath = $_SERVER['SERVER_NAME'].pathinfo($_SERVER['PHP_SELF'], 1);

$apiRootPath      = $domainRootPath.'/api.php';
$documentRootPath = $domainRootPath.'/document.php';
$dataRootPath     = $domainRootPath.'/data.php';
$assetsRootPath   = $dataRootPath.'/assets';


$companyName = "My Company Name"; // Should not be longer than 30 characters
$accountingCurrencyId = 1;



$showPhpError = true;
$devMode = false; // If true-> Disables user authentification


// Octopart API
$enableOctopart   = false;
$octopartApiPath  = 'https://octopart.com/api/v4/';
$octopartApiToken = '';

// Mouser API
$enableMouser  = false;
$mouserApiPath = 'https://api.mouser.com/api/v1';
$mouserApiKey  = '';
$mouserSupplierId = 0;

// Digikey API
$enableDigikey  = false;
$digikeyApiPath = '';
$digikeyClientId = '';
$digikeyClientSecret = '';
$digikeyCallbackPath = '';
$digikeySupplierId = 0;

?>
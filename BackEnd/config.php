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

$serverDataPath = "/volume1/web/BlueNovaGCT";
$documentPath = "/data/documents";
$picturePath = "/data/pictures";

$databaseServerAddress = 'localhost';
$databasePort = '3306';
$databaseName = 'eerp';
$databaseUser = 'eerp';
$databasePassword = 'My Safe DB Password'; 

$domainRootPath = $_SERVER['SERVER_NAME'].pathinfo($_SERVER['PHP_SELF'], 1);

$apiRootPath      = $domainRootPath.'/api.php';
$documentRootPath = $domainRootPath.'/document.php';
$dataRootPath     = $domainRootPath;

$companyName = "My Company Name"; // Should not be longer than 30 characters

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

?>
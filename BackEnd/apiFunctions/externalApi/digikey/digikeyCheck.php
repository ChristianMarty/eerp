<?php
//*************************************************************************************************
// FileName : digikey.php
// FilePath : apiFunctions/externalApi/
// Author   : Christian Marty
// Date		: 06.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";



global $enableDigikey;
global $digikeyApiPath; 
global $digikeyClientId;
global $digikeyClientSecret;
global $digikeyCallbackPath;
global $digikeyAccessTokenExpire;
	


echo var_dump( $enableDigikey);
echo var_dump( $digikeyApiPath); 
echo var_dump( $digikeyClientId);
echo var_dump( $digikeyClientSecret);
echo var_dump( $digikeyCallbackPath);

session_start();
echo 'digikeyAccessToken '.$_SESSION['digikeyAccessToken']."\n";
echo 'digikeyAccessTokenExpire '.$_SESSION['digikeyAccessTokenExpire']."\n";

echo 'digikeyRefreshToken '.$_SESSION['digikeyRefreshToken']."\n";

echo 'digikeyLastError '.$_SESSION['digikeyLastError']."\n";



?>
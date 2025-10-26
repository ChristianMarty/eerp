<?php
//*************************************************************************************************
// FileName : language.php
// FilePath : FilePath : apiFunctions/vendor/contact
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $options = $database->getEnumOptions('vendor_contact','Language');
    $api->returnData($options);
}

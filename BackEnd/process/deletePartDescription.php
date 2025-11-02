<?php
//*************************************************************************************************
// FileName : deletePartDescription.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

$title = "Delete Description";
$description = "Delete production and manufacturer part description";
$parameter = null;


$query = <<<STR
    UPDATE productionPart SET Description = NULL;
    UPDATE manufacturerPart_item SET Description = NULL; 
STR;
$database->execute($query);

echo "Done";
exit;

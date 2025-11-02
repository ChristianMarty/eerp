<?php
//*************************************************************************************************
// FileName : updateParEmpty.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 08.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

$title = "Update Part Empty";
$description = "Update part location of empty parts flag";
$parameter = null;


$query = <<<STR
    UPDATE partStock SET LocationId = 17 WHERE partStock.Cache_Quantity = 0;
STR;
var_dump($database->execute($query));
exit;

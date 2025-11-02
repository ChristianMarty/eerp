<?php
//*************************************************************************************************
// FileName : prodParts.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

$title = "Import Production Parts";
$description = "Import Production Parts from PartLookup.";


$query =  <<<STR
    INSERT INTO productionPart(Number, Description)
        SELECT PartNumber, Description 
        FROM partLookup 
        WHERE NOT EXISTS (SELECT Number FROM productionPart WHERE productionPart.Number =  partLookup.PartNumber)  
        GROUP BY PartNumber;
STR;
$result = $database->execute($query);
var_dump($result);
exit;

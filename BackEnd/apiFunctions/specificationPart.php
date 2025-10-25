<?php
//*************************************************************************************************
// FileName : specificationPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 12.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeParser.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet(Permission::SpecificationPart_List))
{
    $parameters = $api->getGetData();

    $query = <<<STR
        SELECT 
            SpecificationPartNumber,
            specificationPart.Type,
            specificationPart.Name,
            specificationPart_revision.Revision
        FROM specificationPart
        LEFT JOIN specificationPart_revision on specificationPart.Id = specificationPart_revision.SpecificationPartId
        WHERE Revision IS NOT NULL
    STR;

    $output = $database->query($query);
    foreach($output as $item)
    {
        $item->SpecificationPartNumber = intval($item->SpecificationPartNumber);
        $item->ItemCode = barcodeFormatter_SpecificationPart($item->SpecificationPartNumber, $item->Revision);
        $item->Description = $item->Name." - Revision ".$item->Revision;
    }
    $api->returnData($output);
}

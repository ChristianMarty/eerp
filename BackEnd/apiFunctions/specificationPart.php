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
    $result = $database->query($query);
    \Error\checkErrorAndExit($result);

    foreach($result as $item)
    {
        $item->SpecificationPartNumber = intval($item->SpecificationPartNumber);
        $item->ItemCode = \Numbering\format(\Numbering\Category::SpecificationPart, $item->SpecificationPartNumber, $item->Revision);
        $item->Description = $item->Name." - Revision ".$item->Revision;
    }

    $api->returnData($result);
}

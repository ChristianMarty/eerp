<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/specificationPart
// Author   : Christian Marty
// Date		: 01.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->SpecificationPartBarcode)) $api->returnParameterMissingError('SpecificationPartBarcode');
    $specificationPartNumber = barcodeParser_SpecificationPart($parameters->SpecificationPartBarcode);

    $query = <<<STR
        SELECT 
            specificationPart.Number AS SpecificationPartNumber,
            specificationPart.Type,
            specificationPart.Name,
            specificationPart.Description,
            
            productionPart.Number AS ProductionPartNumber,
            numbering.Prefix AS ProductionPartNumberPrefix,
            productionPart.Description AS ProductionPartDescription 
        FROM specificationPart
        LEFT JOIN specificationPart_revision ON specificationPart_revision.SpecificationPartId = specificationPart.Id
        LEFT JOIN productionPart_specificationPart_mapping ON productionPart_specificationPart_mapping.SpecificationPartRevisionId = specificationPart_revision.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_specificationPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE specificationPart.Number = $specificationPartNumber
    STR;

    $result = $database->query($query);
    $output = $result[0];
    $output->SpecificationPartBarcode = barcodeFormatter_SpecificationPart($output->SpecificationPartNumber);

    $productionParts = [];
    foreach ($result as $line)
    {
        if($line->ProductionPartNumber === null) continue;

        $item = clone $line;
        unset($item->SpecificationPartNumber);
        unset($item->SpecificationPartBarcode);
        unset($item->Type);
        unset($item->Title);
        unset($item->Description);
        $item->ProductionPartBarcode = $item->ProductionPartNumberPrefix."-".$item->ProductionPartNumber;

        $productionParts[] =  $item;
    }

    $output->ProductionParts = $productionParts;

    $api->returnData($output);
}
if($api->isPost())
{
    $data = $api->getPostData();

    if(!isset($data->Type)) $api->returnParameterMissingError('Type');
    if(!isset($data->Name)) $api->returnParameterMissingError('Name');

    $sqlData = array();
    $sqlData['Type'] = $data->Type;
    $sqlData['Name'] = $data->Name;
    $sqlData['CreationUserId'] = $user->userId();

    $id = $database->insert("specificationPart", $sqlData);
    $query = <<< QUERY
        SELECT Number FROM specificationPart WHERE Id = $id;
    QUERY;

    $output = array();
    $output['SpecificationPartNumber'] = $database->query($query)[0]->Number;
    $output['ItemCode'] = barcodeFormatter_SpecificationPart($output['SpecificationPartNumber']);
    $api->returnData($output);
}

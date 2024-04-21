<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once  "_function.php";
require_once __DIR__ . "/../_part.php";
require_once __DIR__ . "/../../util/_getDocuments.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->ManufacturerPartItemId)) $api->returnParameterMissingError("ManufacturerPartItemId");
    $manufacturerPartItemId = intval($parameters->ManufacturerPartItemId);
    if($manufacturerPartItemId == 0) $api->returnParameterError("ManufacturerPartItemId");

    $query = <<<STR
        SELECT 
            manufacturerPart_item.Id AS PartId, 
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            manufacturerPart_item.Number AS PartNumber, 
            Attribute,
            manufacturerPart_partPackage.name AS Package, 
            manufacturerPart_class.Name AS PartClassName,
            manufacturerPart_class.Name AS PartClassPath,
            manufacturerPart_item.SeriesId AS SeriesId, 
            manufacturerPart_series.Id AS SeriesId,
            manufacturerPart_series.Title AS SeriesTitle, 
            manufacturerPart_series.NumberTemplate AS SeriesNumberTemplate, 
            manufacturerPart_series.Description AS SeriesDescription,
            manufacturerPart_series.DocumentIds AS SeriesDocumentIds,
            manufacturerPart_item.DocumentIds AS ItemDocumentIds
        FROM manufacturerPart_item
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN manufacturerPart_class On manufacturerPart_class.Id <=> manufacturerPart_series.ClassId OR manufacturerPart_class.Id <=> manufacturerPart_item.PartClassId
        LEFT JOIN manufacturerPart_partPackage On manufacturerPart_partPackage.Id = manufacturerPart_item.PackageId
        LEFT JOIN vendor On vendor.Id <=> manufacturerPart_series.VendorId OR vendor.Id <=> manufacturerPart_item.VendorId
        WHERE manufacturerPart_item.Id = '$manufacturerPartItemId'
        LIMIT 1
    STR;
    $output = $database->query($query)[0];


    $parameter = array();
    if(isset($output->SeriesId)) {
        $parameter = getParameter($output->SeriesId);

        $output->PartNumberWithoutParameters = manufacturerPart_numberWithoutParameters($output->PartNumber);
        $output->PartNumberDescription = descriptionFromNumber($output->SeriesNumberTemplate, $parameter, $output->PartNumber);
    }
    else{
        $output->PartNumberWithoutParameters = "";
        $output->PartNumberDescription = "";
    }

    if($output->PartNumberWithoutParameters !== ""){
        $output->DisplayPartNumber = $output->PartNumberWithoutParameters;
    }else{
        $output->DisplayPartNumber = $output->PartNumber;
    }


    $query = <<<STR
        SELECT 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId,
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            productionPart.Number AS ProductionPartNumber, 
            numbering.Prefix AS ProductionPartNumberPrefix
        FROM manufacturerPart_partNumber
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE manufacturerPart_partNumber.ItemId = '$manufacturerPartItemId'
    STR;
    $result =  $database->query($query);

    $partNumbers = array();
    foreach ($result as $r)
    {
        $manufacturerPartNumber = $r->ManufacturerPartNumber;
        unset($r->ManufacturerPartNumber);

        //$r['Description'] = descriptionFromNumber($output['PartNumber'],getParameter($dbLink,$output['SeriesId']), $r['Number']);

        if(!isset($partNumbers[$manufacturerPartNumber]))
        {
            $temp = array();
            $temp['ProductionPart'] = array();
            $temp['ManufacturerPartNumberId'] = intval($r->ManufacturerPartNumberId);
            $temp['ManufacturerPartNumber'] = $manufacturerPartNumber;
            $temp['ManufacturerPartNumberDescription'] = descriptionFromNumber($output->PartNumber,$parameter,$manufacturerPartNumber);
            $partNumbers[$manufacturerPartNumber] = $temp;
        }

        $r->ProductionPartItemCode = barcodeFormatter_ProductionPart($r->ProductionPartNumberPrefix."-".$r->ProductionPartNumber);
        $r->ProductionPartBarcode = $r->ProductionPartItemCode; // TODO: legacy -> remove
        $partNumbers[$manufacturerPartNumber]['ProductionPart'][] = $r;
    }
    $output->PartNumberItem = array_values($partNumbers);

    if(isset($output->Attribute)) $output->Attribute = decodeAttributes(getAttributes(),$output->Attribute);
    else $output->Attribute = array();

    $documentIds = array();
    if(isset($output->SeriesDocumentIds)) $documentIds += explode(",",$output->SeriesDocumentIds);
    if(isset($output->ItemDocumentIds)) $documentIds += explode(",",$output->ItemDocumentIds);
    $documentIdsStr = implode(",", $documentIds);
    $output->Documents = getDocumentsFromIds($documentIdsStr);

    $api->returnData($output);
}

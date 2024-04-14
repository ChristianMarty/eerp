<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date     : 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../_function.php";

if($api->isGet())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->PartNumberId))$api->returnParameterMissingError("PartNumberId");

    $partNumber = intval($parameters->PartNumberId);

    $query = <<<STR
        SELECT *, 
               manufacturerPart_partNumber.Number AS PartNumber,
               manufacturerPart_partNumber.Id AS PartNumberId,
               manufacturerPart_item.Id AS PartId,
               vendor.Id AS ManufacturerId,
               vendor_displayName(vendor.Id) AS ManufacturerName,
               manufacturerPart_series.Id AS SeriesId,
               manufacturerPart_series.Title AS SeriesTitle,
               manufacturerPart_series.Description AS SeriesDescription,
               manufacturerPart_series.NumberTemplate AS SeriesNumberTemplate, 
               manufacturerPart_partPackage.Id AS PackageId,
               manufacturerPart_partPackage.Name AS PackageName
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN manufacturerPart_class ON manufacturerPart_class.Id = manufacturerPart_series.ClassId
        LEFT JOIN manufacturerPart_partPackage ON manufacturerPart_partPackage.Id = manufacturerPart_item.PackageId
        LEFT JOIN vendor ON  vendor.Id <=> manufacturerPart_partNumber.VendorId OR vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_series.VendorId
        WHERE manufacturerPart_partNumber.Id = '$partNumber'
    STR;

    $output = $database->query($query)[0];

    if($output->NumberTemplate === null) $output->NumberTemplate = $output->Number;

    $parameter= getParameter($output->SeriesId);

    $output->PartNumberDescription = descriptionFromNumber($output->SeriesNumberTemplate,$parameter,$output->PartNumber);
    $api->returnData($output);

}
else if($api->isPost())
{
    $data = $api->getPostData();
    if(!isset($data->VendorId))$api->returnParameterMissingError("VendorId");
    if(!isset($data->PartNumber))$api->returnParameterMissingError("PartNumber");

    $vendorId = intval($data->VendorId);
    $partNumber = $data->PartNumber;//dbEscapeString($dbLink, $_GET["PartNumber"]);

    $partNumberCreate = array();
    $partNumberCreate['VendorId'] = intval($data->VendorId);
    $partNumberCreate['Number'] = trim($data->PartNumber);
    $partNumberCreate['CreationUserId'] = $user->userId();

    $manufacturerPartData = partNumberDataFromNumber($vendorId, $partNumber);

    if($manufacturerPartData !== null)
    {
        $output = array();
        $output['PreExisting'] = true;
        $output['ItemId'] = $manufacturerPartData['ItemId'];
        $output['ItemNumber'] = $manufacturerPartData['Number'];
        $api->returnData($output);
    }

    $output = [];
    $output["ManufacturerPartNumberId"] = $database->insert("manufacturerPart_partNumber", $partNumberCreate);



    //$partParameter = getParameter($dbLink, $manufacturerPartSeries['SeriesId']);

    //$manufacturerPartSeries['PartNumberDescription'] = descriptionFromNumber( $manufacturerPartSeries['NumberTemplate'],$partParameter,$partNumber);

    $api->returnData($output);
}
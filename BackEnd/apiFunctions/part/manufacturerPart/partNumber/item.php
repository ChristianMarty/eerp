<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date		: 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../_function.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET['PartNumberId']))  sendResponse(null, "Part Number Id is not specified!");
    $partNumber = intval($_GET['PartNumberId']);

    $query = <<<STR
        SELECT *, 
               manufacturerPart_partNumber.Number AS PartNumber,
               manufacturerPart_partNumber.Id AS PartNumberId,
               manufacturerPart_item.Id AS PartId,
               vendor.Id AS ManufacturerId,
               vendor.Name AS ManufacturerName,
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
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_partNumber_getVendorId(manufacturerPart_partNumber.Id)
        
        WHERE manufacturerPart_partNumber.Id = '$partNumber'
    STR;

    $dbLink = dbConnect();
    $result = dbRunQuery($dbLink,$query);
    $output = dbGetResult($result);

    if($output['NumberTemplate'] == null) $output['NumberTemplate'] = $output['Number'];
    $output['PartNumberId'] = intval($output['PartNumberId']);
    $output['ItemId'] = intval($output['ItemId']);
    $output['SeriesId'] = intval($output['SeriesId']);
    $output['PackageId'] = intval($output['PackageId']);
    $output['ManufacturerId'] = intval($output['ManufacturerId']);
    $output['PartId'] = intval($output['PartId']);

    $parameter= getParameter($dbLink, $output['SeriesId']);
    $output['PartNumberDescription'] = descriptionFromNumber($output['SeriesNumberTemplate'],$parameter,$output['PartNumber']);

    dbClose($dbLink);

    sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    if(!isset($data['VendorId']))  sendResponse(null, "VendorId is not specified!");
    if(!isset($data['PartNumber']))  sendResponse(null, "PartNumber is not specified!");

    $dbLink = dbConnect();

    $vendorId = intval($data['VendorId']);
    $partNumber = dbEscapeString($dbLink, trim($data['PartNumber']));

    $partNumberCreate = array();
    $partNumberCreate['VendorId'] = intval($data['VendorId']);
    $partNumberCreate['PartNumber'] = trim($data['PartNumber']);

    $manufacturerPartData = partNumberDataFromNumber($dbLink, $vendorId, $partNumber);

    if($manufacturerPartData !== null)
    {
        $output = array();
        $output['PreExisting'] = true;
        $output['ItemId'] = $manufacturerPartData['ItemId'];
        $output['ItemNumber'] = $manufacturerPartData['Number'];
        sendResponse($output);
    }


    $mpnCreate = array();
    $mpnCreate['VendorId'] = $vendorId;
    $mpnCreate['Number'] = $partNumber;

    $query = dbBuildInsertQuery($dbLink, "manufacturerPart_partNumber", $mpnCreate);
    $query .= "SELECT Id FROM manufacturerPart_series WHERE Id = LAST_INSERT_ID();";

    $result = dbMultiQuery($dbLink,$query);

    var_dump($result[0]);

    $output = dbGetResult($result[0]);

    //$partParameter = getParameter($dbLink, $manufacturerPartSeries['SeriesId']);

    //$manufacturerPartSeries['PartNumberDescription'] = descriptionFromNumber( $manufacturerPartSeries['NumberTemplate'],$partParameter,$partNumber);

    dbClose($dbLink);

    sendResponse($output);
}
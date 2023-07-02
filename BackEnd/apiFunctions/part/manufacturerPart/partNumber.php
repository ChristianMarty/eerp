<?php
//*************************************************************************************************
// FileName : partNumber.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 20.05.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../_part.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

    $baseQuery = <<<STR
        SELECT
        manufacturerPart_partNumber.Number AS PartNumber,
        vendor.Name AS ManufacturerName,
        vendor.Id AS ManufacturerId,
        manufacturerPart_partNumber.Id AS PartNumberId,
        manufacturerPart_item.Id AS PartId,
        manufacturerPart_item.Number AS ManufacturerPartNumberTemplate,
        manufacturerPart_item.MarkingCode,
        manufacturerPart_series.Title AS SeriesTitle,
        manufacturerPart_series.Id AS SeriesId,
        manufacturerPart_partNumber.Description
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId OR vendor.Id = manufacturerPart_partNumber.VendorId OR vendor.Id = manufacturerPart_series.VendorId
        
    STR;


    $parameter = array();
    if(isset($_GET["ManufacturerPartNumber"]))
    {
        $manufacturerPartNumber = dbEscapeString($dbLink,$_GET["ManufacturerPartNumber"]);
        $parameter[] =  "manufacturerPart_partNumber.Number LIKE '".$manufacturerPartNumber."'";
    }

    if(isset($_GET["VendorId"]))
    {
        $vendorId = intval($_GET["VendorId"]);
        $parameter[] = "vendor.Id = ".$vendorId;
    }

    $query  = dbBuildQuery($dbLink,$baseQuery,$parameter);
    $query .= " ORDER BY PartNumber ";


    $result = mysqli_query($dbLink,$query);
    $output = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['ManufacturerId'] = intval($r['ManufacturerId']);
        $r['PartNumberId'] = intval($r['PartNumberId']);
        $r['PartId'] = intval($r['PartId']);
        $r['SeriesId'] = intval($r['SeriesId']);
        $r['ManufacturerPartNumberTemplateWithoutParameters'] = manufacturerPart_numberWithoutParameters($r['ManufacturerPartNumberTemplate']);
        $output[] = $r;
    }

    dbClose($dbLink);

    sendResponse($output);
}
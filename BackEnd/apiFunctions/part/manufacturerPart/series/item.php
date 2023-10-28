<?php
//*************************************************************************************************
// FileName : item.php.php
// FilePath : apiFunctions/manufacturerPart/series/
// Author   : Christian Marty
// Date		: 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../../util/_getDocuments.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["ManufacturerPartSeriesId"])) sendResponse(null,"ManufacturerPartSeriesId not set");

    $manufacturerPartSeriesId = intval($_GET["ManufacturerPartSeriesId"]);

    $dbLink = dbConnect();

    $query = <<<STR
        SELECT
            manufacturerPart_series.Id AS ManufacturerPartSeriesId, 
            manufacturerPart_series.Title,
            vendor_displayName(vendor.Id) AS  ManufacturerName, 
            manufacturerPart_class.Name AS ClassName, 
            manufacturerPart_series.Description, NumberTemplate,
            manufacturerPart_series.DocumentIds AS SeriesDocumentIds
        FROM manufacturerPart_series
        LEFT JOIN manufacturerPart_class ON manufacturerPart_class.Id = manufacturerPart_series.ClassId
        LEFT JOIN vendor on vendor.Id = manufacturerPart_series.VendorId
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
    STR;

    $result = mysqli_query($dbLink,$query);

    $output = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['ManufacturerPartSeriesId'] = intval($r['ManufacturerPartSeriesId']);
        $output = $r;
    }

    $parameter= getParameter($dbLink, $manufacturerPartSeriesId);
    $output['Parameter'] = $parameter;

    if($parameter == null) $output['Parameter'] = array();
    foreach($output['Parameter']  as &$p) {
        if(!isset($p['Values'])) $p['Values'] = array();
    }

    $query = <<<STR
        SELECT *
        FROM manufacturerPart_item
        WHERE manufacturerPart_item.SeriesId = '$manufacturerPartSeriesId'
    STR;

    $manufacturerPartItem = array();
    $result = mysqli_query($dbLink,$query);
    while($r = mysqli_fetch_assoc($result))
    {
        $r['ItemId'] = $r['Id'];
        $manufacturerPartItem[] = $r;
    }
    $output['Item'] = $manufacturerPartItem;


    foreach ($output['Item'] as &$item)
    {
        $itemId = $item["Id"];
        $query = <<<STR
            SELECT *
            FROM manufacturerPart_partNumber
            WHERE manufacturerPart_partNumber.ItemId = '$itemId'
        STR;

        $manufacturerPartNumber = array();
        $item['Description'] = descriptionFromNumber($output['NumberTemplate'],$parameter,$item['Number']);

        $result = mysqli_query($dbLink, $query);
        while ($r = mysqli_fetch_assoc($result)) {
            $r['Description'] = descriptionFromNumber($item['Number'],$parameter,$r['Number']);
            $r['PartNumberId'] = intval($r['Id']);
            $manufacturerPartNumber[] = $r;
        }
        $item['PartNumber'] = $manufacturerPartNumber;
    }

    $output["Documents"] = getDocumentsFromIds($dbLink, $output['SeriesDocumentIds']);
    unset($output['SeriesDocumentIds']);

    dbClose($dbLink);

    sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    if(!isset($data['VendorId']))  sendResponse(null, "VendorId is not specified!");
    if(!isset($data['Title']))  sendResponse(null, "Title is not specified!");
    if(!isset($data['Description']))  sendResponse(null, "Description is not specified!");

    $dbLink = dbConnect();

    $seriesCreate = array();
    $seriesCreate['VendorId'] = intval($data['VendorId']);
    $seriesCreate['Description'] = trim($data['Description']);
    $seriesCreate['Title'] = trim($data['Title']);

    $query = dbBuildInsertQuery($dbLink, "manufacturerPart_series", $seriesCreate);
    $query .= "SELECT Id FROM manufacturerPart_series WHERE Id = LAST_INSERT_ID();";

    $output = array();
    $error = null;
    if(mysqli_multi_query($dbLink,$query))
    {
        do {
            if ($result = mysqli_store_result($dbLink)) {
                while ($row = mysqli_fetch_row($result)) {
                    $output["ManufacturerPartSeriesId"] = intval($row[0]);
                }
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($dbLink)) break;
        } while (mysqli_next_result($dbLink));
    }
    else
    {
        $error = "Error description: " . mysqli_error($dbLink);
    }

    dbClose($dbLink);
    sendResponse($output,$error);
}

?>
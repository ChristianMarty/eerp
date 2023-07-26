<?php
//*************************************************************************************************
// FileName : template.php
// FilePath : apiFunctions/manufacturerPart/series/
// Author   : Christian Marty
// Date		: 26.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["ManufacturerPartSeriesId"])) sendResponse(null,"ManufacturerPartSeriesId not set");
    $manufacturerPartSeriesId = intval($_GET["ManufacturerPartSeriesId"]);

    $dbLink = dbConnect();
    $query = <<<STR
        SELECT
            manufacturerPart_series.Id AS ManufacturerPartSeriesId, 
            manufacturerPart_series.SeriesNameMatch,
            manufacturerPart_series.NumberTemplate,
            manufacturerPart_series.Parameter
        FROM manufacturerPart_series
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
    STR;

    $result = mysqli_query($dbLink,$query);
    $output =  mysqli_fetch_assoc($result);

    $output['ManufacturerPartSeriesId'] = intval($output['ManufacturerPartSeriesId']);
    $output['Parameter'] = json_decode($output['Parameter']);

    dbClose($dbLink);
    sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    if(!isset($data['ManufacturerPartSeriesId']))  sendResponse(null, "ManufacturerPartSeriesId is not specified!");
    $manufacturerPartSeriesId = intval($data["ManufacturerPartSeriesId"]);

    $dbLink = dbConnect();

    $seriesCreate = array();
    $seriesCreate['NumberTemplate'] = trim($data['NumberTemplate']);
    $seriesCreate['SeriesNameMatch'] = trim($data['SeriesNameMatch']);
    $seriesCreate['Parameter'] = trim($data['Parameter']);

    $query = dbBuildUpdateQuery($dbLink, "manufacturerPart_series", $seriesCreate, "Id = ".$manufacturerPartSeriesId);
    $result = mysqli_query($dbLink,$query);
    dbClose($dbLink);

    if(!$result) sendResponse(null, "Update failed");

    sendResponse(null);
}

?>
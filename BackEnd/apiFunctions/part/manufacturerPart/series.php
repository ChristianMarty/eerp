<?php
//*************************************************************************************************
// FileName : series.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            manufacturerPart_series.Id AS ManufacturerPartSeriesId, 
            manufacturerPart_series.Title, 
            vendor_displayName(vendor.Id) AS  ManufacturerName, 
            manufacturerPart_class.Name AS ClassName, 
            manufacturerPart_series.Description 
        FROM manufacturerPart_series
        LEFT JOIN manufacturerPart_class ON manufacturerPart_class.Id = manufacturerPart_series.ClassId
        LEFT JOIN vendor on vendor.Id = manufacturerPart_series.VendorId
    STR;

    $result = mysqli_query($dbLink,$query);

    $output = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['ManufacturerPartSeriesId'] = intval($r['ManufacturerPartSeriesId']);
        $output[] = $r;
    }

    dbClose($dbLink);

    sendResponse($output);
}
?>
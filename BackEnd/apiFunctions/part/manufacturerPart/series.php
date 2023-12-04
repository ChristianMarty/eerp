<?php
//*************************************************************************************************
// FileName : series.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
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

    $output = $database->query($query);
    $api->returnData($output);
}

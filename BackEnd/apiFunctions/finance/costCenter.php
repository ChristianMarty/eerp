<?php
//*************************************************************************************************
// FileName : costCenter.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 29.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
{
    $query = <<< QUERY
        SELECT 
            finance_costCenter.Name AS Name, 
            finance_costCenter.CostCenterNumber, 
            finance_costCenter.Description, 
            finance_costCenter.ProjectId, 
            finance_costCenter.Color,
            project.Title AS ProjectName
        FROM finance_costCenter
        LEFT JOIN project ON project.Id = finance_costCenter.ProjectId = project.Id
    QUERY;
    $result = $database->query($query);

    foreach($result as $item) {
        $item->Barcode = barcodeFormatter_CostCenter($item->CostCenterNumber);
    }
    $api->returnData($result);
}

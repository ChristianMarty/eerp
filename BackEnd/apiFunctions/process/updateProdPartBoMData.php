<?php
//*************************************************************************************************
// FileName : updateProdPartBoMData.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/siFormatter.php";

$title = "Update BoM Cache";
$description = "Update production part BoM Cache";
$parameter = null;

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();


    $query = <<<STR
        UPDATE productionPart AS pp, 
            (
                SELECT
                    billOfMaterial_item.ProductionPartId,
                    COUNT(billOfMaterial_item.ProductionPartId) AS Quantity
                FROM billOfMaterial_revision
                LEFT JOIN billOfMaterial ON billOfMaterial.Id = billOfMaterial_revision.BillOfMaterialId
                LEFT JOIN billOfMaterial_item ON billOfMaterial_item.BillOfMaterialRevisionId = billOfMaterial_revision.Id
                GROUP BY billOfMaterial_item.ProductionPartId
            )AS pq
        SET pp.Cache_BillOfMaterial_TotalQuantityUsed = pq.Quantity
        WHERE pp.Id = pq.ProductionPartId;
    STR;
    dbRunQuery($dbLink, $query);

    $query = <<<STR
        UPDATE productionPart AS pp, 
            (
                SELECT
                    billOfMaterial_item.ProductionPartId,
                    COUNT(DISTINCT billOfMaterial.Id) AS BoMs
                FROM billOfMaterial_revision
                LEFT JOIN billOfMaterial ON billOfMaterial.Id = billOfMaterial_revision.BillOfMaterialId
                LEFT JOIN billOfMaterial_item ON billOfMaterial_item.BillOfMaterialRevisionId = billOfMaterial_revision.Id
                GROUP BY billOfMaterial_item.ProductionPartId
            )AS pq
        SET pp.Cache_BillOfMaterial_NumberOfOccurrence = pq.BoMs
        WHERE pp.Id = pq.ProductionPartId;
    STR;
    dbRunQuery($dbLink, $query);

    dbClose($dbLink);

    exit;
}


?>
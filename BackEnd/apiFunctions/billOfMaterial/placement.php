<?php
//*************************************************************************************************
// FileName : placement.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 25.09.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

    if(!isset($_GET["RevisionId"])) sendResponse(NULL, "RevisionId Undefined");
    $revisionId = intval($_GET["RevisionId"]);

    $dbLink = dbConnect();
    $query = <<<STR
        SELECT 
               billOfMaterial_item.ReferenceDesignator,
               billOfMaterial_item.Layer,
               billOfMaterial_item.PositionX,
               billOfMaterial_item.PositionY,
               billOfMaterial_item.Rotation, 
               productionPart.Number AS ProductionPartNumber, 
               numbering.Prefix AS ProductionPartPrefix, 
               productionPart.Description 
        FROM billOfMaterial_item 
        LEFT JOIN productionPart ON productionPart.Id = billOfMaterial_item.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE BillOfMaterialRevisionId = $revisionId
    STR;

    $bom = array();
    $result = dbRunQuery($dbLink,$query);
    while($r = mysqli_fetch_assoc($result))
    {
        $r['ProductionPartNumber'] = $r['ProductionPartPrefix']."-".$r['ProductionPartNumber'];
        $bom[] = $r;
    }

    dbClose($dbLink);

    sendResponse($bom);
}

?>
<?php
//*************************************************************************************************
// FileName : bom.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 13.11.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["RevisionId"])) sendResponse(NULL, "RevisionId Undefined");


	$dbLink = dbConnect();
	if($dbLink == null) return null;

	
	$revisionId = dbEscapeString($dbLink, $_GET["RevisionId"]);

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

	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
        $r['ProductionPartNumber'] = $r['ProductionPartPrefix']."-".$r['ProductionPartNumber'];
		$bom[] = $r;
	}
	
	dbClose($dbLink);
	
	sendResponse($bom);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	if(!isset($data["RevisionId"])) sendResponse(NULL, "RevisionId Undefined");
	if(!isset($data["Bom"])) sendResponse(NULL, "Bom Undefined");
	
	$bom = $data['Bom'];
	$revisionId = dbEscapeString($dbLink, $data["RevisionId"]);
	
	foreach ( $bom as $line)
	{
		$sqlData = array();

       // $temp = explode("-", $line['ProductionPartNumber']);
       // $productionPartPrefix = dbEscapeString($dbLink,$line['ProductionPartNumber']);
       // $productionPartNumber = dbEscapeString($dbLink,$temp[1]);

		$sqlData['BillOfMaterialRevisionId'] = $revisionId;

        $sqlData['ProductionPartId']['raw'] = "(SELECT productionPart.Id FROM productionPart LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId WHERE CONCAT (numbering.Prefix,'-',productionPart.Number) = '".dbEscapeString($dbLink,$line['ProductionPartNumber'])."')";
		//$sqlData["ProductionPartId']['raw'] = '(SELECT Id FROM productionPart WHERE Number = "'.$productionPartNumber.'" AND NumberingPrefixId = ( SELECT Id FROM numbering WHERE Prefix = "'.$productionPartPrefix.'") )';
		$sqlData['ReferenceDesignator'] = trim(dbEscapeString($dbLink,$line['ReferenceDesignator']));
		$sqlData['PositionX'] = trim(dbEscapeString($dbLink,$line['XPosition']));
		$sqlData['PositionY'] = trim(dbEscapeString($dbLink,$line['YPosition']));
		$sqlData['Rotation'] = trim(dbEscapeString($dbLink,$line['Rotation']));
		$sqlData['Layer'] = trim(dbEscapeString($dbLink,$line['Layer']));
		$sqlData['Description'] = trim(dbEscapeString($dbLink,$line['Description']));
		
		$query = dbBuildInsertQuery($dbLink,"billOfMaterial_item", $sqlData);

       // echo $query;
       // exit;

		dbRunQuery($dbLink,$query);
	}
	
	dbClose($dbLink);	
	
	
	$projectData = array();
	
	sendResponse($projectData);
}

?>
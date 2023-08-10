<?php
//*************************************************************************************************
// FileName : analysis.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 31.07.2023
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
        SELECT * ,
               COUNT(*) AS Quantity, 
               productionPart.Number AS ProductionPartNumber, 
               numbering.Prefix AS ProductionPartPrefix, 
               productionPart.Description 
        FROM billOfMaterial_item
        LEFT JOIN productionPart ON productionPart.Id = billOfMaterial_item.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE BillOfMaterialRevisionId = $revisionId
        GROUP BY ProductionPartId
    STR;

	$bomStock = array();
	$bom = array();
	
	$numberOfLines = 0;
	$numberOfLinesAvailable = 0;
	$totalComponents = 0;

    $totalAveragePurchasePrice = 0;
	
	$result = mysqli_query($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$bomLine = array();
		$bomLine["ProductionPartNumber"] = $r['ProductionPartPrefix']."-".$r['ProductionPartNumber'];
		$bomLine["Description"] = $r['Description'];
		$bomLine["Quantity"] = $r['Quantity'];

        $referencePrice = array();
        $referencePrice['Minimum'] = $r['Cache_ReferencePrice_Minimum'];
        $referencePrice['Average'] = $r['Cache_ReferencePrice_WeightedAverage'];
        $referencePrice['Maximum'] = $r['Cache_ReferencePrice_Maximum'];

        $referenceLeadTime = array();
        $referenceLeadTime['Minimum'] = null;
        $referenceLeadTime['Average'] = $r['Cache_ReferenceLeadTime_WeightedAverage'];
        $referenceLeadTime['Maximum'] = null;

        $purchasePrice = array();
        $purchasePrice['Minimum'] = null;
        $purchasePrice['Average'] = $r['Cache_PurchasePrice_WeightedAverage'];
        $purchasePrice['Maximum'] = null;

        $totalAveragePurchasePrice += $purchasePrice['Average']*$bomLine["Quantity"];

        $bomLine["PurchasePrice"] = $purchasePrice;
        $bomLine["ReferencePrice"] = $referencePrice;
        $bomLine["ReferenceLeadTime"] = $referenceLeadTime;
        $bomLine["NumberOfManufacturers"] = $r['Cache_Sourcing_NumberOfManufacturers'];
        $bomLine["NumberOfParts"] = $r['Cache_Sourcing_NumberOfParts'];

		$bom[] = $bomLine;
		
		$numberOfLines ++;
		$totalComponents+= $r['Quantity'];
	}
	
	dbClose($dbLink);

    $cost = array();
    $cost['TotalAveragePurchasePrice'] = $totalAveragePurchasePrice;

    $bomStock['Cost'] = $cost;
	$bomStock['Bom'] = $bom;
	$bomStock['TotalNumberOfComponents'] = $totalComponents;
	$bomStock['NumberOfUniqueComponents'] = $numberOfLines;
	
	sendResponse($bomStock);
}

?>
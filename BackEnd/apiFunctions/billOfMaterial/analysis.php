<?php
//*************************************************************************************************
// FileName : analysis.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet(\Permission::BillOfMaterial_View))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->RevisionId)) $api->returnParameterMissingError("RevisionId");
    $revisionId = intval($parameter->RevisionId);

    $query = <<<STR
        SELECT 
            * ,
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

    $bom = $database->query($query);

	$bomStock = array();
	
	$numberOfLines = 0;
	$numberOfLinesAvailable = 0;
	$totalComponents = 0;

    $totalAveragePurchasePrice = 0;

	foreach ($bom as $line)
	{
        $line->ProductionPartBarcode = barcodeFormatter_ProductionPart($line->ProductionPartPrefix . "-" . $line->ProductionPartNumber);
        $line->ProductionPartNumber = $line->ProductionPartBarcode; // TODO: Legacy -> remove

        $referencePrice = array();
        $referencePrice['Minimum'] = $line->Cache_ReferencePrice_Minimum;
        $referencePrice['Average'] = $line->Cache_ReferencePrice_WeightedAverage;
        $referencePrice['Maximum'] = $line->Cache_ReferencePrice_Maximum;

        $referenceLeadTime = array();
        $referenceLeadTime['Minimum'] = null;
        $referenceLeadTime['Average'] = $line->Cache_ReferenceLeadTime_WeightedAverage;
        $referenceLeadTime['Maximum'] = null;

        $purchasePrice = array();
        $purchasePrice['Minimum'] = null;
        $purchasePrice['Average'] = $line->Cache_PurchasePrice_WeightedAverage;
        $purchasePrice['Maximum'] = null;

        $totalAveragePurchasePrice += $purchasePrice['Average']*$line->Quantity;

        $line->PurchasePrice = $purchasePrice;
        $line->NumberOfManufacturers = $line->Cache_Sourcing_NumberOfManufacturers;
        $line->NumberOfParts = $line->Cache_Sourcing_NumberOfParts;
		
		$numberOfLines ++;
		$totalComponents += $line->Quantity;
	}

    $cost = array();
    $cost['TotalAveragePurchasePrice'] = $totalAveragePurchasePrice;

    $bomStock['Cost'] = $cost;
	$bomStock['Bom'] = $bom;
	$bomStock['TotalNumberOfComponents'] = $totalComponents;
	$bomStock['NumberOfUniqueComponents'] = $numberOfLines;
	
	$api->returnData($bomStock);
}

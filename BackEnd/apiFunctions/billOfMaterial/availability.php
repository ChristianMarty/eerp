<?php
//*************************************************************************************************
// FileName : availability.php
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

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->RevisionId)) $api->returnParameterMissingError("RevisionId");
    $revisionId = intval($parameter->RevisionId);

    $query = <<<STR
        SELECT * ,
               COUNT(*) AS Quantity, 
               productionPart_getQuantity(productionPart.NumberingPrefixId, productionPart.Number) AS Stock, 
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

	$numberOfLines = 0;
	$numberOfLinesAvailable = 0;
	$totalComponents = 0;

	foreach($bom as $line)
    {
        $line->ProductionPartBarcode = barcodeFormatter_ProductionPart($line->ProductionPartPrefix . "-" . $line->ProductionPartNumber);
        $line->ProductionPartNumber = $line->ProductionPartBarcode; // TODO: Legacy -> remove

        $line->StockQuantity = $line->Stock;
        $line->Availability = $line->Stock / $line->Quantity * 100;
        if ($line->Availability > 100) $line->Availability = 100;

        $line->StockCertaintyFactor = 0;  // TODO: Add real value

        $numberOfLines++;
        if ($line->Stock >= $line->Quantity) $numberOfLinesAvailable++;

        $totalComponents += $line->Quantity;
    }

    $bomStock = array();
	$bomStock['Bom'] = $bom;
	if($numberOfLines != 0)$bomStock['StockItemsAvailability'] = $numberOfLinesAvailable/$numberOfLines*100;
	else $bomStock['StockItemsAvailability'] = 0;
	$bomStock['TotalNumberOfComponents'] = $totalComponents;
	$bomStock['NumberOfUniqueComponents'] = $numberOfLines;

    $api->returnData($bomStock);
}

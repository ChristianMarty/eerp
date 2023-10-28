<?php
//*************************************************************************************************
// FileName : price.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	if(!isset($_GET["ProductionPartNumber"])) sendResponse(NULL, "Production Part Number Undefined");

    $partNumber = barcodeParser_ProductionPart($_GET["ProductionPartNumber"]);

    if(!$partNumber) sendResponse(NULL, "Part Number Invalid");

	$dbLink = dbConnect();

    $query = <<< STR
        SELECT Price, MinimumOrderQuantity, IncrementalOrderQuantity, Weight, InformationSource, InformationDate, Note, CurrencyCode, productionPart_manufacturerPart_mapping.ManufacturerPartId, ManufacturerPartNumber, vendor_displayName(vendor.Id) AS VendorName, vendor.DisplayName AS VendorDisplayName, suppier.Name AS SuppierName, suppier.DisplayName AS SuppierDisplayName, suppier.Id AS SuppierId
        FROM  part_referencePrice
        LEFT JOIN productionPart_manufacturerPart_mapping ON part_referencePrice.ManufacturerPartId = productionPart_manufacturerPart_mapping.ManufacturerPartId
        LEFT JOIN productionPart ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id OR part_referencePrice.ProductionPartId = productionPart.Id
        LEFT JOIN finance_currency ON part_referencePrice.CurrencyId = finance_currency.Id
        LEFT JOIN manufacturerPart ON manufacturerPart.Id = productionPart_manufacturerPart_mapping.ManufacturerPartId
        LEFT JOIN vendor ON manufacturerPart.VendorId = vendor.Id
        LEFT JOIN vendor AS suppier ON part_referencePrice.SupplierId = vendor.Id
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = '$partNumber';
    STR;

	$result = mysqli_query($dbLink,$query);
	
	$output = array();
    $output['Data'] = array();

    $minimum = 100000000;
    $maximum = 0;
    $averageSum = 0;
    $weightedAverageSum = 0;
    $weightSum = 0;


	while($r = mysqli_fetch_assoc($result))
    {
        if($r['VendorDisplayName']) $r['ManufacturerPart'] = $r['VendorDisplayName']." ".$r['ManufacturerPartNumber'];
        else $r['ManufacturerPart'] = $r['VendorName']." ".$r['ManufacturerPartNumber'];

        if($r['SuppierDisplayName']) $r['SuppierName'] = $r['SuppierDisplayName'];
        unset($r['SuppierDisplayName']);

        $output['Data'][] = $r;

        if($r['Price'] < $minimum ) $minimum = $r['Price'];
        if($r['Price'] > $maximum ) $maximum = $r['Price'];

        $averageSum +=  $r['Price'];
        $weightedAverageSum +=  $r['Price'] * $r['Weight'];
        $weightSum += $r['Weight'];
    }

	dbClose($dbLink);


	if(count($output['Data']))
	{
		$output['Statistics']['Minimum'] = round($minimum,6);
		$output['Statistics']['Maximum'] = round($maximum,6);
		$output['Statistics']['Average'] = round($averageSum / count($output['Data']),6);
		$output['Statistics']['WeightedAverage'] =  round($weightedAverageSum / $weightSum,6);
	}
	else
	{
		$output['Statistics']['Minimum'] = null;
		$output['Statistics']['Maximum'] = null;
		$output['Statistics']['Average'] = null;
		$output['Statistics']['WeightedAverage'] =  null;
	}
	
		

	sendResponse($output);
}

?>
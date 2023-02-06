<?php
//*************************************************************************************************
// FileName : leadTime.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	if(!isset($_GET["ProductionPartNumber"])) sendResponse(NULL, "Production Part Number Undefined");

    $partNumber = barcodeParser_ProductionPart($_GET["ProductionPartNumber"]);

    if(!$partNumber) sendResponse(NULL, "Part Number Invalid");

	$dbLink = dbConnect();

    $query = <<< STR
        SELECT LeadTime, Weight, InformationSource, InformationDate, Note, productionPartMapping.ManufacturerPartId, ManufacturerPartNumber, vendor.Name AS VendorName, vendor.ShortName AS VendorShortName FROM  part_referenceLeadTime
        LEFT JOIN productionPartMapping ON part_referenceLeadTime.ManufacturerPartId = productionPartMapping.ManufacturerPartId
        LEFT JOIN productionPart ON productionPartMapping.ProductionPartId = productionPart.Id OR part_referenceLeadTime.ProductionPartId = productionPart.Id
        LEFT JOIN manufacturerPart ON manufacturerPart.Id = productionPartMapping.ManufacturerPartId
        LEFT JOIN vendor ON manufacturerPart.VendorId = vendor.Id
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
        if($r['VendorShortName']) $r['ManufacturerPart'] = $r['VendorShortName']." ".$r['ManufacturerPartNumber'];
        else $r['ManufacturerPart'] = $r['VendorName']." ".$r['ManufacturerPartNumber'];

        $output['Data'][] = $r;

        if($r['LeadTime'] < $minimum ) $minimum = $r['LeadTime'];
        if($r['LeadTime'] > $maximum ) $maximum = $r['LeadTime'];

        $averageSum +=  $r['LeadTime'];
        $weightedAverageSum +=  $r['LeadTime'] * $r['Weight'];
        $weightSum += $r['Weight'];
    }

	dbClose($dbLink);
	
	if(count($output['Data']))
	{
		$output['Statistics']['Minimum'] = intval($minimum);
		$output['Statistics']['Maximum'] = intval($maximum);
		$output['Statistics']['Average'] = round($averageSum / count($output['Data']),1);
		$output['Statistics']['WeightedAverage'] =  round($weightedAverageSum / $weightSum,1);
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
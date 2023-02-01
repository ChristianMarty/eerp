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
        WHERE productionPart.PartNo = '$partNumber';
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

    $output['Statistics']['Minimum'] = $minimum;
    $output['Statistics']['Maximum'] = $maximum;
    $output['Statistics']['Average'] = round($averageSum / count($output['Data']),2);
    $output['Statistics']['WeightedAverage'] =  round($weightedAverageSum / $weightSum,2);

	sendResponse($output);
}

?>
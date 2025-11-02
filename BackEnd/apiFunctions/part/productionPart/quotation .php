<?php
//*************************************************************************************************
// FileName : quotation.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    $api->returnError("Not Implemented");

    if (!isset($parameters->ProductionPartNumber)) $api->returnParameterMissingError('ProductionPartNumber');
    $productionPartNumber = \Numbering\parser(\Numbering\Category::ProductionPart, $parameters->ProductionPartNumber);
    if($productionPartNumber == 0) $api->returnParameterError( "ProductionPartNumber");


    /*
    $query = <<< STR
        SELECT 
            LeadTime, 
            Weight, 
            InformationSource, 
            InformationDate, 
            Note, 
            productionPart_manufacturerPart_mapping.ManufacturerPartId, 
            ManufacturerPartNumber, 
            vendor_displayName(vendor.Id) AS VendorName, 
            vendor.DisplayName AS VendorDisplayName, 
            suppier.Name AS SuppierName, 
            suppier.DisplayName AS SuppierDisplayName, 
            suppier.Id AS SuppierId 
        FROM  part_referenceLeadTime
        LEFT JOIN productionPart_manufacturerPart_mapping ON part_referenceLeadTime.ManufacturerPartId = productionPart_manufacturerPart_mapping.ManufacturerPartId
        LEFT JOIN productionPart ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id OR part_referenceLeadTime.ProductionPartId = productionPart.Id
        LEFT JOIN manufacturerPart ON manufacturerPart.Id = productionPart_manufacturerPart_mapping.ManufacturerPartId
        LEFT JOIN vendor ON manufacturerPart.VendorId = vendor.Id
        LEFT JOIN vendor AS suppier ON part_referenceLeadTime.SupplierId = vendor.Id
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

        if($r['LeadTime'] < $minimum ) $minimum = $r['LeadTime'];
        if($r['LeadTime'] > $maximum ) $maximum = $r['LeadTime'];

        $averageSum +=  $r['LeadTime'];
        $weightedAverageSum +=  $r['LeadTime'] * $r['Weight'];
        $weightSum += $r['Weight'];
    }

	
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

	$api->returnData($output);
    */
}

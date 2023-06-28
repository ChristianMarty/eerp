<?php
//*************************************************************************************************
// FileName : prodPartsMatch.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

$title = "Match Parts";
$description = "Match Manufacturer Part against PartLookup and production parts.";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "TRUNCATE `productionPart`; ALTER TABLE `productionPart` AUTO_INCREMENT=1;";
	echo dbRunQuery($dbLink,$query);
	
	
	$query = "SELECT * FROM `partLookup` WHERE VendorId IS NOT NULL";
	$queryResult = dbRunQuery($dbLink,$query);
	
	
	$partLookup = array();
	
	// Sort by ManufacturerId
	while($part = mysqli_fetch_assoc($queryResult))
	{
		$mfrId = $part['VendorId'];
		
		if( !isset($partLookup[$mfrId]) || !is_array($partLookup[$mfrId]))
		{
			$partLookup[$mfrId] = array();
		}
		$partLookup[$mfrId][] = $part;
	}
	
	$query = "SELECT * FROM `manufacturerPart` ";
	$queryResult = dbRunQuery($dbLink,$query);
	
	$mfrParts = array();
	
	// Sort by ManufacturerId
	while($part = mysqli_fetch_assoc($queryResult))
	{
		$mfrId = $part['VendorId'];
		
		if( !isset($mfrParts[$mfrId]) || !is_array($mfrParts[$mfrId]))
		{
			$mfrParts[$mfrId] = array();
		}
		
		$mfrParts[$mfrId][] = $part;
	}
	
	dbClose($dbLink);
	
	
	// Match parts
	
	$matches = array();
	$count = 0;
	foreach($partLookup as  $partLookupMfr)
	{
		$mfrId = $partLookupMfr[0]['VendorId'];

		if(!array_key_exists($mfrId, $mfrParts)) continue;
		
		foreach($mfrParts[$mfrId] as $mfrPart)
		{
			foreach($partLookupMfr as $lookupPart)
			{
				$temp = findMatch($lookupPart, $mfrPart);
				
				if(!is_null($temp))
				{
					$count++;
					$matches[] = $temp;
				}
			}
		}
	}
	
	foreach($matches as  $line)
	{
		addProdPart($line['PartNo'],$line['MpnId'],$line['MatchCertainty']);
	}
	
	// Add manualy enterd Part Number if no found
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            partStock.ManufacturerPartId,
            partStock.OrderReference 
        FROM partStock
        LEFT JOIN productionPartMapping ON partStock.ManufacturerPartId = productionPartMapping.ManufacturerPartId
        LEFT JOIN productionPart ON partStock.ManufacturerPartId = productionPartMapping.ManufacturerPartId
        WHERE (partStock.OrderReference != productionPart.Number OR (productionPart.Number IS NULL AND partStock.OrderReference IS NOT NULL)) AND partStock.OrderReference != ''
        GROUP BY partStock.ManufacturerPartId,partStock.OrderReference
    STR;

	$queryResult = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);
	
	while($part = mysqli_fetch_assoc($queryResult))
	{
		addProdPart($part['OrderReference'],$part['ManufacturerPartId'],"5");
		$matches[] = $part;
	}
	
	sendResponse($matches);
}

function findMatch($lookup, $part)
{
	set_time_limit(20);
	
	$mfrId = $part['VendorId'];
	$mfrPartNr = trim($part['ManufacturerPartNumber']);
	$mfrPartId = $part['Id'];
			
	$lookupPartNr = trim($lookup['ManufacturerPartNumber']);
				
	if($mfrPartNr == $lookupPartNr) 
	{
		$temp = array();
		$temp['$mfrPartNr'] = $mfrPartNr;
		$temp['$lookupPartNr'] = $lookupPartNr;
		$temp['PartNo'] = $lookup['PartNo'];
		$temp['MpnId'] = $mfrPartId;
		$temp['MfrId'] = $mfrId;
		
		$temp['MatchCertainty'] = "0";
		
		return $temp;
	}
	
	$mfrPartNr = str_replace(' ','',strtolower($mfrPartNr));
	$lookupPartNr = str_replace(' ','',strtolower($lookupPartNr));
	
	if($mfrPartNr == $lookupPartNr) 
	{
		$temp = array();
		$temp['$mfrPartNr'] = $mfrPartNr;
		$temp['$lookupPartNr'] = $lookupPartNr;
		$temp['PartNo'] = $lookup['PartNo'];
		$temp['MpnId'] = $mfrPartId;
		$temp['MfrId'] = $mfrId;
		
		$temp['MatchCertainty'] = "1";
		
		return $temp;
	}
	
	if(str_replace('-','',$mfrPartNr) == str_replace('-','',$lookupPartNr )) 
	{
		$temp = array();
		$temp['$mfrPartNr'] = $mfrPartNr;
		$temp['$lookupPartNr'] = $lookupPartNr;
		$temp['PartNo'] = $lookup['PartNo'];
		$temp['MpnId'] = $mfrPartId;
		$temp['MfrId'] = $mfrId;
		
		$temp['MatchCertainty'] = "2";
		
		return $temp;
	}
	
	$tempMfrPartNr = str_replace('-','',$mfrPartNr);
	$templookupPartNr = str_replace('-','',$lookupPartNr);
	
	if(is_numeric($tempMfrPartNr) AND is_numeric($templookupPartNr))
	{
		if( intval($tempMfrPartNr) == intval($templookupPartNr)) 
		{
			$temp = array();
			$temp['$mfrPartNr'] = $mfrPartNr;
			$temp['$lookupPartNr'] = $lookupPartNr;
			$temp['PartNo'] = $lookup['PartNo'];
			$temp['MpnId'] = $mfrPartId;
			$temp['MfrId'] = $mfrId;
			
			$temp['MatchCertainty'] = "3";
			
			return $temp;
		}
	}
	
	// produces wrong matches
	/*if((strstr($tempMfrPartNr, $templookupPartNr) OR strstr($templookupPartNr, $tempMfrPartNr)) AND $tempMfrPartNr != $templookupPartNr)
	{
		$temp = array();
		$temp['$mfrPartNr'] = $mfrPartNr;
		$temp['$lookupPartNr'] = $lookupPartNr;
		$temp['PartNo'] = $lookup['PartNo'];
		$temp['MpnId'] = $mfrPartId;
		$temp['MfrId'] = $mfrId;
		
		$temp['MatchCertainty'] = "4";
		
		return $temp;
	}*/

	return null;
}

function like_match($pattern, $subject): bool
{
    $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$pattern}$/i", $subject);
}

function addProdPart($partNo, $mfrPartId, $matchCertainty)
{
	$query = "INSERT IGNORE INTO productionPartMapping(ProductionPartId, ManufacturerPartId, MatchCertainty) VALUES( (SELECT Id FROM productionPart WHERE Number = '".$partNo."'), ".$mfrPartId.", '".$matchCertainty."')";
		
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	dbRunQuery($dbLink,$query);
	dbClose($dbLink);
}

?>
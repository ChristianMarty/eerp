<?php
//*************************************************************************************************
// FileName : location.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";


function getLocations()
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query = "SELECT * FROM location ORDER BY `Name` ASC";	
	
	$result = dbRunQuery($dbLink,$query);
	
	$locations = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$locations[] = $r;
	}
	
	dbClose($dbLink);	
	
	return $locations;
}

function hasChild($rows,$id): bool
{
	foreach ($rows as $row) 
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function buildLocationTree($rows, $parentId): array
{
	$treeItem = array();
	foreach ($rows as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$temp = array();
			
			$temp['Name'] = $row['Name'];
			$temp['LocationNumber'] = $row['LocNr'];
			$temp['LocationBarcode'] = "Loc-".$row['LocNr'];
			
			$temp['Attributes'] = array();
			if($row['ESD'] == 1) $temp['Attributes']['EsdSave'] = true;
			elseif ($row['ESD'] == null) $temp['Attributes']['EsdSave'] = null;
			else $temp['Attributes']['EsdSave'] = false;
			
			
			// TODO: Remove legacy below
			$temp['Id'] = $row['Id'];
			$temp['LocNr'] = "Loc-".$row['LocNr'];
			
			if($row['ESD'] == 1) $temp['ESD'] = true;
			elseif ($row['ESD'] == null) $temp['ESD'] = null;
			else $temp['ESD'] = false;
			
			// up to here
		
			if (hasChild($rows,$row['Id']))
			{
				$temp['Children'] = array();
				$temp['Children'] =  buildLocationTree($rows,$row['Id']);
			}
			array_push($treeItem, $temp);
		}
	}
	
	return $treeItem;
}

function buildLocation($rows, $id)
{
	if($id == NULL) return "";
	
	$lines = array();
	foreach($rows as $row)
	{
		$lines[$row['Id']] = $row;
	}
	
	$descriptionString = "";
	$nextId = $id;
	$i = 0;
	$depth = $lines[$id]['RecursionDepth'];
	while($i < $depth) 
	{
		$descriptionString = $lines[$nextId]['Name']." ".$descriptionString;
		$nextId = $lines[$nextId]['ParentId'];
		
		if($nextId == 0) break;
		$i++;
	}
	
	if(strlen($descriptionString) == 0)
	{
		$descriptionString = $lines[$id]['Name'];
	}
	
	return $descriptionString;
}

function buildLocationPath($rows, $id, $depth): string
{
	if($id == NULL) return "";
	
	$lines = array();
	foreach($rows as $row)
	{
		$lines[$row['Id']] = $row;
	}
	
	$descriptionString = "";
	$nextLocId = $id;
	$i = 0;
	while($i < $depth) 
	{	
		$nextParentId = $nextLocId;
		$itemDepth = $lines[$nextLocId]['RecursionDepth'];
		$nextLocId = $lines[$nextLocId]['LocationId'];
		
		if($nextLocId == 0) break;
		if($nextLocId == NULL) break;
		
		while($nextParentId != 0 AND $itemDepth >= 0)
		{
			$descriptionString = $lines[$nextParentId]['Name']." ".$descriptionString;
			$nextParentId = $lines[$nextParentId]['ParentId'];
			
			$itemDepth --;
		}
		
		$descriptionString = "-> ".$descriptionString;
		
		if($lines[$nextLocId]['Movable'] == 0 )
		{
			$nextParentId = $nextLocId;
			$itemDepth = $lines[$nextLocId]['RecursionDepth'];
			$nextLocId = $lines[$nextLocId]['LocationId'];
			while($nextParentId != 0)
			{
				$descriptionString = $lines[$nextParentId]['Name']." ".$descriptionString;
				$nextParentId = $lines[$nextParentId]['ParentId'];
			}
			break;
		}
		

		$i++;
	}
	
	return $descriptionString;
}
	
?>
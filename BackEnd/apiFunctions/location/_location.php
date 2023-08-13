<?php
//*************************************************************************************************
// FileName : _location.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 12.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

function location_getName(int|null $locationId) : string
{
	if($locationId === null)return "";

	global $locations;
	if($locations == null) $locations = location_getLocations();

	if(array_key_exists($locationId, $locations)) $output = $locations[$locationId]['Cache_DisplayName'];
	else $output = "Error: Location dose not exist";

	if($output == null) $output = "";
	return $output;
}

function location_getPath(int|null $locationId) : string
{
	if($locationId === null)return "";

	global $locations;
	if($locations == null) $locations = location_getLocations();

	if(array_key_exists($locationId, $locations)) $output = $locations[$locationId]['Cache_DisplayPath'];
	else $output = "Error: Location dose not exist";

	if($output == null) $output = "";
	return $output;
}

function location_getLocations() : array
{
	$query = <<<STR
		SELECT *
		FROM location
		ORDER BY `Name` ASC
	STR;

	$dbLink = dbConnect();
	$result = dbRunQuery($dbLink,$query);

	$locationList = array();
	while($itemData = mysqli_fetch_assoc($result))
	{
		$itemData['Id'] = intval($itemData['Id']);
		$itemData['ParentId'] = intval($itemData['ParentId']);
		$itemData['LocationId'] = intval($itemData['LocationId']);
		$itemData['RecursionDepth'] = intval($itemData['RecursionDepth']);

		$locationList[$itemData['Id']] = $itemData;
	}
	dbClose($dbLink);

	return $locationList;
}

function location_hasChild(array $locationData, int $id): bool
{
	foreach ($locationData as $row)
	{
		if ($row['ParentId'] == $id)return true;
	}
	return false;
}

function location_buildTree(array $locationData, int $parentId): array
{
	$treeItem = array();
	foreach ($locationData as $row)
	{
		if ($row['ParentId'] == $parentId)
		{
			$temp = array();

			$temp['Name'] = $row['Name'];
			$temp['Description'] = $row['Description'];
			$temp['LocationNumber'] = $row['LocNr'];
			$temp['LocationBarcode'] = barcodeFormatter_LocationNumber($row['LocNr']);

			$temp['Attributes'] = array();
			if($row['ESD'] == 1) $temp['Attributes']['EsdSave'] = true;
			elseif ($row['ESD'] == null) $temp['Attributes']['EsdSave'] = null;
			else $temp['Attributes']['EsdSave'] = false;

			// TODO: Remove legacy below
			$temp['Id'] = $row['Id'];
			$temp['LocNr'] = barcodeFormatter_LocationNumber($row['LocNr']);

			if($row['ESD'] == 1) $temp['ESD'] = true;
			elseif ($row['ESD'] == null) $temp['ESD'] = null;
			else $temp['ESD'] = false;

			// up to here
			if (location_hasChild($locationData,$row['Id']))
			{
				$temp['Children'] = array();
				$temp['Children'] =  location_buildTree($locationData,$row['Id']);
			}
			$treeItem[] = $temp;
		}
	}

	return $treeItem;
}

function location_buildLocation(array $locationData, int $id): string
{
	if($id == 0) return "";

	$descriptionString = "";
	$nextId = $id;
	$i = 0;
	while($i < 100)
	{
		$descriptionString = $locationData[$nextId]['Name']."".$descriptionString;
		$nextId = $locationData[$nextId]['ParentId'];

		if($nextId == 0) break;
		$i++;
	}

	if(strlen($descriptionString) == 0)
	{
		$descriptionString = $locationData[$id]['Name'];
	}

	return $descriptionString;
}

function location_buildLocationPath(array $locationData, int $id, int $depth): string
{
	if($id == 0) return "";

	$descriptionString = "";
	$nextLocationId = $id;
	$i = 0;

	$nextParentId = $locationData[$nextLocationId]['ParentId'];
	$nextLocationId = $locationData[$nextLocationId]['LocationId'];

	if($nextLocationId == 0)  $nextLocationId = $nextParentId;

	while($i < $depth)
	{
		$i++;
		if($nextLocationId == 0) break;
		if(!location_hasChild($locationData, $nextLocationId))
		{
			$descriptionString = location_buildLocation($locationData, $nextLocationId)."".$descriptionString;
		}

		$nextParentId = $locationData[$nextLocationId]['ParentId'];
		$nextLocationId = $locationData[$nextLocationId]['LocationId'];

		if($nextLocationId == 0)  $nextLocationId = $nextParentId;
	}

	return trim($descriptionString);
}
?>

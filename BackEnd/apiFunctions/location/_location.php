<?php
//*************************************************************************************************
// FileName : _location.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 12.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

class Location
{
	private static array $locationData;
	public function __construct( )
    {
		if(empty($this->data)){
			global $database;
			$query = <<<STR
				SELECT *
				FROM location
				ORDER BY `Name` ASC
			STR;
			$result = $database->query($query);
			foreach($result as $item)
			{
				self::$locationData[$item->Id] = $item;
			}
		}
	}

	public static function name(int|null $locationId): string
	{
		if($locationId === null) return "";

		if(array_key_exists($locationId, self::$locationData)) $output = self::$locationData[$locationId]['Cache_DisplayName'];
		else $output = "Error: Location dose not exist";

		if($output == null) $output = "";
		return $output;
	}

	public static function path(int|null $locationId): string
	{
		if($locationId === null) return "";

		if(array_key_exists($locationId, self::$locationData)) $output = self::$locationData[$locationId]['Cache_DisplayPath'];
		else $output = "Error: Location dose not exist";

		if($output == null) $output = "";
		return $output;
	}

	public function tree(int|null $parentId = null): array
	{
		if($parentId === null) $parentId = 0;

		$treeItem = array();
		foreach (self::$locationData as $row)
		{
			if ($row->ParentId === $parentId)
			{
				$temp = new stdClass;

				$temp->Name = $row->Name;
				$temp->Description = $row->Description;
				$temp->LocationNumber = $row->LocNr;
				$temp->LocationBarcode = barcodeFormatter_LocationNumber($row->LocNr);

				$temp->Attributes = new stdClass;
				$temp->Attributes->EsdSave = boolval($row->ESD);

				if ($this->hasChild($row->Id)) $temp->Children = $this->tree($row->Id);


				// TODO: Remove legacy below
				
				$temp->Id = $row->Id;
				$temp->LocNr = barcodeFormatter_LocationNumber($row->LocNr);
				$temp->ESD = $temp->Attributes->EsdSave; //*/


				$treeItem[] = $temp;
			}
		}

		return $treeItem;
	}

	private function hasChild( int $id): bool
	{
		foreach (self::$locationData  as $row)
		{
			if ($row->ParentId == $id)return true;
		}
		return false;
	}

}

function location_getItems(int|null $locationId) : array
{
	if($locationId === null) return [];

	global $database;

	// get items
	$items = array();
	$query = <<<QUERY
		SELECT 
		    StockNo,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
			vendor_displayName(manufacturerPart_partNumber.VendorId) AS ManufacturerName,
			Date, 
			Cache_Quantity 
		FROM partStock
		LEFT JOIN manufacturerPart_partNumber on partStock.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
		WHERE LocationId = '$locationId';
	QUERY;

	$result = $database->query($query);
	foreach ($result as $item)
	{
		$data = array();
		$data["Item"] =  barcodeFormatter_StockNumber($item->StockNo);
		$data["Category"] = "Stock";
		$data["Description"] = "$item->ManufacturerName $item->ManufacturerPartNumber, $item->Date Qty: $item->Cache_Quantity";
		$items[] = $data;
	}

	$query = <<<QUERY
		SELECT 
		    InvNo, 
		    Title, 
		    Manufacturer, 
		    Type, 
		    LocationId 
		FROM inventory
		WHERE LocationId = '$locationId';
	QUERY;

	$result = $database->query($query);
	foreach ($result as $item)
	{
		$data = array();
		$data["Item"] = barcodeFormatter_InventoryNumber($item->InvNo);
		$data["Category"] = "Inventory";
		$data["Description"] = "$item->Title - $item->Manufacturer $item->Type";
		$items[] = $data;
	}

	$query = <<<QUERY
		SELECT 
		    AssemblyUnitNumber, 
		    SerialNumber, 
		    Name, 
		    Description,  
		    LocationId 
		FROM assembly_unit
		LEFT JOIN assembly on assembly.Id =  assembly_unit.AssemblyId
		WHERE LocationId = '$locationId';
	QUERY;

	$result = $database->query($query);
	foreach ($result as $item)
	{
		$data = array();
		$data["Item"] = barcodeFormatter_AssemblyUnitNumber($item->AssemblyUnitNumber);
		$data["Category"] = "Assembly Unit";
		$data["Description"] = "$item->Name - $item->Description SN: $item->SerialNumber";
		$items[] = $data;
	}

	$query = <<<QUERY
		SELECT 
			LocNr, 
			Name 
		FROM location 
		WHERE LocationId = '$locationId';
	QUERY;

	$result = $database->query($query);
	foreach ($result as $item)
	{
		$data = array();
		$data["Item"] = barcodeFormatter_LocationNumber($item->LocNr);
		$data["Category"] = "Location";
		$data["Description"] = "$item->Name";
		$items[] = $data;
	}

	return $items;
}

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

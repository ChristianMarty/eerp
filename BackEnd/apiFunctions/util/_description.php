<?php
//*************************************************************************************************
// FileName : itemDescription.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

use Numbering\Category;

require_once __DIR__ . "/../location/_location.php";

class ItemDescription implements \JsonSerializable
{
    public string|int $itemNumber;
    public Category $category = Category::Undefined;
    public string|int $description;
    public bool $movable;
    public int $locationId;

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->Item = \Numbering\format($this->category, $this->itemNumber);
        $output->Category = $this->category;
        $output->Description = $this->description;
        $output->Movable = $this->movable;

        $locationData = new Location();
        $output->LocationCode = $locationData->itemCode($this->locationId);
        $output->LocationName = $locationData->name($this->locationId);

        return $output;
    }
}


//Generates a universal description of an item of any category
function description_generateSummary(string $itemCode): ItemDescription | \Error\Data
{
    global $database;
    global $user;

	$temp = explode("-", $itemCode);
	$itemPrefix = strtolower($temp[0]);
	$itemNr = $database->escape(trim(strtolower($temp[1])));

    $category = \Numbering\prefixToCategory($itemPrefix);
    if(\Error\checkError($category)){
        return $category;
    }


	if($category === Category::Stock)
	{
        if(!$user->checkPermission(\Permission::Stock_View)){
            return \Error\permission(\Permission::Stock_View);
        }

		$query = <<< STR
		SELECT
		    StockNumber,
			vendor_displayName(vendor.Id) AS ManufacturerName,
			manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
			partStock.Date, 
			Cache_Quantity AS Quantity, 
			LocationId 
		FROM partStock 
		LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId
		LEFT JOIN vendor ON vendor.Id = manufacturerPart_partNumber_getVendorId(partStock.ManufacturerPartNumberId)
		WHERE StockNumber = $itemNr;
		STR;

		$result = $database->query($query);

		if(count($result) == 0)
		{
            return \Error\generic("Item not found");
		}

		$itemData = $result[0];

		$descriptor = $itemData->ManufacturerName." ".$itemData->ManufacturerPartNumber;
        if($itemData->Date) $descriptor .= ", ".$itemData->Date;
		$descriptor .= ", Qty: ".$itemData->Quantity;

        $output = new ItemDescription();
        $output->itemNumber = $itemData->StockNumber;
        $output->category = \Numbering\Category::Stock;
        $output->description = $descriptor;
        $output->movable = true;
        $output->locationId = $itemData->LocationId;

        return $output;

	}
	else if($category === Category::Inventory)
	{
        if(!$user->checkPermission(\Permission::Inventory_View)){
            return \Error\permission(\Permission::Inventory_View);
        }

        $query = <<< STR
		SELECT
		    InventoryNumber,
			Title, 
			Manufacturer, 
			Type, 
			LocationId
		FROM inventory
		WHERE InventoryNumber = $itemNr
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            return \Error\generic("Item not found");
        }

        $itemData = $result[0];

		$descriptor = $itemData->Title;
		$descriptor .= " - ".$itemData->Manufacturer." ".$itemData->Type;


        $output = new ItemDescription();
        $output->itemNumber = $itemData->InventoryNumber;
        $output->category = \Numbering\Category::Inventory;
        $output->description = $descriptor;
        $output->movable = true;
        $output->locationId = $itemData->LocationId;

        return $output;
	}
	else if($category === Category::AssemblyUnit)
	{
        if(!$user->checkPermission(\Permission::Assembly_View)){
            return \Error\permission(\Permission::Assembly_View);
        }

		$query = <<<STR
			SELECT 
			    AssemblyUnitNumber,
				Name, 
				Description, 
				SerialNumber, 
				LocationId
			FROM assembly_unit
			LEFT JOIN assembly ON assembly.Id = assembly_unit.AssemblyId
			WHERE AssemblyUnitNumber = '$itemNr'
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            return \Error\generic("Item not found");
        }

        $itemData = $result[0];

		$descriptor = $itemData->Name;
		$descriptor .= " - ".$itemData->Description." SN: ".$itemData->SerialNumber;

        $output = new ItemDescription();
        $output->itemNumber = $itemData->AssemblyUnitNumber;
        $output->category = \Numbering\Category::AssemblyUnit;
        $output->description = $descriptor;
        $output->movable = true;
        $output->locationId = $itemData->LocationId;

        return $output;
	}
	else if($category === Category::Location)
	{
        if(!$user->checkPermission(\Permission::Location_View)){
            return \Error\permission(\Permission::Location_View);
        }

		$query = <<<STR
			SELECT 
			    LocationNumber,
			    Id,
			    Movable, 
			    LocationId
			FROM location 
			WHERE LocationNumber = $itemNr
		STR;
        $result = $database->query($query);

        if(count($result) == 0)
        {
            return \Error\generic("Item not found");
        }

        $itemData = $result[0];

		$location = new Location();

        $output = new ItemDescription();
        $output->itemNumber = $itemData->AssemblyUnitNumber;
        $output->category = \Numbering\Category::AssemblyUnit;
        $output->description = $location->name($itemData->Id);
        if($itemData->Movable == "1") $output->movable = true;
        else $output->movable = false;
        $output->locationId = $itemData->LocationId;

        return $output;
	}

    return \Error\generic("Unknown Item Category");
}

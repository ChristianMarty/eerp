<?php
//*************************************************************************************************
// FileName : search.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

class SearchResult implements \JsonSerializable
{
    public \Numbering\Category $category;
    public string $item;
    public string $redirectCode;
    public string|null $description = null;

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->Category = $this->category;
        $output->Item = $this->item;
        $output->RedirectCode = $this->redirectCode;
        $output->Description = $this->description??"";
        return $output;
    }
}

if($api->isGet( Permission::Search))
{
	$parameter = $api->getGetData();
	if(!isset($parameter->search)) $api->returnParameterMissingError("search");

	$search = trim(strtolower($parameter->search));
    if(strlen($search) === 0){
        $api->returnData([]);
    }
	$parts = explode('-',$search);

	if(count($parts) >= 2)  // Search for barcodes
	{
        $found = false;
		$category = "";	
		$prefix = "";

        $query = <<<STR
            SELECT
                *
            FROM numbering 
        STR;
		$result = $database->query($query);
        \Error\checkErrorAndExit($result);

		foreach($result as $item)
		{
			if(strtolower($item->Prefix) === $parts[0])
			{
                $category = $item->Category;
				$prefix = $item->Prefix;
				$found = true;
				break;
			}
		}

		if($found)
		{
            $output = new SearchResult();
            $output->category = \Numbering\matchCategory($category);
            $output->item = $prefix . "-" . $parts[1];
            $output->redirectCode = $prefix . "-" . $parts[1];

			$api->returnData([$output]);
		}
	}

	// Search everywhere else
	$output = array_merge(
        search_productionPart($search),
		search_manufacturerPartNumber($search),
		search_assemblySerialNumber($search),
		search_manufacturerPartItem($search),
		manufacturerPartSeries($search),
		search_vendor($search),
        search_supplierPartNumber($search),
        search_purchaseOrder($search),
        search_inventory($search)
	);

	$api->returnData($output);
}

function manufacturerPartSeries(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = <<<STR
		SELECT
			manufacturerPart_series.Id, 
			Title, 
			Description, 
			vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_series 
		LEFT JOIN vendor on manufacturerPart_series.VendorId = vendor.Id
		WHERE Title LIKE $input OR  Description LIKE $input
	STR;
	$result = $database->query($query);
    if($result instanceof \Error\Data) return [];

	$output = [];
	foreach($result as $item)
	{
		$temp = new SearchResult();
		$temp->category = \Numbering\Category::ManufacturerPartSeries;
        $temp->item = $item->VendorName." - ".$item->Title;
        $temp->redirectCode = (string)$item->Id;
        $temp->description = $item->Description;

		$output[] = $temp;
	}
	return $output;
}

function search_vendor(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = <<<STR
		SELECT 
			Id, 
			FullName, 
			ShortName,
			AbbreviatedName,
			vendor_displayName(vendor.Id) AS VendorName
		FROM vendor 
		WHERE FullName LIKE $input OR ShortName LIKE $input OR AbbreviatedName LIKE $input
	STR;
	$result = $database->query($query);
    if($result instanceof \Error\Data) return [];

	$output = [];
	foreach($result as $item)
	{
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::Vendor;
        $temp->item = $item->VendorName;
        $temp->redirectCode = (string)$item->Id;

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartItem(string $input): array
{
	global $database;
	$input = $database->escape($input);

	$query = <<<STR
		SELECT 
		    manufacturerPart_item.Id, 
		    Number, 
		    vendor_displayName(vendor.Id) AS VendorName
		FROM manufacturerPart_item 
		LEFT JOIN vendor on manufacturerPart_item.VendorId = vendor.Id
		WHERE Number LIKE $input
	STR;
	$result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
	foreach($result as $item)
	{
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::ManufacturerPart;
        $temp->item = $item->VendorName." - ".$item->Number;
        $temp->redirectCode = (string)$item->Id;

		$output[] = $temp;
	}
	return $output;
}

function search_manufacturerPartNumber(string $input): array
{
	global $database;
	$input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            Id, 
            Number 
        FROM manufacturerPart_partNumber 
        WHERE Number LIKE $input
    QUERY;
	$result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
	foreach($result as $item)
	{
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::ManufacturerPartNumber;
        $temp->item = $item->Number;
        $temp->redirectCode = (string)$item->Id;

		$output[] = $temp;
	}
	return $output;
}

function search_productionPart(string $input): array
{
    global $database;
    $input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            productionPart.Number, 
            productionPart.Description,
            numbering.Prefix,
            numbering.Category
        FROM productionPart
        LEFT JOIN numbering on numbering.Id = productionPart.NumberingPrefixId
        WHERE productionPart.Number LIKE $input 
           OR productionPart.Description LIKE $input
    QUERY;
    $result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
    foreach($result as $item)
    {
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::ProductionPart;
        $temp->item = $item->Prefix."-".$item->Number;
        $temp->redirectCode = $temp->item;
        $temp->description = $item->Description;

        $output[] = $temp;
    }
    return $output;
}

function search_assemblySerialNumber(string $input): array
{
	global $database;
	$input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            AssemblyUnitNumber, 
            SerialNumber,
            assembly.Name
        FROM assembly_unit
        LEFT JOIN assembly on assembly.Id = assembly_unit.AssemblyId
        WHERE SerialNumber LIKE $input
    QUERY;
	$result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
	foreach($result as $item)
	{
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::AssemblyUnit;
        $temp->item = $item->SerialNumber;
        $temp->redirectCode = \Numbering\format(\Numbering\Category::AssemblyUnit, $item->AssemblyUnitNumber);
        $temp->description = $item->Name;

		$output[] = $temp;
	}
	return $output;
}

function search_supplierPartNumber(string $input): array
{
    global $database;
    $input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            supplierPart.SupplierPartNumber 
        FROM supplierPart
        LEFT JOIN manufacturerPart_partNumber ON supplierPart.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        WHERE supplierPart.SupplierPartNumber LIKE $input
    QUERY;
    $result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
    foreach($result as $item)
    {
        $temp = new SearchResult();
        $temp->category = \Numbering\Category::SupplierPart;
        $temp->item = $item->SupplierPartNumber;
        $temp->redirectCode = (string)$item->ManufacturerPartNumberId;

        $output[] = $temp;
    }
    return $output;
}

function search_purchaseOrder(string $input): array
{
    global $database;
    $input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            purchaseOrder_itemOrder.Sku,
            purchaseOrder_itemOrder.ManufacturerName,
            purchaseOrder_itemOrder.ManufacturerPartNumber,
            purchaseOrder_itemOrder.Description, 
            purchaseOrder.PurchaseOrderNumber,
            purchaseOrder_itemOrder.LineNumber
        FROM purchaseOrder_itemOrder
        LEFT JOIN purchaseOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
        WHERE purchaseOrder_itemOrder.Sku LIKE $input 
           OR purchaseOrder_itemOrder.ManufacturerName LIKE $input
           OR purchaseOrder_itemOrder.ManufacturerPartNumber LIKE $input
           OR purchaseOrder_itemOrder.Description LIKE $input
    QUERY;
    $result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
    foreach($result as $item)
    {
        $description = $item->Description;
        if(!$database::stringEmptyOrNull($item->ManufacturerPartNumber)) $description = $item->ManufacturerPartNumber." - ".$description;
        if(!$database::stringEmptyOrNull($item->ManufacturerName)) $description = $item->ManufacturerName." - ".$description;
        if(!$database::stringEmptyOrNull($item->Sku)) $description = $item->Sku." - ".$description;

        $temp = new SearchResult();
        $temp->category = \Numbering\Category::PurchaseOrder;
        $temp->item = \Numbering\format(\Numbering\Category::PurchaseOrder, $item->PurchaseOrderNumber, $item->LineNumber);
        $temp->redirectCode = $temp->item ;
        $temp->description = $description;

        $output[] = $temp;
    }
    return $output;
}

function search_inventory(string $input): array
{
    global $database;
    $input = $database->escape($input);

    $query = <<< QUERY
        SELECT 
            inventory.InventoryNumber,
            inventory.Title,
            inventory.Manufacturer,
            inventory.Type
        FROM inventory
        WHERE inventory.Title LIKE $input 
           OR inventory.Manufacturer LIKE $input 
           OR inventory.Type LIKE $input
    QUERY;
    $result = $database->query($query);
    if($result instanceof \Error\Data) return [];

    $output = [];
    foreach($result as $item)
    {
        $description = $item->Manufacturer." ".$item->Type;
        if(!$database::stringEmptyOrNull($item->Title)) $description = $item->Title." - ".$description;

        $temp = new SearchResult();
        $temp->category = \Numbering\Category::Inventory;
        $temp->item = \Numbering\format(\Numbering\Category::Inventory, $item->InventoryNumber);
        $temp->redirectCode = $item->InventoryNumber;
        $temp->description = $description;

        $output[] = $temp;
    }
    return $output;
}


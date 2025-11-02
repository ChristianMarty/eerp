<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 27.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/_stock.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../location/_location.php";

function _stockPartQuery(string $stockNo): string
{
    return <<<STR
        SELECT
            partStock.Id AS PartStockId, 
            partStock.DeleteRequestUserId, 
            user.Initials AS CountingRequest,
            partStock.CountingRequestDate,
            supplier.Id AS SupplierId,
            vendor_displayName(supplier.Id) AS SupplierName, 
            supplierPart.SupplierPartNumber, 
            partStock.StockNumber, 
            vendor_displayName(manufacturer.Id) AS ManufacturerName, 
            manufacturer.Id AS ManufacturerId, 
            partStock.LotNumber, 
            manufacturer.Id AS ManufacturerId, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            manufacturerPart_partNumber.SinglePartWeight AS SinglePartWeight,
            partStock.Date, 
            manufacturerPart_partNumber.Description,
            manufacturerPart_item.Id AS ManufacturerPartItemId,
            poLine.SpecificationPartRevisionId AS SpecificationPartRevisionId,
            partStock.LocationId, 
            partStock.HomeLocationId, 
            hc.CreateQuantity,  
            partStock_getQuantity(partStock.StockNumber) AS Quantity, 
            hc.CreateData,
            country.ShortName as CountryOfOriginName,
            country.Alpha2Code as CountryOfOriginCode,
            country.NumericCode as CountryOfOriginNumericCode
        FROM partStock 
        LEFT JOIN (
            SELECT SupplierPartId, SpecificationPartRevisionId, purchaseOrder_itemReceive.Id FROM purchaseOrder_itemOrder
            LEFT JOIN purchaseOrder_itemReceive ON purchaseOrder_itemOrder.Id = purchaseOrder_itemReceive.ItemOrderId
        )poLine ON poLine.Id = partStock.ReceivalId
        LEFT JOIN user ON user.Id = partStock.CountingRequestUserId
        LEFT JOIN supplierPart ON (supplierPart.Id = partStock.SupplierPartId AND partStock.ReceivalId IS NULL) OR (supplierPart.Id = poLine.SupplierPartId)   
        LEFT JOIN manufacturerPart_partNumber ON (manufacturerPart_partNumber.Id = partStock.ManufacturerPartNumberId AND supplierPart.ManufacturerPartNumberId IS NULL) OR manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)manufacturer ON manufacturer.Id = manufacturerPart_item.VendorId OR manufacturer.Id = manufacturerPart_partNumber.VendorId OR manufacturer.Id = manufacturerPart_series.VendorId
        LEFT JOIN (SELECT Id, vendor_displayName(id) FROM vendor)supplier ON supplier.Id = supplierPart.VendorId
        LEFT JOIN country On country.Id = partStock.CountryOfOriginCountryId
        LEFT JOIN (
            SELECT StockId, Quantity AS CreateQuantity, CreationDate AS CreateData FROM partStock_history WHERE ChangeType = 'Create' AND StockId = (SELECT ID FROM partStock WHERE StockNumber = $stockNo)
        )hc ON  hc.StockId = partStock.Id
        WHERE partStock.StockNumber = $stockNo
    STR;
}

if($api->isGet(\Permission::Stock_View))
{
    $parameter = $api->getGetData();
    if(!isset($parameter->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $parameter->StockCode);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

    $result = $database->query(_stockPartQuery($database->escape($stockNumber)));
    \Error\checkErrorAndExit($result);
    \Error\checkNoResultAndExit($result, $parameter->StockCode);

    $item = $result[0];

    $output = new stdClass();

    $output->ItemCode = \Numbering\format(\Numbering\Category::Stock, $item->StockNumber);
    $output->StockNumber = $item->StockNumber;

    $output->LotNumber = $item->LotNumber??"";
    $output->Description = $item->Description??"";

	if($item->Date) {
		$date = new DateTime($item->Date);
        $output->DateCode = $date->format("yW");
        $output->Date = $date->format("Y-m-d");
	}else{
        $output->DateCode = "";
        $output->Date = null;
	}

    // Add CountryOfOrigin information
    $country = new stdClass();
    $country->Name = $item->CountryOfOriginName;
    $country->Alpha2Code = $item->CountryOfOriginCode;
    $country->NumericCode = $item->CountryOfOriginNumericCode;

    $output->CountryOfOrigin = $country;

    // Add supplier information
    $supplier = new stdClass();
    $supplier->Name = $item->SupplierName;
    $supplier->PartNumber = $item->SupplierPartNumber??"";
    $supplier->VendorId = intval($item->SupplierId);

    $output->Supplier = $supplier;

    // Add purchase Information
    $output->Purchase = \Stock\Stock::purchaseInformation(intval($item->PartStockId));

    // Add part information
    $part = new stdClass();
    $part->ManufacturerName = $item->ManufacturerName;
    $part->ManufacturerId = intval($item->ManufacturerId);
    $part->ManufacturerPartNumber = $item->ManufacturerPartNumber;
    if($item->ManufacturerPartNumberId !== null) $part->ManufacturerPartNumberId = intval($item->ManufacturerPartNumberId);
    else $part->ManufacturerPartNumberId = null;
    if($item->ManufacturerPartItemId !== null) $part->ManufacturerPartItemId = intval($item->ManufacturerPartItemId);
    else $part->ManufacturerPartItemId = null;
    if($item->SpecificationPartRevisionId !== null) $part->SpecificationPartRevisionId = intval($item->SpecificationPartRevisionId);
    else $part->SpecificationPartRevisionId = null;

    $weight = new stdClass();
    $weight->SinglePartWeight = $item->SinglePartWeight;
    $unitOfMeasurement = new stdClass();
    $unitOfMeasurement->Unit = "Gram";
    $unitOfMeasurement->Symbol = "g";
    $weight->UnitOfMeasurement = $unitOfMeasurement;
    $part->Weight = $weight;

    $output->Part = $part;

    // Add Quantity
    $quantity = new stdClass();
    $quantity->Quantity = floatval($item->Quantity);
    $quantity->CreateQuantity = floatval($item->CreateQuantity);
    $quantity->CreateData = $item->CreateData;
    $quantity->Certainty = \Stock\Stock::certainty(intval($item->PartStockId));
    $countingRequest = new stdClass();
    $countingRequest->Date = $item->CountingRequestDate;
    $countingRequest->UserInitials = $item->CountingRequest;
    $quantity->CountingRequest = $countingRequest;
    $output->Quantity = $quantity;

    // Add Location
    $location = new Location();
    $output->Location = $location->locationItem($item->LocationId, $item->HomeLocationId);

	if($item->DeleteRequestUserId !== null)$output->Deleted = true;
	else $output->Deleted = false;

    $api->returnData($output);
}
else if($api->isPost(\Permission::Stock_Create))
{
    $data = $api->getPostData();

    if($data->LotNumber == null){
        $lotNumber = null;
    } else {
        $lotNumber = trim($data->LotNumber);
        if(strlen($lotNumber) == 0)$lotNumber = null;
    }

    if($data->Date == null){
        $date = null;
    } else {
        $date = trim($data->Date);
        if(strlen($date) == 0)$date = null;
    }

	$quantity = intval($data->Quantity);
    
    if($data->LocationCode == "") $data->LocationCode = null;
    if($data->LocationCode === null) {
        global $defaultLocationBarcode;
        $data->LocationCode = $defaultLocationBarcode;
    }
    
    $location = \Numbering\parser(\Numbering\Category::Location, $data?->LocationCode??null);
	
	if(isset($data->ReceivalId))  // If part is created based on purchase receival id
	{
        $stockNumber = \Stock\Stock::createOnReceival(
            intval($data->ReceivalId),
            $location,
            $quantity,
            $date,
            $lotNumber
        );
	}
	else // If new part is created
	{
        $stockNumber = \Stock\Stock::create(
            intval($data->ManufacturerId),
            $data->ManufacturerPartNumber,
            $location,
            $quantity,
            $date,
            $lotNumber,
            intval($data->SupplierId),
            $data->SupplierPartNumber
        );
	}

    $stockPart = $database->query(_stockPartQuery("'$stockNumber'"))[0];

    $stockPart->ItemCode = \Numbering\format(\Numbering\Category::Stock, $stockPart->StockNumber);

    $api->returnData($stockPart);
}
else if($api->isPatch(\Permission::Stock_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $data->StockCode);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

    $stockNumber = $database->escape($stockNumber);
    $countryNumericCode = intval($data?->CountryOfOriginNumericCode??0);
    $data->LotNumber = $data?->LotNumber??null;
    $data->Date = $data?->Date??null;

    $sqlData['CountryOfOriginCountryId']['raw'] = "(SELECT Id FROM country WHERE NumericCode = '$countryNumericCode')";
    $sqlData['LotNumber	'] = $data->LotNumber;
    $sqlData['Date'] = $data->Date;

    $result = $database->update("partStock", $sqlData, "StockNumber = $stockNumber");
    $api->returnData($result);
}
else if($api->isDelete(\Permission::Stock_Delete))
{
	$data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
	$stockNumber = \Numbering\parser(\Numbering\Category::Stock, $data->StockCode);
	if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

    $stockNumber = $database->escape($stockNumber);

	$sqlData['DeleteRequestUserId'] = $user->userId();;
	$sqlData['DeleteRequestDate']['raw'] = "current_timestamp()";
	$sqlData['DeleteRequestNote'] = $data->Note;

    $result = $database->update("partStock", $sqlData, "StockNumber = $stockNumber");
    $api->returnData($result);
}

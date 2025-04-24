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
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

function _stockPartQuery(string $stockNo): string
{
	return <<<STR
	SELECT 	partStock.Id AS PartStockId, 
	        partStock.DeleteRequestUserId, 
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

if($api->isGet("stock.view"))
{
    $parameter = $api->getGetData();

    if(!isset($parameter->StockCode)) $api->returnParameterMissingError("StockCode");
    $stockNumber = barcodeParser_StockNumber($parameter->StockCode);
    if($stockNumber === null) $api->returnParameterError("StockCode");

	$r = $database->query(_stockPartQuery($database->escape($stockNumber)));

    if(empty($r)){
        $api->returnEmpty();
    }
    $r = $r[0];

    $output = new stdClass();

    $output->ItemCode = barcodeFormatter_StockNumber($r->StockNumber);
    $output->StockNumber = $r->StockNumber;

    $output->LotNumber = $r->LotNumber??"";
    $output->Description = $r->Description??"";

	if($r->Date) {
		$date = new DateTime($r->Date);
        $output->DateCode = $date->format("yW");
        $output->Date = $date->format("Y-m-d");
	}else{
        $output->DateCode = "";
        $output->Date = null;
	}

    // Add CountryOfOrigin information
    $country = new stdClass();
    $country->Name = $r->CountryOfOriginName;
    $country->Alpha2Code = $r->CountryOfOriginCode;
    $country->NumericCode = $r->CountryOfOriginNumericCode;

    $output->CountryOfOrigin = $country;

    // Add supplier information
    $supplier = new stdClass();
    $supplier->Name = $r->SupplierName;
    $supplier->PartNumber = $r->SupplierPartNumber??"";
    $supplier->VendorId = intval($r->SupplierId);

    $output->Supplier = $supplier;

    // Add purchase Information
    $output->Purchase = \stock\stock::purchaseInformation(intval($r->PartStockId));

    // Add part information
    $part = new stdClass();
    $part->ManufacturerName = $r->ManufacturerName;
    $part->ManufacturerId = intval($r->ManufacturerId);
    $part->ManufacturerPartNumber = $r->ManufacturerPartNumber;
    if($r->ManufacturerPartNumberId !== null) $part->ManufacturerPartNumberId = intval($r->ManufacturerPartNumberId);
    else $part->ManufacturerPartNumberId = null;
    if($r->ManufacturerPartItemId !== null) $part->ManufacturerPartItemId = intval($r->ManufacturerPartItemId);
    else $part->ManufacturerPartItemId = null;
    if($r->SpecificationPartRevisionId !== null) $part->SpecificationPartRevisionId = intval($r->SpecificationPartRevisionId);
    else $part->SpecificationPartRevisionId = null;

    $weight = new stdClass();
    $weight->SinglePartWeight = $r->SinglePartWeight;
    $unitOfMeasurement = new stdClass();
    $unitOfMeasurement->Unit = "Gram";
    $unitOfMeasurement->Symbol = "g";
    $weight->UnitOfMeasurement = $unitOfMeasurement;
    $part->Weight = $weight;

    $output->Part = $part;

    // Add Quantity
    $quantity = new stdClass();
    $quantity->Quantity = floatval($r->Quantity);
    $quantity->CreateQuantity = floatval($r->CreateQuantity);
    $quantity->CreateData = $r->CreateData;
    $quantity->Certainty = \stock\stock::certainty(intval($r->PartStockId));
    $output->Quantity = $quantity;

    // Add Location
    $location = new Location();
    $output->Location = $location->locationItem($r->LocationId, $r->HomeLocationId);

	if($r->DeleteRequestUserId !== null)$output->Deleted = true;
	else $output->Deleted = false;

    $api->returnData($output);
}
else if($api->isPost("stock.create"))
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
    
    $location = barcodeParser_LocationNumber($data->LocationCode ?? null);
	
	if(isset($data->ReceivalId))  // If part is created based on purchase receival id
	{
        $stockNumber = \stock\stock::createOnReceival(
            intval($data->ReceivalId),
            $location,
            $quantity,
            $date,
            $lotNumber
        );
	}
	else // If new part is created
	{
        $stockNumber = \stock\stock::create(
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

    $stockPart->ItemCode = barcodeFormatter_StockNumber($stockPart->StockNumber);

    $api->returnData($stockPart);
}
else if($api->isPatch("stock.edit"))
{
    $data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnParameterMissingError("StockCode");
    $stockNumber = barcodeParser_StockNumber($data->StockCode);
    if($stockNumber === false) $api->returnParameterError("StockNumber");

    $stockNumber = $database->escape($stockNumber);
    $countryNumericCode = intval($data->CountryOfOriginNumericCode);
    $data->LotNumber = $data->LotNumber??null;
    $data->Date = $data->Date??null;

    $sqlData['CountryOfOriginCountryId']['raw'] = "(SELECT Id FROM country WHERE NumericCode = '$countryNumericCode')";
    $sqlData['LotNumber	'] = $data->LotNumber;
    $sqlData['Date'] = $data->Date;

    $database->update("partStock", $sqlData, "StockNumber = $stockNumber");

    $api->returnEmpty();
}
else if($api->isDelete("stock.delete"))
{
	$data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnParameterMissingError("StockCode");
	$stockNumber = barcodeParser_StockNumber($data->StockCode);
	if($stockNumber === false) $api->returnParameterError("StockNumber");

    $stockNumber = $database->escape($stockNumber);

	$sqlData['DeleteRequestUserId'] = $user->userId();;
	$sqlData['DeleteRequestDate']['raw'] = "current_timestamp()";
	$sqlData['DeleteRequestNote'] = $data->Note;

    $database->update("partStock", $sqlData, "StockNumber = $stockNumber");

    $api->returnEmpty();
}

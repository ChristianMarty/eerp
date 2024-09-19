<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../stock/_stock.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";
require_once __DIR__ . "/../_part.php";
require_once __DIR__ . "/../../location/_location.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->ProductionPartBarcode)) $api->returnParameterMissingError("ProductionPartBarcode");
    $productionPartBarcode = barcodeParser_ProductionPart($parameter->ProductionPartBarcode);
    if($productionPartBarcode == null) $api->returnParameterError("ProductionPartBarcode");

    $query = <<<STR
        SELECT 
            numbering.Prefix, 
            productionPart.Number, 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartBarcode,
            productionPart.Description AS ProductionPartDescription,  
            vendor.Id AS ManufacturerId,
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            manufacturerPart_item.Number AS ManufacturerPart,
            manufacturerPart_item.Id AS ManufacturerPartId,
            productionPart.StockMinimum, 
            productionPart.StockMaximum, 
            productionPart.StockWarning
        FROM productionPart
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id =  productionPart_manufacturerPart_mapping.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id = COALESCE(manufacturerPart_series.VendorId, manufacturerPart_item.VendorId, manufacturerPart_partNumber.VendorId)
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId        
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = '$productionPartBarcode'
    STR;

    $result = $database->query($query);

	$output = array();
    foreach ($result as $r)
    {
        if (!isset($output['ProductionPartBarcode'])) // First row
        {
            $output['ProductionPartBarcode'] = $r->ProductionPartBarcode;
            $output['Description'] = $r->ProductionPartDescription;
            $output['StockMinimum'] = $r->StockMinimum;
            $output['StockMaximum'] = $r->StockMaximum;
            $output['StockWarning'] = $r->StockWarning;
            $output['Stock'] = array();
            $output['ManufacturerPart'] = array();
        }

        $manufacturerRow = array();
        $manufacturerRow['ManufacturerPartNumber'] = $r->ManufacturerPartNumber;
        $manufacturerRow['ManufacturerPartNumberId'] = intval($r->ManufacturerPartNumberId);
        $manufacturerRow['ManufacturerPartNumberTemplate'] = manufacturerPart_numberWithoutParameters($r->ManufacturerPart);
        $manufacturerRow['ManufacturerPartId'] = intval($r->ManufacturerPartId);
        $manufacturerRow['ManufacturerName'] = $r->ManufacturerName;
        $manufacturerRow['ManufacturerId'] = intval($r->ManufacturerId);
        $output['ManufacturerPart'][] = $manufacturerRow;
    }

// get stock
    $query = <<<STR
        SELECT
           partStock.Id AS PartStockId,
           partStock.StockNumber,
           partStock.Date,
           partStock.LotNumber,
           partStock_getQuantity(partStock.StockNumber) AS Quantity,
           LocationId
        FROM partStock  

        LEFT JOIN purchaseOrder_itemReceive ON partStock.ReceivalId  = purchaseOrder_itemReceive.Id
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemReceive.ItemOrderId = purchaseOrder_itemOrder.Id
        LEFT JOIN supplierPart ON supplierPart.Id = purchaseOrder_itemOrder.SupplierPartId
        LEFT JOIN manufacturerPart_partNumber ON supplierPart.ManufacturerPartNumberId <=> manufacturerPart_partNumber.Id OR manufacturerPart_partNumber.Id <=> partStock.ManufacturerPartNumberId 

        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id
        -- LEFT JOIN productionPart_specificationPart_mapping ON productionPart_specificationPart_mapping.SpecificationPartRevisionId = partStock.SpecificationPartRevisionId
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId -- OR productionPart.Id = productionPart_specificationPart_mapping.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId        
    
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = '$productionPartBarcode' AND partStock.DeleteRequestUserId IS NULL 
    STR;
    $result = $database->query($query);

    $stock = array();
    $totalStockQuantity = 0;
    $totalStockCertainty = 0;
    $location = new Location();
    foreach ($result as $r)
    {
        $stockRow = array();
        $stockRow['ItemNumber'] = $r->StockNumber;
        $stockRow['ItemCode'] = barcodeFormatter_StockNumber($r->StockNumber);
        $stockRow['Date'] = $r->Date;
        $stockRow['Lot'] = $r->LotNumber;
        $stockRow['Quantity'] = intval($r->Quantity);
        $totalStockQuantity += $stockRow['Quantity'];
        $stockRow['LocationName'] = $location->name($r->LocationId);
        $certainty =  \stock\stock::certainty(intval($r->PartStockId));
        $stockRow['Certainty'] = $certainty;
        $totalStockCertainty += $certainty->Factor*($r->Quantity);

        if(isset($parameter->HideEmptyStock) && $stockRow['Quantity'] == 0) {
            if (filter_var($parameter->HideEmptyStock, FILTER_VALIDATE_BOOLEAN)) {
                continue;
            }
        }
        $stock[] = $stockRow;
    }
    $output['Stock'] = $stock;
    $output['TotalStockQuantity'] = $totalStockQuantity;
    $output['TotalStockCertainty'] = round($totalStockCertainty/$totalStockQuantity, 4);
    $output['TotalStockRating'] = round($output['TotalStockCertainty'] * 5);

// get Characteristics

    $manufacturerPartNumberIdList = [];
    foreach ($output['ManufacturerPart'] as &$item) {
        $manufacturerPartNumberIdList[] = $item['ManufacturerPartNumberId'];
    }
    $manufacturerPartNumberIdStr = implode(",", $manufacturerPartNumberIdList);

    $query = <<<STR
        SELECT 
            manufacturerPart_partNumber.Number,
            GROUP_CONCAT(manufacturerPart_partNumber.Id) AS PartNumberIds,
            manufacturerPart_item.Id,
            manufacturerPart_item.Attribute
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        WHERE manufacturerPart_partNumber.Id IN ($manufacturerPartNumberIdStr)
        GROUP BY manufacturerPart_item.Id
    STR;
    $result = $database->query($query);

    $characteristics = array();
    $attributeIds = array();
    foreach ($result as $r)
    {
        if($r->Attribute !== null)
        {
            $r->Attribute = json_decode($r->Attribute);
            $attributeIds = array_merge($attributeIds, array_keys((array)$r->Attribute));
        }
        else{
            $r->Attribute = array();
        }

        $characteristics[] = $r;
    }
    $output['Characteristics'] = array();
    $output['Characteristics']["AttributeIds"] = array_unique($attributeIds);
    $output['Characteristics']["Data"] = $characteristics;

// get Attributes
    $attributeIdString = implode(",",$output['Characteristics']["AttributeIds"]);
	$attributes = array();
	if($attributeIdString)
	{
		$query = <<<STR
			SELECT manufacturerPart_attribute.Id, 
				   manufacturerPart_attribute.ParentId, 
				   manufacturerPart_attribute.Name, 
				   manufacturerPart_attribute.Type, 
				   manufacturerPart_attribute.Scale, 
				   unitOfMeasurement.Name AS UnitName, 
				   unitOfMeasurement.Unit, 
				   unitOfMeasurement.Symbol 
			FROM manufacturerPart_attribute 
			LEFT JOIN unitOfMeasurement On unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
			WHERE manufacturerPart_attribute.Id IN ($attributeIdString)

		STR;
        $result = $database->query($query);

        foreach ($result as $r)
        {
			$attributes[$r->Id] = $r;
		}
	}
    $output['Characteristics']["Attributes"] = $attributes;

    // Decode Attributes
    $dataWithAttribute = array();
    foreach ($output['Characteristics']["Data"] as $line) {
        $temp = array();
        $temp['PartNumber'] = $line->Number;
        foreach ($line->Attribute as $key => $value) {
            $attributeName = $attributes[$key]->Name;
            $temp[$attributeName] = $value;
        }
        $dataWithAttribute[] = $temp;
    }

    $output['Characteristics']["Data"] = $dataWithAttribute;

	$api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    $prefixId = intval($data->PrefixId);

    $sqlData = array();
    $sqlData['NumberingPrefixId'] = $prefixId;
    $sqlData['Number']['raw'] = "productionPart_generateNumber($prefixId)";
    $sqlData['Description'] = $data->Description;
    $sqlData['CreationUserId'] = $user->userId();

    $productionPartId = $database->insert("productionPart", $sqlData);

    $query = <<< STR
        SELECT 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartNumber
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId 
        WHERE productionPart.Id = $productionPartId;
    STR;

    $result = $database->query($query);

    $output = [];
    $output['ProductionPartNumber'] = $result[0]->ProductionPartNumber;

    $api->returnData($output);
}

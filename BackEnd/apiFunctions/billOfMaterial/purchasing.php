<?php
//*************************************************************************************************
// FileName : purchasing.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../externalApi/octopart.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($api->isGet())
{
    $parameter = $api->getGetData();

    if(!isset($parameter->RevisionId)) $api->returnParameterMissingError("RevisionId");
    $revisionId = intval($parameter->RevisionId);

    $quantity = 1;
    if(isset($parameter->Quantity)) $quantity = intval($parameter->Quantity);

    $authorizedOnly = $parameter->AuthorizedOnly ?? false;
    $includeBrokers = $parameter->Brokers ?? false;
    $includeNoStock = $parameter->NoStock ?? false;
    $knownSuppliers = $parameter->KnownSuppliers ?? true;

    $query = <<<STR
        SELECT
               COUNT(*) AS Quantity, 
               productionPart_getQuantity(productionPart.NumberingPrefixId, productionPart.Number) AS Stock, 
               productionPart.Number AS ProductionPartNumber, 
               numbering.Prefix AS ProductionPartPrefix, 
               productionPart.Description,
               manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
               vendor_displayName(vendor.Id) AS ManufacturerName,
               manufacturerPart_partNumber.OctopartId
        FROM billOfMaterial_item
        LEFT JOIN productionPart ON productionPart.Id = billOfMaterial_item.ProductionPartId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id = productionPart_manufacturerPart_mapping.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_item ON manufacturerPart_partNumber.ItemId = manufacturerPart_item.Id
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_series.VendorId OR vendor.Id = manufacturerPart_item.VendorId OR manufacturerPart_partNumber.VendorId
        WHERE BillOfMaterialRevisionId = $revisionId
        GROUP BY manufacturerPart_partNumber.Id
    STR;

    $data = array();
	$result = $database->query($query);

    $vendorList = octopart_getVendorList();

	foreach ($result AS $r)
	{
        $r->TotalQuantity = $r->Quantity*$quantity;
        $r->ProductionPartNumber = barcodeFormatter_ProductionPart($r->ProductionPartPrefix."-".$r->ProductionPartNumber);

        $octopartData = octopart_getPartData($dbLink, $r->OctopartId);

        $r->Data = octopart_formatAvailabilityData($octopartData, $vendorList, $authorizedOnly,  $includeBrokers, $includeNoStock, $knownSuppliers);

        $r->CheapestPrice = 100000000000;
        $r->CheapestSupplier = "";

        foreach ($r->Data as $key=>&$supplier)
        {
            if(!$includeNoStock && $supplier->Stock === 0){
                unset($r->Data[$key]);
                continue;
            }

            if($knownSuppliers && $supplier->VendorId === null)
            {
                unset($r->Data[$key]);
                continue;
            }

            if($supplier->MinimumOrderQuantity > $r->TotalQuantity)
            {
                unset($r->Data[$key]);
                continue;
            }

            $i = 0;
            foreach ($supplier->Prices as &$prices) {
                if($prices->Quantity > $r->TotalQuantity)break;
                $i++;
            }
            $supplier->Prices = array_values(array_slice($supplier->Prices, $i-1,2)); // Show price for set quantity and next higher quantity
			
            if(isset($supplier->Prices[0]) && $supplier->Prices[0]['Price'] < $r->CheapestPrice){
                $r->CheapestPrice = $supplier->Prices[0]['Price'] ;
                $r->CheapestSupplier = $supplier->VendorName;
            }
        }

        if($r->CheapestPrice == 100000000000) $r->CheapestPrice = null;

        $r->Data = array_values($r->Data);

        $data[] = $r;
	}

    $api->returnData($data);
}

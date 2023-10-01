<?php
//*************************************************************************************************
// FileName : purchasing.php
// FilePath : apiFunctions/billOfMaterial/
// Author   : Christian Marty
// Date		: 25.09.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../externalApi/octopart.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["RevisionId"])) sendResponse(NULL, "RevisionId Undefined");
    $revisionId = intval($_GET["RevisionId"]);

    $quantity = 1;
    if(isset($_GET["Quantity"])) $quantity = intval($_GET["Quantity"]);

    $authorizedOnly = false;
    $includeBrokers = false;
    $includeNoStock = false;
    $knownSuppliers = true;
    if(isset($_GET["AuthorizedOnly"])) $authorizedOnly = filter_var($_GET["AuthorizedOnly"],FILTER_VALIDATE_BOOLEAN);
    if(isset($_GET["Brokers"])) $includeBrokers = filter_var($_GET["Brokers"],FILTER_VALIDATE_BOOLEAN);
    if(isset($_GET["NoStock"])) $includeNoStock = filter_var($_GET["NoStock"],FILTER_VALIDATE_BOOLEAN);
    if(isset($_GET["KnownSuppliers"])) $knownSuppliers = filter_var($_GET["KnownSuppliers"],FILTER_VALIDATE_BOOLEAN);

    $dbLink = dbConnect();
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
	$result = dbRunQuery($dbLink,$query);

    $vendorList = octopart_getVendorList();
	

	while($r = mysqli_fetch_assoc($result)) 
	{
        $r['Quantity'] = intval($r['Quantity']);
        $r['TotalQuantity'] = $r['Quantity']*$quantity;
        $r['Stock'] = intval($r['Stock']);
        $r['ProductionPartNumber'] = $r['ProductionPartPrefix']."-".$r['ProductionPartNumber'];

        $start = microtime(true);
        $octopartData = octopart_getPartData($dbLink, $r['OctopartId']);
        $r['Load Time'] = microtime(true) - $start;

        $formatStart = microtime(true);
        $r['Data'] = octopart_formatAvailabilityData($octopartData, $vendorList, $authorizedOnly,  $includeBrokers, $includeNoStock, $knownSuppliers);
        $r['Format Time'] = microtime(true) - $formatStart;

        $r['CheapestPrice'] = 100000000000;
        $r['CheapestSupplier'] = "";

        $start = microtime(true);

        foreach ($r['Data'] as $key=>&$supplier) {
            if(!$includeNoStock && $supplier['Stock'] === 0){
                unset($r['Data'][$key]);
                continue;
            }

            if($knownSuppliers && $supplier['VendorId'] === null)
            {
                unset($r['Data'][$key]);
                continue;
            }

            if($supplier['MinimumOrderQuantity'] > $r['TotalQuantity'])
            {
                unset($r['Data'][$key]);
                continue;
            }

            $i = 0;
            foreach ($supplier['Prices'] as &$prices) {
                if($prices['Quantity'] > $r['TotalQuantity'])break;
                $i++;
            }
            $supplier['Prices'] = array_values(array_slice($supplier['Prices'],$i-1,2)); // Show price for set quantity and next higher quantity
			
            if(isset($supplier['Prices'][0]) && $supplier['Prices'][0]['Price'] < $r['CheapestPrice']){
                $r['CheapestPrice'] = $supplier['Prices'][0]['Price'] ;
                $r['CheapestSupplier'] = $supplier['VendorName'];
            }
        }

        if($r['CheapestPrice'] == 100000000000) $r['CheapestPrice'] = null;

        $r['Data'] = array_values($r['Data']);

        $r['Process Time'] = microtime(true) - $start;
        $data[] = $r;
	}
	
	dbClose($dbLink);
	sendResponse($data);
}

?>
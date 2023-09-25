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
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_partNumber_getVendorId(manufacturerPart_partNumber.Id)
        WHERE BillOfMaterialRevisionId = $revisionId
        GROUP BY manufacturerPart_partNumber.Id
    STR;

    $data = array();
	$result = dbRunQuery($dbLink,$query);

	while($r = mysqli_fetch_assoc($result)) 
	{
        $r['Quantity'] = intval($r['Quantity']);
        $r['TotalQuantity'] = $r['Quantity']*$quantity;
        $r['Stock'] = intval($r['Stock']);
        $r['ProductionPartNumber'] = $r['ProductionPartPrefix']."-".$r['ProductionPartNumber'];
        $r['Data'] = octopart_formatAvailabilityData(octopart_getPartData($r['OctopartId']),  $authorizedOnly,  $includeBrokers);

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
        }

        $r['Data'] = array_values($r['Data']);
        $data[] = $r;
	}
	
	dbClose($dbLink);
	sendResponse($data);
}

?>
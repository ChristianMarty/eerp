<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["ProductionPartBarcode"])) sendResponse(NULL, "Production Part Barcode Undefined");
    $productionPartBarcode= barcodeParser_ProductionPart($_GET["ProductionPartBarcode"]);

	$dbLink = dbConnect();

    $baseQuery = <<<STR
        SELECT 
            numbering.Prefix, 
            productionPart.Number, 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartBarcode,
            productionPart.Description AS ProductionPartDescription,  
            vendor.Id AS ManufacturerId,
            vendor_displayName(vendor.Id) AS ManufacturerName, 
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId, 
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber, 
            partStock.StockNo, 
            partStock.Date, 
            partStock.LotNumber,
            partStock_getQuantity(partStock.StockNo) AS Quantity, 
            productionPart.StockMinimum, 
            productionPart.StockMaximum, 
            productionPart.StockWarning,
            location_getName(partStock.LocationId) AS LocationName 
        FROM productionPart
        LEFT JOIN productionPartMapping ON productionPartMapping.ProductionPartId = productionPart.Id
        LEFT JOIN partStock ON partStock.ManufacturerPartNumberId = productionPartMapping.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_partNumber ON manufacturerPart_partNumber.Id =  productionPartMapping.ManufacturerPartNumberId
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId or vendor.Id = manufacturerPart_series.VendorId OR manufacturerPart_partNumber.VendorId
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId        
    STR;

    $queryParam = array();
    $queryParam[] = "CONCAT(numbering.Prefix,'-',productionPart.Number) = '$productionPartBarcode'";
    if(isset($_GET["HideEmptyStock"]))
    {
        if(filter_var($_GET["HideEmptyStock"], FILTER_VALIDATE_BOOLEAN)) $queryParam[] = "(partStock.Cache_Quantity != '0' OR partStock.Cache_Quantity IS NULL)";
    }

    $query = dbBuildQuery($dbLink,$baseQuery,$queryParam);
	$result = mysqli_query($dbLink,$query);

	$output = array();
    $totalStockQuantity = 0;
    while($r = mysqli_fetch_assoc($result)) {
        if (!isset($output['ProductionPartBarcode'])) // First row
        {
            $output['ProductionPartBarcode'] = $r['ProductionPartBarcode'];
            $output['Description'] = $r['ProductionPartDescription'];
            $output['StockMinimum'] = $r['StockMinimum'];
            $output['StockMaximum'] = $r['StockMaximum'];
            $output['StockWarning'] = $r['StockWarning'];
            $output['Stock'] = array();
            $output['ManufacturerPart'] = array();
        }

        $stockRow = array();
        if ($r['StockNo'] != null)
        {
            $stockRow['StockNumber'] = $r['StockNo'];
            $stockRow['StockBarcode'] = barcodeFormatter_StockNumber($r['StockNo']);
            $stockRow['Date'] = $r['Date'];
            $stockRow['Lot'] = $r['LotNumber'];
            $stockRow['Quantity'] = $r['Quantity'];
            $totalStockQuantity += $stockRow['Quantity'];
            $stockRow['LocationName'] = $r['LocationName'];
        }
        $output['Stock'][] = $stockRow;

        $manufacturerRow = array();
        $manufacturerRow['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
        $manufacturerRow['ManufacturerPartNumberId'] = intval($r['ManufacturerPartNumberId']);
        $manufacturerRow['ManufacturerName'] = $r['ManufacturerName'];
        $manufacturerRow['ManufacturerId'] = intval($r['ManufacturerId']);
        $output['ManufacturerPart'][] = $manufacturerRow;
    }
    $output['TotalStockQuantity'] = $totalStockQuantity;

	/*$manufacturerParts = array();
	$manufacturerParts[] = array();


    if(!array_key_exists($r['PartId'],$manufacturerParts))
    {
        $Part = array();
        $Part['ManufacturerName'] = $r['ManufacturerName'];
        $Part['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
        $Part['LifecycleStatus'] = $r['LifecycleStatus'];
        $Part['PartId'] = $r['PartId'];
        $Part['Description'] = "";

        $manufacturerParts[$r['PartId']] = $Part;
        $manufacturerParts[$r['PartId']]['Stock'] = array();
    }

    $StockRow = array();
    $StockRow['StockNo'] = $r['StockNo'];
    $StockRow['Date'] = $r['Date'];
    $StockRow['Quantity'] = $r['Quantity'];
    $StockRow['LocationName'] = $r['LocationName'];
    $StockRow['PartId'] = $r['PartId']+10;


    $manufacturerParts[$r['PartId']]['Stock'][] = $StockRow;

	unset($manufacturerParts[0]);
	$manufacturerParts = array_values($manufacturerParts);
	
	$totalStockQuantity = 0;
	foreach($manufacturerParts as &$item)
	{
		$totalPartQuantity = 0;
		foreach($item['Stock'] as $StockItem) $totalPartQuantity += $StockItem['Quantity'];
		$item['Quantity'] = $totalPartQuantity;
		$totalStockQuantity += $totalPartQuantity;
	}

    $output['TotalStockQuantity'] = $totalStockQuantity;
    $output['ManufacturerParts'] = $manufacturerParts;*/
	
	
	dbClose($dbLink);

	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();

    $prefixId = intval($data['PrefixId']);

    $sqlData = array();
    $sqlData['NumberingPrefixId'] = $prefixId;
    $sqlData['Number']['raw'] = "productionPart_generateNumber($prefixId)";
    $sqlData['Description'] = dbEscapeString($dbLink,$data['Description']);

    $query = dbBuildInsertQuery($dbLink,"productionPart", $sqlData);

    $query .= <<< STR
        SELECT CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartNumber
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId 
        WHERE productionPart.Id = LAST_INSERT_ID();
    STR;

    $error = null;
    $output = array();

    if(mysqli_multi_query($dbLink,$query))
    {
        do {
            if ($result = mysqli_store_result($dbLink)) {
                while ($row = mysqli_fetch_row($result)) {
                    $output['ProductionPartNumber'] = $row[0];
                }
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($dbLink)) break;
        } while (mysqli_next_result($dbLink));
    }
    else
    {
        $error = "Error description: " . mysqli_error($dbLink);
    }

    dbClose($dbLink);
    sendResponse($output,$error);
}

?>
<?php
//*************************************************************************************************
// FileName : partPurchase.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 15.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["ManufacturerPartId"]) && !isset($_GET["ProductionPartNumber"])) sendResponse(NULL,"ManufacturerPartId or ProductionPartNumber Required!");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query  = "SELECT *, SUM(QuantityReceived) AS TotalQuantityReceived FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN supplierPart ON supplierPart.Id = purchasOrder_itemOrder.SupplierPartId  ";
	$query .= "LEFT JOIN purchasOrder ON purchasOrder.Id = purchasOrder_itemOrder.PurchasOrderId ";
	$query .= "LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = supplierPart.ManufacturerPartId ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
    $query .= "LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId ";
	
	$parameters = array();
	
	if(isset($_GET["ManufacturerPartId"]))
	{
		$parameters[] = 'supplierPart.ManufacturerPartId = ' . dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
	}
	else if(isset($_GET["ProductionPartNumber"]))
	{
		$parameters[] = "CONCAT(numbering.Prefix,'-',productionPart.Number) = '" . dbEscapeString($dbLink, $_GET["ProductionPartNumber"]) . "'";
	}
	else
	{
		sendResponse(NULL,"Parameter Error!");
	}
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$query .= " GROUP BY purchasOrder_itemOrder.PurchasOrderId";

	$result = dbRunQuery($dbLink,$query);

	$rows = array();
	$rowcount = mysqli_num_rows($result);
	$totalQuantity = 0;
	$receivedQuantity = 0;

    $priceMinimum = 100000000;
    $priceMaximum = 0;
    $priceAverageSum = 0;
    $priceWeightedAverageSum = 0;
    $priceWeightSum = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		$totalQuantity += $r['Quantity'];
		$receivedQuantity += $r['TotalQuantityReceived'];

        if($r['Price'] < $priceMinimum ) $priceMinimum = $r['Price'];
        if($r['Price'] > $priceMaximum ) $priceMaximum = $r['Price'];

        $priceAverageSum +=  $r['Price'];
        $priceWeightedAverageSum +=  $r['Price'] * $r['Quantity'];
        $priceWeightSum += $r['Quantity'];
		
		$rows[] = $r;
	}
	
	$output = array();

    $output['Statistics'] = array();
    $output['Statistics']['Quantity'] = array();
	$output['Statistics']['Quantity']['Ordered'] = $totalQuantity;
	$output['Statistics']['Quantity']['Pending'] = $totalQuantity - $receivedQuantity;
	$output['Statistics']['Quantity']['Received'] = $receivedQuantity;
    $output['Statistics']['Price'] = array();
    if(count($rows) != 0)
    {
        $output['Statistics']['Price']['Minimum'] = round($priceMinimum, 6);
        $output['Statistics']['Price']['Maximum'] = round($priceMaximum, 6);
        $output['Statistics']['Price']['Average'] = round($priceAverageSum / count($rows), 6);
        $output['Statistics']['Price']['WeightedAverage'] = round($priceWeightedAverageSum / $priceWeightSum, 6);
    }
    else
    {
        $output['Statistics']['Price']['Minimum'] = null;
        $output['Statistics']['Price']['Maximum'] = null;
        $output['Statistics']['Price']['Average'] = null;
        $output['Statistics']['Price']['WeightedAverage'] = null;
    }

	$output['Data'] = $rows;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>
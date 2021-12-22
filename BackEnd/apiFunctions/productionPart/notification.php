<?php
//*************************************************************************************************
// FileName : notification.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.12.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT productionPart.PartNo, productionPart_getQuantity(productionPart.PartNo) as StockQuantity, productionPart_stockNotification.StockMinimum, productionPart_stockNotification.StockMaximum, productionPart_stockNotification.StockWarning FROM productionPart "; 
	$query .= "LEFT JOIN productionPart_stockNotification ON productionPart_stockNotification.PartNo = productionPart.PartNo ";
	
	
	$result = mysqli_query($dbLink,$query);
	
	$rows = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		
		
		$quantity = intval($r['StockQuantity']);
		$minimum = intval($r['StockMinimum']);
		$maximum = intval($r['StockMaximum']);
		$warning = intval($r['StockWarning']);
		
		$status = null;
		if( $quantity < $minimum and $minimum != Null) $status = "Minimum";
		else if( $quantity < $warning and $warning != Null) $status = "Warning";
		else if( $quantity > $maximum  and $maximum != Null ) $status = "Maximum";
		
		
		$r["Status"] = $status;
		
		if($status != null) array_push($rows, $r);
		

	}

	dbClose($dbLink);

	sendResponse($rows);
}

?>
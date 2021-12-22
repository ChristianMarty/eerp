<?php
//*************************************************************************************************
// FileName : summary.php
// FilePath : apiFunctions/productionPart/notification
// Author   : Christian Marty
// Date		: 01.12.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query = "SELECT productionPart.PartNo, productionPart_getQuantity(productionPart.PartNo) as StockQuantity, productionPart_stockNotification.StockMinimum, productionPart_stockNotification.StockMaximum, productionPart_stockNotification.StockWarning FROM productionPart "; 
	$query .= "LEFT JOIN productionPart_stockNotification ON productionPart_stockNotification.PartNo = productionPart.PartNo ";
	$query .= "WHERE StockMinimum IS NOT Null OR StockMaximum IS NOT NULL OR StockWarning IS NOT Null";
	
	//echo $query;
	//exit;
	
	$result = mysqli_query($dbLink,$query);
	
	
	
	$warningNotifications = 0;
	$minimumNotifications = 0;
	$maximumNotifications = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$quantity = intval($r['StockQuantity']);
		$minimum = intval($r['StockMinimum']);
		$maximum = intval($r['StockMaximum']);
		$warning = intval($r['StockWarning']);
		
		if( $quantity < $minimum and $minimum != Null) $minimumNotifications ++;
		else if( $quantity < $warning and $warning != Null) $warningNotifications ++;
		else if( $quantity > $maximum  and $maximum != Null ) $maximumNotifications ++;	
	}

	dbClose($dbLink);
	
	$output = array();
	
	$output['Warning'] = $warningNotifications;
	$output['Minimum'] = $minimumNotifications;
	$output['Maximum'] = $maximumNotifications;
	
	sendResponse($output);
}

?>
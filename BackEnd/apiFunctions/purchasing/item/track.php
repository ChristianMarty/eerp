<?php
//*************************************************************************************************
// FileName : track.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["ReceivalId"]))sendResponse(null, "ReceivalId not specified");
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$receivalId = intval(dbEscapeString($dbLink, $_GET["ReceivalId"]));
	$output = array();
	
	$query  = "SELECT StockNo,  partStock_history.Quantity AS CreateQuantity FROM partStock ";
	$query .= "LEFT JOIN partStock_history ON partStock_history.StockId = partStock.Id ";
	$query .= "WHERE ReceivalId = ".$receivalId." AND partStock_history.ChangeType = 'Create' ";
	
	$result = dbRunQuery($dbLink,$query);

	while($r = mysqli_fetch_assoc($result)) 
	{
        $temp = array();
        $temp['Barcode'] = 'Stk-'.$r['StockNo'];
        $temp['Type'] = "Part Stock";
        $temp['Description'] = null;
        $temp['CreateQuantity'] = $r['CreateQuantity'];;
		$output[] = $temp;
	}
	
	$query = "SELECT InvNo, Title FROM inventory ";
	$query .= "LEFT JOIN inventory_purchasOrderReference ON inventory_purchasOrderReference.InventoryId = inventory.Id ";
	$query .= "WHERE ReceivalId = ".$receivalId;
	$result = dbRunQuery($dbLink,$query);

	while($r = mysqli_fetch_assoc($result)) 
	{
        $temp = array();
        $temp['Barcode'] = 'Inv-'.$r['InvNo'];
        $temp['Type'] = "Inventory";
        $temp['CreateQuantity'] = null;
        $temp['Description'] = $r['Title'];
		$output[] = $temp;
	}


	dbClose($dbLink);	
	sendResponse($output);
}


?>
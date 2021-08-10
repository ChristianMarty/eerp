<?php
//*************************************************************************************************
// FileName : history.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
		
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$temp = dbEscapeString($dbLink, $_GET["StockNo"]);
	$temp = strtolower($temp);
	$stockNo = str_replace("stk-","",$temp);
	
	$query = "SELECT ChangeType, Quantity, Date FROM partStock_history ";
	$query .= "WHERE StockId = (SELECT Id FROM partStock WHERE StockNo = '".$stockNo."') ";
	$query .="ORDER BY Id ASC";
	

	$result = dbRunQuery($dbLink,$query);
	$output = array();
	$gctNr = null;
	
	$quantity = 0;
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$description = "";
		$type = null;
		
		
		if($r["ChangeType"] == 'Relative')
		{
			if($r['Quantity'] >0 ) 
			{
				$description = "Add ".$r['Quantity']."pcs"; 
				$type = "add";
				$quantity += intval($r['Quantity'],10);
			}
			else 
			{
				$description = "Remove ".abs($r['Quantity'])."pcs";
				$type = "remove";	
				$quantity += intval($r['Quantity'],10);		
			}	
		}
		else if($r["ChangeType"] == 'Absolute')
		{
			$description = "Stocktaking"; 
			$type = "set";	
			$quantity = intval($r['Quantity'],10);
		}
		else if($r["ChangeType"] == 'Create')
		{
			$description = "Create"; 
			$type = "create";	
			$quantity = intval($r['Quantity'],10);
		}
		
		$description .= ", New Quantity: ".$quantity;
		
		$r['Type'] = $type;
		$r['Description'] = $description;
		array_push($output, $r);
	}
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>

<?php
//*************************************************************************************************
// FileName : accuracy.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 05.01.2022
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
	
	$query  = "SELECT * FROM partStock_history_sinceLastCount ";
	$query .= "WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '".$stockNo."') ";
	
	$result = dbRunQuery($dbLink,$query);
	
	$daysSinceStocktaking = NULL;
	$lastStocktakingDate = NULL;
	$certaintyFactor = 1;
	
	$movements = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		if($r['ChangeType'] == 'Absolute' || $r['ChangeType'] == 'Create')
		{
			$earlier = new DateTime($r['Date']);
			$later = new DateTime();
			
			$daysSinceStocktaking = $later->diff($earlier)->format("%a");
			$lastStocktakingDate = $r['Date'];
		}
		else
		{
			
			array_push($movements, $r);
		}
	}
	
	if($daysSinceStocktaking <= 1) 
	{
		$certaintyFactor = 1; // If counted today or yesterday
	}
	else
	{
		
		// TODO: Make this better
		$noOfMoves = count($movements);
		$certaintyFactor -= ($noOfMoves*0.025);
		
		$certaintyFactor -= ($daysSinceStocktaking*0.0025);
	}
	
	$output = array();
	$output['CertaintyFactor'] = round($certaintyFactor,4);
	$output['DaysSinceStocktaking'] = $daysSinceStocktaking;
	$output['LastStocktakingDate'] = $lastStocktakingDate;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>

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
require_once __DIR__ . "/../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["StockNo"]))sendResponse(null, "StockNo not specified");
	$stockNumber = barcodeParser_StockNumber($_GET["StockNo"]);
	if(!$stockNumber) sendResponse(null, "StockNo invalid");

	$dbLink = dbConnect();

	$query = <<<STR
		SELECT * FROM partStock_history_sinceLastCount
		WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '$stockNumber')
	STR;
	
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
			$movements[] = $r;
		}
	}
	
	if($daysSinceStocktaking > 1) // If not counted today
	{
		// TODO: Make this better
		$noOfMoves = count($movements);
		$certaintyFactor -= ($noOfMoves*0.025);
		
		$certaintyFactor -= ($daysSinceStocktaking*0.0025);
		
		if($certaintyFactor<0) $certaintyFactor = 0;
	}
	
	$output = array();
	$output['CertaintyFactor'] = round($certaintyFactor,4);
	$output['CertaintyFactorRating'] = round($output['CertaintyFactor']*5);
	$output['DaysSinceStocktaking'] = $daysSinceStocktaking;
	$output['LastStocktakingDate'] = $lastStocktakingDate;
	
	dbClose($dbLink);	
	sendResponse($output);
}
?>

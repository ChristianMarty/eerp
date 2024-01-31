<?php
//*************************************************************************************************
// FileName : accuracy.php
// FilePath : apiFunctions/stock/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeParser.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	if(!isset($parameter->StockNo)) $api->returnParameterMissingError("StockNo");
	$stockNumber = barcodeParser_StockNumber($parameter->StockNo);
	if($stockNumber === null) $api->returnParameterError("StockNo");

	$query = <<<STR
		SELECT * FROM partStock_history_sinceLastCount
		WHERE StockId = (SELECT partStock.Id FROM partStock WHERE StockNo = '$stockNumber')
	STR;
	
	$result = $database->query($query);
	
	$daysSinceStocktaking = NULL;
	$lastStocktakingDate = NULL;
	$certaintyFactor = 1;
	
	$movements = array();
	foreach ($result as $item)
	{
		if($item->ChangeType == 'Absolute' || $item->ChangeType == 'Create')
		{
			$earlier = new DateTime($item->CreationDate);
			$later = new DateTime();
			
			$daysSinceStocktaking = $later->diff($earlier)->format("%a");
			$lastStocktakingDate = $item->CreationDate;
		}
		else
		{
			$movements[] = $item;
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
	
	$api->returnData($output);
}

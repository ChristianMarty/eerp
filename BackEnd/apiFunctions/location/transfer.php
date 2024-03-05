<?php
//*************************************************************************************************
// FileName : transfer.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

function filterInv($var): int
{
    if(barcodeParser_InventoryNumber($var) === null) return 0;
    else return 1;
}

function filterStk($var): int
{
    if(barcodeParser_StockNumber($var) === null) return 0;
    else return 1;
}

function filterLoc($var): int
{
    if(barcodeParser_LocationNumber($var) === null) return 0;
    else return 1;
}

function filterAsu($var): int
{
    if(barcodeParser_AssemblyUnitNumber($var) === null) return 0;
    else return 1;
}

function moveItems($itemList, $locationNr, string $category, string $idName): string
{
	global $database;

	foreach($itemList as &$item)
	{
		$item = $database->escape(explode("-", $item)[1]);
	}
	$itemListStr = implode(",",$itemList);

	$baseQuery = <<<STR
		UPDATE $category
		SET LocationId = (SELECT `Id` FROM `location` WHERE `LocationNumber`= '$locationNr')
		WHERE $idName IN($itemListStr)
	STR;


	$database->execute($baseQuery);

	return $database->getErrorMessage()??"";
}


if($api->isPost())
{
	$data = $api->getPostData();

	if(!isset($data->DestinationLocationNumber)) $api->returnParameterMissingError("DestinationLocationNumber");
	$locationNr = barcodeParser_LocationNumber($data->DestinationLocationNumber);
	if($locationNr == null)  $api->returnParameterError("DestinationLocationNumber");

	if(!isset($data->TransferList)) $api->returnParameterMissingError("TransferList");

	$itemList =  $data->TransferList;

	// Split into different categories
	$invList = array_filter($itemList, "filterInv");
	$stkList = array_filter($itemList, "filterStk");
	$locList = array_filter($itemList, "filterLoc");
	$asuList = array_filter($itemList, "filterAsu");
	
	$error = "";
	if(!empty($invList)) $error .= moveItems( $invList, $locationNr, "inventory", "InvNo");
	if(!empty($stkList)) $error .= moveItems( $stkList, $locationNr, "partStock", "StockNo");
	if(!empty($locList)) $error .= moveItems( $locList, $locationNr, "location", "LocationNumber");
	if(!empty($asuList)) $error .= moveItems( $asuList, $locationNr, "assembly_unit", "AssemblyUnitNumber");
	
	if(empty($error)) $api->returnEmpty();
	else $api->returnError($error);
}

<?php
//*************************************************************************************************
// FileName : transfer.php
// FilePath : apiFunctions/location/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

function filterInv($var)
{
	if(explode("-",strtolower($var))[0] == "inv") return 1;
	else return 0;
}

function filteStk($var)
{
	if(explode("-",strtolower($var))[0] == "stk") return 1;
	else return 0;
}

function filterLoc($var)
{
	if(explode("-",strtolower($var))[0] == "loc") return 1;
	else return 0;
}

function moveItems($dbLink, $itemList, $locationNr, $catogery, $idName)
{
	$baseQuery = "UPDATE `".$catogery."` ";
	$baseQuery  .= "SET LocationId = (SELECT `Id` FROM `location` WHERE `LocNr`= '".$locationNr."')";

	foreach($itemList as &$item) 
	{
		$item = explode("-", $item)[1];
		$item = dbEscapeString($dbLink,$item);
	}

	$baseQuery  .= "WHERE ".$idName." IN('".implode("', '",$itemList)."')";
	
	if(!mysqli_multi_query($dbLink,$baseQuery))
	{
		return "Error description: " . mysqli_error($dbLink);
	}
}


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();

	$locationNr = dbEscapeString($dbLink,$data['data']['LocNr']);
	$itemList = $data['data']['ItemList'];
	
	if(substr(strtolower($locationNr),0,4) != "loc-")  sendResponse(null,"Invalid destination location");
	$locationNr = str_replace("Loc-","",$locationNr);
	
	// Split into different categories
	$invList = array_filter($itemList, "filterInv");
	$stkList = array_filter($itemList, "filteStk");
	$locList = array_filter($itemList, "filterLoc");
	
	$error = "";
	if(!empty($invList)) $error .= moveItems($dbLink, $invList, $locationNr, "inventory", "InvNo");
	if(!empty($stkList)) $error .= moveItems($dbLink, $stkList, $locationNr, "partStock", "StockNo");
	if(!empty($locList)) $error .= moveItems($dbLink, $locList, $locationNr, "location", "LocNr");
	
	if(empty($error)) $error = null;
	
	dbClose($dbLink);	
	sendResponse(null,$error);
}
?>

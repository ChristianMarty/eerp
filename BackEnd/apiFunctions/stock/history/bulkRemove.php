<?php
//*************************************************************************************************
// FileName : bulkRemove.php
// FilePath : apiFunctions/stock/history/
// Author   : Christian Marty
// Date		: 11.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../util/location.php";


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$workOrderNo = null;
	if(isset($data['WorkOrderNo']) and $data['WorkOrderNo'] !== null)
	{
		$workOrderNo = dbEscapeString($dbLink,$data['WorkOrderNo']);
	}
	
	$workOrder = null;
	if($workOrderNo != 0)
	{
		$query = "SELECT * FROM workOrder WHERE WorkOrderNo =".$workOrderNo;
		$result = dbRunQuery($dbLink,$query);	
		$workOrder = mysqli_fetch_assoc($result);
	}

	$partList = $data['Data']['Items'];
	
	dbClose($dbLink);
	
	$error = null;
	// validate data
	foreach($partList as $key => $line)
	{
		$dbLink = dbConnect();
		
		$note = null;
		if(isset($line['Note']) and $line['Note'] !== null)
		{
			$note = dbEscapeString($dbLink,$line['Note']);
			$note = trim($note);
			if($note == "") $note = null;
		}
		
		$temp = dbEscapeString($dbLink, $line["Barcode"]);
		$temp = strtolower($temp);
		$stockNo = str_replace("stk-","",$temp);
		
		$removeQuantity = dbEscapeString($dbLink, $line["RemoveQuantity"]);
		
		$sqlData = array();

		$sqlData['Note'] = $note;
		$sqlData['EditToken']['raw'] = "history_generateEditToken()";
		$sqlData['StockId']['raw'] = '(SELECT Id FROM partStock WHERE StockNo = "'.$stockNo.'")';
		$sqlData['Quantity'] = abs($removeQuantity)*-1;
		$sqlData['ChangeType']['raw'] = '"Relative"';
		
		if($workOrder != null)
		{
			$sqlData['WorkOrderId'] = $workOrder['Id'];
		}
		
		$query = dbBuildInsertQuery($dbLink,"partStock_history", $sqlData);

		$result = dbRunQuery($dbLink,$query);
		
		$msg = mysqli_error($dbLink);
		if($msg != "")
		{
			$error = "Error description: " . mysqli_error($dbLink);
			break;
		}
		
		dbClose($dbLink);

		
	}
		
	sendResponse(null,$error);
}

?>

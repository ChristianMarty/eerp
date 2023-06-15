<?php
//*************************************************************************************************
// FileName : costCenter.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	
	$query = "SELECT * FROM finance_costCenter ";
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	while($r = mysqli_fetch_assoc($result))
	{
        $r['Barcode'] = barcodeFormatter_CostCenter($r['CostCenterNumber']);
		$r['Id'] = intval($r['Id']);
		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}
?>
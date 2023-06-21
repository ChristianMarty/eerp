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
	
	$query = <<<STR
        SELECT 
            finance_costCenter.Name AS Name, 
            finance_costCenter.CostCenterNumber, 
            finance_costCenter.Description, 
            finance_costCenter.ProjectId, 
            finance_costCenter.Color,
            project.Title AS ProjectName
        FROM finance_costCenter
        LEFT JOIN project ON project.Id = finance_costCenter.ProjectId = project.Id
    STR;
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	while($r = mysqli_fetch_assoc($result))
	{
        $r['Barcode'] = barcodeFormatter_CostCenter($r['CostCenterNumber']);
		$output[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($output);
}
?>
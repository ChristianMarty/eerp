<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/project/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	
	if(!isset($_GET["ProjectNumber"])) sendResponse(NULL, "Project Number Undefined");
    $projectNumber = barcodeParser_Project($_GET["ProjectNumber"]);

	$dbLink = dbConnect();

    $query = <<< STR
        SELECT * FROM project 
        WHERE project.ProjectNumber = $projectNumber
    STR;
	
	$result = mysqli_query($dbLink,$query);
	$r = mysqli_fetch_assoc($result);

    $r["ProjectBarcode"] = barcodeFormatter_Project($r['ProjectNumber']);

	
	dbClose($dbLink);

	sendResponse($r);
}

?>
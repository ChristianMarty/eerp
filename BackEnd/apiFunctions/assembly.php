<?php
//*************************************************************************************************
// FileName : assembly.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 16.08.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/util/_barcodeFormatter.php";
require_once __DIR__ . "/util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$query  = "SELECT * FROM assembly ";
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AssemblyBarcode'] = barcodeFormatter_AssemblyNumber($r['AssemblyNumber']);
		$assembly[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($assembly);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	if(!isset($data['Name'])) sendResponse(null,"Name missing");
	
	$dbLink = dbConnect();

	$name = dbEscapeString($dbLink,$data['Name']);
	$description = dbEscapeString($dbLink,$data['Description']);
	
	
	$sqlData = array();
	$sqlData['Name'] = $name;
	$sqlData['Description']  = $description;
	$sqlData['AssemblyNumber']['raw'] = "(SELECT generateItemNumber())";
	
	$query = dbBuildInsertQuery($dbLink,"assembly", $sqlData);
	
	$query .= " SELECT AssemblyNumber FROM assembly WHERE Id = LAST_INSERT_ID();";

	$error = null;
	$output = null;
	
	if(mysqli_multi_query($dbLink,$query))
	{
		do {
			if ($result = mysqli_store_result($dbLink)) {
				while ($row = mysqli_fetch_row($result)) {
					$output = barcodeFormatter_AssemblyNumber($row[0]);
				}
				mysqli_free_result($result);
			}
			if(!mysqli_more_results($dbLink)) break;
		} while (mysqli_next_result($dbLink));
	}
	else
	{
		$error = "Error description: " . mysqli_error($dbLink);
	}
	
	dbClose($dbLink);	
	sendResponse($output, $error);
}

?>
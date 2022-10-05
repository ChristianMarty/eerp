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
require __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

	$query  = "SELECT * FROM assembly ";
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AssemblyBarcode'] = "ASM-".$r['AssemblyNo'];
		$assembly[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($assembly);
}

?>
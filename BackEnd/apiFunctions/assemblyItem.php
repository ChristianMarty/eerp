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

	$query  = "SELECT * FROM assembly_item ";
	$query .= "LEFT JOIN assembly ON assembly.Id = assembly_item.AssemblyId ";
	
	if(isset($_GET["SerialNumber"]))
	{
		$sn = dbEscapeString($dbLink, $_GET["SerialNumber"]);
		$query .= "WHERE SerialNumber LIKE '".$sn."'";
	}
	
	$queryParam = array();

	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AssemblyBarcode'] = "ASM-".$r['AssemblyNo'];
		$r['AssemblyItemBarcode'] = "ASI-".$r['AssemblyItemNo'];
		$assembly[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($assembly);
}

?>
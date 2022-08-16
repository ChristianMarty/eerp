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

	$query = "SELECT *, location_getName(LocationId) AS LocationName FROM assembly ";	
	
	$queryParam = array();

	if(isset($_GET["AssemblyNo"]))
	{
		$temp = dbEscapeString($dbLink, $_GET["AssemblyNo"]);
		$temp = strtolower($temp);
		$temp = str_replace("asm-","",$temp);
		array_push($queryParam, "AssemblyNo LIKE '".$temp."'");		
	}
	
	$query = dbBuildQuery($dbLink, $query, $queryParam);
	
	$result = dbRunQuery($dbLink,$query);
	
	$assembly = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['Barcode'] = "ASM-".$r['AssemblyNo'];
		$assembly[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($assembly);
}

?>
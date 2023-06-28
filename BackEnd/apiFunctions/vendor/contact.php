<?php
//*************************************************************************************************
// FileName : contact.php
// FilePath : apiFunctions/vendor
// Author   : Christian Marty
// Date		: 08.05.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
	
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

	$query = "SELECT * FROM vendor_contact ";
	if(isset($_GET["VendorId"])) $query .= "WHERE  VendorId = ".dbEscapeString($dbLink, $_GET["VendorId"]);
	
	$result = dbRunQuery($dbLink,$query);
	
	$address = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['Id'] = intval($r['Id']);
		$r['VendorId'] = intval($r['VendorId']);
		$r['VendorAddressId'] = intval($r['VendorAddressId']);
		$address[] = $r;
	}

	dbClose($dbLink);
	sendResponse($address);
}
?>
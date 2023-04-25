<?php
//*************************************************************************************************
// FileName : information.php
// FilePath : apiFunctions/vendor/api/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SupplierId"])) sendResponse(null, "SupplierId missing!");

    $dbLink = dbConnect();
    $supplierId = dbEscapeString($dbLink, $_GET["SupplierId"]);

    $query = "SELECT * FROM vendor WHERE Id = ".$supplierId.";";
    $result = dbRunQuery($dbLink,$query);
    $supplierData = mysqli_fetch_assoc($result);
    dbClose($dbLink);

    $name = $supplierData['API'];
    if($name === null) sendResponse(null, "Supplier not supported!");

    $path =  __DIR__ . "/../../externalApi/".$name."/".$name.".php";
    require $path;

    $data = call_user_func($name."_apiInfo");
		
	sendResponse($data);
}
?>
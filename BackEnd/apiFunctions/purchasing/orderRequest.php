<?php
//*************************************************************************************************
// FileName : orderRequest.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 15.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$query   = "SELECT orderRequest.Id AS OrderRequestId, GROUP_CONCAT(PartNo) AS PartNoList, supplierPart.ManufacturerPartId, orderRequest.SupplierPartId, supplier.Id AS SupplierId, supplier.Name AS SupplierName, supplierPart.SupplierPartNumber, supplierPart.SupplierPartLink, Quantity, CreationDate, ";
	$query  .= "manufacturerPart.ManufacturerPartNumber, partManufacturer.Name AS ManufacturerName ";
	$query  .= "FROM orderRequest ";
	$query  .= "LEFT JOIN supplierPart ON supplierPart.Id = orderRequest.SupplierPartId ";
	$query  .= "LEFT JOIN supplier ON supplier.Id = supplierPart.SupplierId ";
	$query  .= "LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId ";
	$query  .= "LEFT JOIN partManufacturer ON partManufacturer.Id = manufacturerPart.ManufacturerId ";
	
	$query  .= "LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = manufacturerPart.Id ";
	$query  .= "LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId ";
	
	if(isset($_GET["ManufacturerPartId"])) $manufacturerPartId =  dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
	if(isset($_GET["SupplierId"])) $supplierId =  dbEscapeString($dbLink, $_GET["SupplierId"]);
	
	$parameters = array();
	if(isset($manufacturerPartId)) array_push($parameters, 'supplierPart.ManufacturerPartId = '. $manufacturerPartId);
	if(isset($supplierId)) array_push($parameters, 'supplier.Id = '. $supplierId);
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$query  .= " GROUP BY ManufacturerPartId";

	$result = dbRunQuery($dbLink,$query);

	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		array_push($rows,$r);	
	}

	dbClose($dbLink);	
	sendResponse($rows);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$supplierPartId = $data['data']['SupplierPartId'];
	$quantity = $data['data']['Quantity'];
	$description = $data['data']['Description'];
	
	$row = array();
	$row['Description'] = $description;
	$row['SupplierPartId'] = $supplierPartId;
	$row['Quantity'] = $quantity;
	
	$query = dbBuildInsertQuery($dbLink, "orderRequest",$row);
	
	$result = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);	
	
	
	sendResponse(null,null);
}
?>
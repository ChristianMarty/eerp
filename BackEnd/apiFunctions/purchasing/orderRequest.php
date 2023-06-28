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

    $query = <<< STR
        SELECT 
            orderRequest.Id AS OrderRequestId, 
            GROUP_CONCAT(productionPart.Number) AS PartNoList, 
            supplierPart.ManufacturerPartId, 
            orderRequest.SupplierPartId, 
            vendor.Id AS SupplierId, 
            vendor.Name AS SupplierName, 
            supplierPart.SupplierPartNumber, 
            supplierPart.SupplierPartLink, 
            Quantity, 
            CreationDate, 
            orderRequest.Description,
            mfrPart.ManufacturerPartNumber, 
            mfrPart.ManufacturerName
        FROM orderRequest
        LEFT JOIN supplierPart ON supplierPart.Id = orderRequest.SupplierPartId
        LEFT JOIN vendor ON vendor.Id = supplierPart.VendorId
        LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId
        LEFT JOIN (
            SELECT 
                ManufacturerPartNumber, 
                manufacturerPart.Id AS Id, 
                vendor.Name AS ManufacturerName 
            FROM manufacturerPart 
                LEFT JOIN vendor On vendor.Id = manufacturerPart.VendorId
            )mfrPart On mfrPart.Id = supplierPart.ManufacturerPartId 
        LEFT JOIN productionPartMapping ON productionPartMapping.ManufacturerPartId = mfrPart.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPartMapping.ProductionPartId
    STR;

	if(isset($_GET["ManufacturerPartId"])) $manufacturerPartId =  dbEscapeString($dbLink, $_GET["ManufacturerPartId"]);
	if(isset($_GET["SupplierId"])) $supplierId =  dbEscapeString($dbLink, $_GET["SupplierId"]);
	
	$parameters = array();
	if(isset($manufacturerPartId)) $parameters[] = 'supplierPart.ManufacturerPartId = ' . $manufacturerPartId;
	if(isset($supplierId)) $parameters[] = 'vendor.Id = ' . $supplierId;
	
	$query = dbBuildQuery($dbLink, $query, $parameters);
	
	$query  .= " GROUP BY ManufacturerPartId";

	$result = dbRunQuery($dbLink,$query);

	$rows = array();
	$rowcount = mysqli_num_rows($result);
	while($r = mysqli_fetch_assoc($result)) 
	{
		unset($r['Id']);
		$rows[] = $r;
	}

	dbClose($dbLink);	
	sendResponse($rows);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	
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
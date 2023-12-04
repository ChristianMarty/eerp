<?php
//*************************************************************************************************
// FileName : orderRequest.php
// FilePath : apiFunctions/purchasing
// Author   : Christian Marty
// Date		: 15.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    /*
    $query = <<< STR
        SELECT 
            orderRequest.Id AS OrderRequestId, 
            GROUP_CONCAT(productionPart.Number) AS PartNoList, 
            supplierPart.ManufacturerPartId, 
            orderRequest.SupplierPartId, 
            vendor.Id AS SupplierId, 
            vendor_displayName(vendor.Id) AS SupplierName, 
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
                vendor_displayName(vendor.Id) AS ManufacturerName 
            FROM manufacturerPart 
                LEFT JOIN vendor On vendor.Id = manufacturerPart.VendorId
            )mfrPart On mfrPart.Id = supplierPart.ManufacturerPartId 
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartId = mfrPart.Id
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId
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
	}//*/

	$api->returnError("Not Implemented");
}
else if($api->isPost())
{
	$data = $api->getPostData()->data;

	$row = array();
	$row['Description'] = $data->Description;
	$row['SupplierPartId'] = $data->SupplierPartId;
	$row['Quantity'] = $data->Quantity;

    $database->insert("orderRequest",$row);

    $api->returnEmpty();

}

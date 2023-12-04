<?php
//*************************************************************************************************
// FileName : supplierPart.php
// FilePath : apiFunctions/supplier/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();
	
	if(isset($parameter->ManufacturerPartNumberId)) $manufacturerPartNumberId = intval($parameter->ManufacturerPartNumberId);
	if(isset($parameter->SupplierId)) $supplierId =  intval($parameter->SupplierId);
	if(isset($parameter->ProductionPartNo)) $productionPartNo =  $database->escape($parameter->ProductionPartNo);
	
	$supplierData = array();
    $query = <<<STR
        SELECT *, 
               manufacturerPart_partNumber.Number AS ManufacturerPartNumbe, 
               vendor.Id AS SupplierId,
               vendor_displayName(vendor.Id) AS SupplierName,
               supplierPart.Id AS SupplierPartId 
        FROM supplierPart 
        LEFT JOIN vendor On vendor.Id = supplierPart.VendorId 
        LEFT JOIN manufacturerPart_partNumber On manufacturerPart_partNumber.Id = supplierPart.ManufacturerPartNumberId 
        LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ManufacturerPartNumberId = manufacturerPart_partNumber.Id 
        LEFT JOIN productionPart ON productionPart.Id = productionPart_manufacturerPart_mapping.ProductionPartId 
    STR;

	$parameters = array();
	if(isset($manufacturerPartNumberId)) $parameters[] = " supplierPart.ManufacturerPartNumberId = $manufacturerPartNumberId";
	if(isset($supplierId)) $parameters[] = " supplierPart.VendorId = $supplierId";
	if(isset($productionPartNo)) $parameters[] = " productionPart.PartNo = '$productionPartNo'";

    $supplierData = $database->query($query,$parameters);
	
	$api->returnData($supplierData);
}
else if($api->isPost())
{
	$data = $api->getPostData()->data;

	$supplierPartCreate = array();
	$supplierPartCreate['ManufacturerPartNumberId'] = intval($data->ManufacturerPartNumberId);
	$supplierPartCreate['VendorId'] = intval($data->SupplierId);
	$supplierPartCreate['SupplierPartNumber'] = $data->SupplierPartNumber;
	$supplierPartCreate['SupplierPartLink'] = $data->SupplierPartLink;
	$supplierPartCreate['Note'] = $data->Note;

    $database->insert("supplierPart", $supplierPartCreate);
	
	$api->returnEmpty();
}

<?php
//*************************************************************************************************
// FileName : state.php
// FilePath : apiFunctions/purchasing/item
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isPatch())
{
	$parameters = $api->getGetData();
	if(!isset($parameters->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
	$purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
	if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

	$data = $api->getPostData();

	$poData = array();
	$poData['Status'] = $data->NewState;
	$database->update('purchaseOrder',$poData,'PoNo = '.$purchaseOrderNumber );

	$api->returnEmpty();
}


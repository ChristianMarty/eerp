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

if($api->isPatch(\Permission::PurchaseOrder_Edit))
{
	$parameters = $api->getGetData();
	if(!isset($parameters->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
	$purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNumber);
	if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

	$data = $api->getPostData();

	$poData = array();
	$poData['Status'] = $data->NewState;
	$database->update('purchaseOrder',$poData,'PurchaseOrderNumber = '.$purchaseOrderNumber );

	$api->returnEmpty();
}


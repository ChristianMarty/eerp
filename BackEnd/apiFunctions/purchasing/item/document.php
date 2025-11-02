<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : apiFunctions/purchasing/item
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../document/_document.php";

if($api->isGet(\Permission::PurchaseOrder_View))
{
	$parameters = $api->getGetData();
	if(!isset($parameters->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
	$purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $parameters->PurchaseOrderNumber);
	if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

	$query = "SELECT DocumentIds FROM purchaseOrder WHERE PurchaseOrderNumber = '$purchaseOrderNumber';";
	$result = $database->query($query)[0]->DocumentIds??null;

	$api->returnData(\Document\getDocumentsFromIds($result));
}

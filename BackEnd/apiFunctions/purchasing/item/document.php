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

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_getDocuments.php";

if($api->isGet())
{
	$parameters = $api->getGetData();
	if(!isset($parameters->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
	$purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
	if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

	$query = "SELECT DocumentIds FROM purchaseOrder WHERE PurchaseOrderNumber = '$purchaseOrderNumber';";
	$result = $database->query($query)[0]->DocumentIds??null;

	$api->returnData(getDocumentsFromIds($result));
}

<?php
//*************************************************************************************************
// FileName : edit.php
// FilePath : apiFunctions/purchasing/additionalCharge/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../_function.php";

if($api->isPost(\Permission::PurchaseOrder_Edit))
{
	$data = $api->getPostData();
    if(!isset($data->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
    $purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $data->PurchaseOrderNumber);
    if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

    $lines = $data->Lines;
    foreach ($lines as $line)
    {
        $sqlData = array();

        $id = intval($line->AdditionalChargesLineId);
        $sqlData['LineNumber'] = $line->LineNumber;
        $sqlData['Type'] = $line->Type;
        if($line->Price === null) $sqlData['Price'] = 0;
        else $sqlData['Price'] = $line->Price;
        $sqlData['Quantity'] = $line->Quantity;
        $sqlData['VatTaxId'] = intval($line->VatTaxId);
        $sqlData['Description'] = $line->Description;
        $sqlData['CreationUserId'] = $user->userId();

        if($id != 0)
        {
            $condition = "Id = ".$id;
            $database->update("purchaseOrder_additionalCharges", $sqlData, $condition);
        }
        else
        {
            $sqlData['PurchaseOrderId']['raw'] = "(SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '".$purchaseOrderNumber."' )";
            $database->insert("purchaseOrder_additionalCharges", $sqlData);
        }
    }

	$api->returnData(getPurchaseOrderData($purchaseOrderNumber));
}
else if($api->isDelete(\Permission::PurchaseOrder_Edit))
{
    $data = $api->getPostData();
    if(!isset($data->PurchaseOrderNumber))$api->returnParameterMissingError('PurchaseOrderNumber');
    $purchaseOrderNumber = \Numbering\parser(\Numbering\Category::PurchaseOrder, $data->PurchaseOrderNumber);
    if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

    if(!isset($data->AdditionalChargeLineId))$api->returnParameterMissingError('AdditionalChargeLineId');
    $lineId = intval($data->AdditionalChargeLineId);
    if($lineId === 0) $api->returnParameterError('AdditionalChargeLineId');

    $query = "DELETE FROM purchaseOrder_additionalCharges WHERE Id = ".$lineId." AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '".$purchaseOrderNumber."' );";
    $database->query($query);

    $api->returnData(getPurchaseOrderData($purchaseOrderNumber));
}

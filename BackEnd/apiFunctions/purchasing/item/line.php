<?php
//*************************************************************************************************
// FileName : line.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 19.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;


require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

function save_line($purchaseOrderNumber, $line): int
{
    $line = (array)$line;
    global $database;
    global $user;

    $sqlData = array();

    $lineId = intval($line['OrderLineId']);
    $sqlData['Description'] = $line['Description'];
    $sqlData['OrderReference'] = $line['OrderReference'];
    $sqlData['Quantity'] = $line['QuantityOrdered'];
    $sqlData['Sku'] = $line['SupplierSku'];
    $sqlData['VatTaxId'] = intval($line['VatTaxId']);
    $sqlData['UnitOfMeasurementId'] = intval($line['UnitOfMeasurementId']);
    $sqlData['Discount'] = $line['Discount'];
    $sqlData['StockPart']['raw'] = $database::dbToBit($line['StockPart']);
    $sqlData['ExpectedReceiptDate'] = $line['ExpectedReceiptDate'];
    $sqlData['CreationUserId'] = $user->userId();

    if($line['Price'] === null) $sqlData['Price'] = 0;
    else $sqlData['Price'] = $line['Price'];

    $sqlData['SpecificationPartRevisionId'] = null;
   if(array_key_exists('SpecificationPartRevisionCode', $line) && $line['SpecificationPartRevisionCode'] !== null) {
        $specificationPartNumber = barcodeParser_SpecificationPart($line['SpecificationPartRevisionCode']);
        $specificationPartRevision = barcodeParser_SpecificationPartRevision($line['SpecificationPartRevisionCode']);
        if($specificationPartNumber !== null and $specificationPartRevision !== null) {

            $specificationPartRevision = $database->escape($specificationPartRevision);
            $specificationPartQuery = <<<STR
                SELECT 
                    Id 
                FROM specificationPart_revision 
                WHERE  SpecificationPartId = 
                       (SELECT Id FROM specificationPart WHERE SpecificationPartNumber = $specificationPartNumber)
                        AND (specificationPart_revision.Revision = $specificationPartRevision)
            STR;
            $sqlData['SpecificationPartRevisionId']['raw'] = "($specificationPartQuery)";
        }
    }
    $sqlData['Note'] = $line['Note'];
    $type = $line['LineType'];

    $sqlData['Type'] = $type;
    $sqlData['PartNo'] = $line['PartNo'];
    $sqlData['ManufacturerName'] = $line['ManufacturerName'];
    $sqlData['ManufacturerPartNumber'] = $line['ManufacturerPartNumber'];


    if($sqlData['Type'] == 'Generic'){
        $sqlData['StockPart']['raw'] = $database::dbToBit(false);
    }

    if($lineId != 0)// Update row
    {
        $condition = "Id = ".$lineId;
        $sqlData['LineNumber'] = $line['LineNumber'];
        $database->update("purchaseOrder_itemOrder", $sqlData, $condition);

    }
    else // Insert new row
    {
        $sqlData['PurchaseOrderId']['raw'] = "(SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '".$purchaseOrderNumber."' )";
        $sqlData['LineNumber'] = 0;
        $database->insert("purchaseOrder_itemOrder", $sqlData);

        $query = <<<STR
            UPDATE purchaseOrder_itemOrder SET 
            LineNumber = (SELECT MAX(LineNumber)+1 FROM purchaseOrder_itemOrder WHERE PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '$purchaseOrderNumber' )) 
            WHERE LineNumber = 0 AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '$purchaseOrderNumber' )
        STR;
        $database->execute($query);

        $query = <<<STR
            SELECT Id FROM purchaseOrder_itemOrder WHERE Id = LAST_INSERT_ID()
        STR;

        $lineId = $database->query($query)[0]->Id;
    }

    return $lineId;
}

function update_costCenter(int $lineId, array | null $costCenterList): void
{
    global $database;
    global $user;
    // TODO: Find better way to update this
    $query = <<<STR
            DELETE FROM purchaseOrder_itemOrder_costCenter_mapping WHERE ItemOrderId = $lineId
    STR;
    $database->execute($query);

    if($costCenterList === null) return;

    foreach ($costCenterList as $cc)
    {
        $costCenterNumber = barcodeParser_CostCenter($cc->Barcode);
        $quota =
        $sqlData = array();
        $sqlData['CostCenterId']['raw'] = "(SELECT Id FROM finance_costCenter WHERE CostCenterNumber = $costCenterNumber)";
        $sqlData['ItemOrderId'] = $lineId;
        $sqlData['Quota'] = floatval($cc->Quota);
        $sqlData['CreationUserId'] = $user->userId();

        $database->insert("purchaseOrder_itemOrder_costCenter_mapping", $sqlData);
    }
}

if($api->isGet(\Permission::PurchaseOrder_View))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->LineId)) $api->returnParameterMissingError("LineId");
    $lineId = intval($parameters->LineId);
    if($lineId == 0) $api->returnParameterError("LineId");


    $query = purchaseOrderItem_getLineQuery(null, $lineId);
    $result = $database->query($query)[0];

    $output = purchaseOrderItem_getDataFromQueryResult("po",$result);

    $query = purchaseOrderItem_getCostCenterQuery($output['OrderLineId']);
    $result = $database->query($query)??null;

    $output['CostCenter'] = purchaseOrderItem_getCostCenterData($result);

    $api->returnData($output);
}
else if($api->isPost(\Permission::PurchaseOrder_Edit) OR $api->isPatch(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();
    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError('PurchaseOrderNumber');
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

    $lines = $api->getPostData();

    foreach ($lines->Lines as $line) {
        $lineId = save_line($purchaseOrderNumber, $line);
        update_costCenter($lineId, $line->CostCenter??null);
    }
 
    $api->returnEmpty();
}
else if($api->isDelete(\Permission::PurchaseOrder_Edit))
{
    $parameters = $api->getGetData();
    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError('PurchaseOrderNumber');
    if(!isset($parameters->LineId)) $api->returnParameterMissingError('LineId');

    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    $lineId = intval($parameters->LineId);
    if(!$purchaseOrderNumber)  $api->returnParameterError('PurchaseOrderNumber');
    if($lineId == 0)  $api->returnParameterError('LineId');

    $query = <<<STR
        DELETE FROM purchaseOrder_itemOrder 
               WHERE Id = $lineId AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PurchaseOrderNumber = '$purchaseOrderNumber' );
    STR;
    $database->query($query);
    $api->returnEmpty();
}

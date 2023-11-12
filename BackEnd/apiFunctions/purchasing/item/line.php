<?php
//*************************************************************************************************
// FileName : line.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 19.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $api;
global $database;

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../_function.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

function save_line($dbLink, $purchaseOrderNumber, $line): int
{
    $sqlData = array();

    $lineId = intval($line['OrderLineId']);
    $sqlData['Description'] = $line['Description'];
    $sqlData['OrderReference'] = $line['OrderReference'];
    $sqlData['Quantity'] = $line['QuantityOrdered'];
    $sqlData['Sku'] = $line['SupplierSku'];
    $sqlData['VatTaxId'] = intval($line['VatTaxId']);
    $sqlData['UnitOfMeasurementId'] = intval($line['UnitOfMeasurementId']);
    $sqlData['Discount'] = $line['Discount'];
    $sqlData['StockPart']['raw'] = dbToBit($line['StockPart']);
    $sqlData['ExpectedReceiptDate'] = $line['ExpectedReceiptDate'];

    if($line['Price'] === null) $sqlData['Price'] = 0;
    else $sqlData['Price'] = $line['Price'];

   if($line['SpecificationPartNumber'] !== null) {
        $specificationPartNumber = barcodeParser_SpecificationPart($line['SpecificationPartNumber']);
        $sqlData['SpecificationPartId']['raw'] = "(SELECT Id FROM specificationPart WHERE  Number = $specificationPartNumber)";
    } else {
        $sqlData['SpecificationPartId'] = null;
    }

    $sqlData['Note'] = $line['Note'];
    $type = $line['LineType'];

    $sqlData['Type'] = $type;
    $sqlData['PartNo'] = $line['PartNo'];
    $sqlData['ManufacturerName'] = $line['ManufacturerName'];
    $sqlData['ManufacturerPartNumber'] = $line['ManufacturerPartNumber'];

    if($sqlData['Type'] == 'Generic'){
        $sqlData['StockPart']['raw'] = dbToBit(false);
    }

    if($lineId != 0)// Update row
    {
        $condition = "Id = ".$lineId;
        $sqlData['LineNo'] = $line['LineNo'];
        $query = dbBuildUpdateQuery($dbLink,"purchaseOrder_itemOrder", $sqlData, $condition);
        dbRunQuery($dbLink,$query);
    }
    else // Insert new row
    {
        $sqlData['PurchaseOrderId']['raw'] = "(SELECT Id FROM purchaseOrder WHERE PoNo = '".$purchaseOrderNumber."' )";
        $sqlData['LineNo'] = 0;
        $query = dbBuildInsertQuery($dbLink,"purchaseOrder_itemOrder", $sqlData);
        dbRunQuery($dbLink,$query);

        $query = <<<STR
            UPDATE purchaseOrder_itemOrder SET 
            LineNo = (SELECT MAX(LineNo)+1 FROM purchaseOrder_itemOrder WHERE PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PoNo = '$purchaseOrderNumber' )) 
            WHERE LineNo = 0 AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PoNo = '$purchaseOrderNumber' )
        STR;
        dbRunQuery($dbLink,$query);

        $query = <<<STR
            SELECT Id FROM purchaseOrder_itemOrder WHERE Id = LAST_INSERT_ID()
        STR;

        $result = dbRunQuery($dbLink,$query);
        $lineId = mysqli_fetch_assoc($result)['Id'];
    }

    return $lineId;
}

function update_costCenter($dbLink, $lineId, $costCenterList): void
{
    // TODO: Find better way to update this
    $query = <<<STR
            DELETE FROM purchaseOrder_itemOrder_costCenter_mapping WHERE ItemOrderId = $lineId
    STR;
    dbRunQuery($dbLink,$query);

    foreach ($costCenterList as $cc)
    {
        $costCenterNumber = barcodeParser_CostCenter($cc['Barcode']);
        $quota = floatval($cc['Quota']);

        $query = <<<STR
            INSERT INTO purchaseOrder_itemOrder_costCenter_mapping (CostCenterId, ItemOrderId, Quota) 
            VALUES ((SELECT Id FROM finance_costCenter WHERE CostCenterNumber = $costCenterNumber),$lineId,$quota)
        STR;
        dbRunQuery($dbLink,$query);
    }
}

if($api->isGet())
{
    if(!isset($_GET["LineId"])) sendResponse(NULL, "Line Id Undefined");
    $lineId = intval($_GET["LineId"]);
    if($lineId == 0)  sendResponse(null,"Line Id Invalid");

    $dbLink = dbConnect();

    $query = purchaseOrderItem_getLineQuery(null, $lineId);
    $result =  dbRunQuery($dbLink,$query);
    $r = mysqli_fetch_assoc($result);

    $output = purchaseOrderItem_getDataFromQueryResult("po",$r);

    $query = purchaseOrderItem_getCostCenterQuery($output['OrderLineId']);
    $result =  dbRunQuery($dbLink,$query);

    $output['CostCenter'] = purchaseOrderItem_getCostCenterData($result);

    dbClose($dbLink);

    sendResponse($output);
}
else if($api->isPost() OR $api->isPatch())
{
    $parameters = $api->getGetData();
    if(!isset($parameters->PurchaseOrderNumber)) $api->returnParameterMissingError('PurchaseOrderNumber');
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($parameters->PurchaseOrderNumber);
    if(!$purchaseOrderNumber) $api->returnParameterError('PurchaseOrderNumber');

    $lines = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();
    foreach ($lines['Lines'] as $line)
    {
        $lineId = save_line($dbLink, $purchaseOrderNumber, $line);
        update_costCenter($dbLink, $lineId, $line['CostCenter']);
    }
    dbClose($dbLink);

    $api->returnEmpty();
}
else if($api->isDelete())
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
               WHERE Id = $lineId AND PurchaseOrderId = (SELECT Id FROM purchaseOrder WHERE PoNo = '$purchaseOrderNumber' );
    STR;
    $database->query($query);
    $api->returnEmpty();
}

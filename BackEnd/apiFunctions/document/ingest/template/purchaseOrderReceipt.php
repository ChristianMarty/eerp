<?php
//*************************************************************************************************
// FileName : purchaseOrderReceipt.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 03.12.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";

require_once __DIR__ . "/../../_functions.php";
require_once  __DIR__."/../../../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();

    $poNumber = $data ['PurchaseOrderNumber'];
    $note = $data ['Note'];

    $poNumber = barcodeParser_PurchaseOrderNumber($poNumber);

    $query = <<<STR
    SELECT Id, PoNo, PurchaseDate, DocumentIds FROM purchaseOrder WHERE  PoNo = $poNumber   
    STR;

    $result = dbRunQuery($dbLink,$query);
    if(!$result)
    {
        dbClose($dbLink);
        sendResponse(null,"DB Error");
    }
    $po = mysqli_fetch_assoc($result);

    if(!isset($po['PoNo']))
    {
        dbClose($dbLink);
        sendResponse(null,"PO-Number not found");
    }

    $name= "PO-".$po['PoNo']."_".$po['PurchaseDate'];

    dbClose($dbLink);

    $ingestData = array();
    $ingestData['FileName'] = $data['FileName'];
    $ingestData['Name'] = $name;
    $ingestData['Type'] = 'Receipt';
    $ingestData['Description'] = 'Receipt '.date('Y-m-d');
    $ingestData['Note'] = $note;

    $result = ingest($ingestData);

    if(!is_int($result)) sendResponse(null,$result['error']);

    $docIds = explode(",", $po['DocumentIds']);
    $docIds[] = $result;

    if (($key = array_search("", $docIds)) !== false) unset($docIds[$key]); // Remove empty string

    $docIdStr = implode(",",$docIds);

    $query = <<<STR
    UPDATE purchaseOrder SET  DocumentIds  =  '$docIdStr' WHERE  PoNo = $poNumber   
    STR;

    $dbLink = dbConnect();
    $result = dbRunQuery($dbLink,$query);
    dbClose($dbLink);

    sendResponse($docIdStr,null);

}

?>


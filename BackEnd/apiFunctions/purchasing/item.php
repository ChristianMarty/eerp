<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/_function.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$purchaseOrderNo = null;
	if(isset($_GET["PurchaseOrderNumber"])) $purchaseOrderNo = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);

	$output = getPurchaseOrderData($purchaseOrderNo);
	
	// Get Documents
	if(isset($output['MetaData']['DocumentIds'])) $DocIds = $output['MetaData']['DocumentIds'];
	else $DocIds = null;
	unset($output['MetaData']['DocumentIds']);
	
	$output["Documents"] = getDocuments($DocIds);

	sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $dbLink = dbConnect();

    $data = json_decode(file_get_contents('php://input'),true);

    $poCreate = array();
    $poCreate['VendorId'] = intval($data['SupplierId']);
    $poCreate['PurchaseDate'] = $data['PurchaseDate'];
    if($data['Title'] != "") $poCreate['Title'] = $data['Title'];
    if($data['Description'] != "") $poCreate['Description'] = $data['Description'];

    $poCreate['PoNo']['raw'] = "purchaseOrder_generatePoNo()";

    $query = dbBuildInsertQuery($dbLink, "purchaseOrder", $poCreate);

    $query .= "SELECT PoNo FROM purchaseOrder WHERE Id = LAST_INSERT_ID();";

    $output = array();
    $error = null;

    if(mysqli_multi_query($dbLink,$query))
    {
        do {
            if ($result = mysqli_store_result($dbLink)) {
                while ($row = mysqli_fetch_row($result)) {
                    $output["PurchaseOrderNo"] = $row[0];
                }
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($dbLink)) break;
        } while (mysqli_next_result($dbLink));
    }
    else
    {
        $error = "Error description: " . mysqli_error($dbLink);
    }


    dbClose($dbLink);
    sendResponse($output,$error);

}
else if ($_SERVER['REQUEST_METHOD'] == 'PATCH')
{
    if(!isset($_GET["PurchaseOrderNumber"])) sendResponse(NULL, "Purchase Order Number Undefined");
    $purchaseOrderNumber = barcodeParser_PurchaseOrderNumber($_GET["PurchaseOrderNumber"]);
    if(!$purchaseOrderNumber) sendResponse(NULL, "Purchase Order Number Parser Error");

    $data = json_decode(file_get_contents('php://input'),true);

    $poData = array();
    $poData['VendorId'] = intval($data['data']['SupplierId']);
    $poData['Title'] = $data['data']['Title'];
    $poData['PurchaseDate'] = $data['data']['PurchaseDate'];
    $poData['AcknowledgementNumber'] = $data['data']['AcknowledgementNumber'];
    $poData['QuotationNumber'] = $data['data']['QuotationNumber'];
    $poData['OrderNumber'] = $data['data']['OrderNumber'];
    $poData['Description'] = $data['data']['Description'];
    $poData['Carrier'] = $data['data']['Carrier'];
    $poData['PaymentTerms'] = $data['data']['PaymentTerms'];
    $poData['InternationalCommercialTerms'] = $data['data']['InternationalCommercialTerms'];
    $poData['HeadNote'] = $data['data']['HeadNote'];
    $poData['FootNote'] = $data['data']['FootNote'];
    $poData['CurrencyId'] = intval($data['data']['CurrencyId']);
    $poData['ExchangeRate'] = $data['data']['ExchangeRate'];
    $poData['VendorAddressId'] = intval($data['data']['VendorAddressId']);
    $poData['VendorContactId'] = intval($data['data']['VendorContactId']);
    $poData['Status'] = $data['data']['Status'];

    $dbLink = dbConnect();
    $query = dbBuildUpdateQuery($dbLink, "purchaseOrder", $poData, "PoNo = ".$purchaseOrderNumber);
    $result = dbRunQuery($dbLink,$query);

    dbClose($dbLink);
    sendResponse(null);
}

?>
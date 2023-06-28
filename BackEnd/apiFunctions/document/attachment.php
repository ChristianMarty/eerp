<?php
//*************************************************************************************************
// FileName : attachment.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

if ($_SERVER['REQUEST_METHOD'] == 'GET')
{

    $attachToTable = $_GET['Table'];
    $attachToBarcode = $_GET['AttachBarcode'];

    $dbLink = dbConnect();

    $docIdList = null;

    if($attachToTable === "PurchaseOrderDocument")
    {
        $poBarcode =  barcodeParser_PurchaseOrderNumber($attachToBarcode);
        if(!$poBarcode)sendResponse(null, "PurchaseOrderDocument Barcode error");

        $query = "SELECT DocumentIds FROM purchaseOrder WHERE PoNo = '".$poBarcode."'";
        $result = dbRunQuery($dbLink,$query);
        if(!$result) sendResponse(null, "Error in doc list");
        $docIdList = mysqli_fetch_assoc($result)['DocumentIds'];

    }
    $output = getDocuments($docIdList);
    dbClose($dbLink);
    sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $dbLink = dbConnect();

	$data = json_decode(file_get_contents('php://input'),true);
    $attachToTable = $data['Table'];
    $attachToBarcode = $data['AttachBarcode'];
	
	$docList = "";
	
	foreach($data['DocumentBarcodes'] as $key => $line)
	{
        $docNumber = barcodeParser_DocumentNumber($line);
		$docList .= $docNumber.",";
	}
    $docList = substr($docList, 0, -1);

    $docIdList = "";
    if(!empty($docList)) {
        $query = "SELECT GROUP_CONCAT(Id) AS DocumentIds FROM document WHERE DocumentNumber IN(" . $docList . ")";
        $result = dbRunQuery($dbLink, $query);
        if (!$result) sendResponse(null, "Error in doc list");

        $docIdList = mysqli_fetch_assoc($result)['DocumentIds'];
    }

    if($attachToTable === "PurchaseOrderDocument")
    {
        $poCode =  barcodeParser_PurchaseOrderNumber($attachToBarcode);
        if(!$poCode)sendResponse(null, "PurchaseOrderDocument Barcode error");

        $query = "UPDATE purchaseOrder SET  DocumentIds = '".$docIdList."' WHERE PoNo = '".$poCode."'";
        $result = dbRunQuery($dbLink,$query);
        if(!$result) sendResponse(null, "Document List Update Failed");

    }

	dbClose($dbLink);
	sendResponse(null);
}
?>
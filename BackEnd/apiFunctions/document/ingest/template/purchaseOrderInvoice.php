<?php
//*************************************************************************************************
// FileName : purchaseOrderInvoice.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 03.12.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;
require_once __DIR__ . "/_purchaseOrderDocument.php";

if($api->isPost())
{
    $data = $api->getPostData();
    purchaseOrderDocumentIngest($data, 'Invoice');
    $api->returnError("Ingest Error"); // This part of the code should not be reachable
}

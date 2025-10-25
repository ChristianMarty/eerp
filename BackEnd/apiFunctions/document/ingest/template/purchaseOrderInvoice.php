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

if($api->isPost(Permission::Document_Ingest_Save))
{
    $data = $api->getPostData();
    $result = purchaseOrderDocumentIngest($data, 'Invoice');
    $api->returnData($result);
}

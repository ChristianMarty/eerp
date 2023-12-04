<?php
//*************************************************************************************************
// FileName : purchaseOrderReceipt.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 03.12.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

if($api->isPost())
{
    $data = $api->getPostData();
    purchaseOrderDocumentIngest($data, 'Quote');
    $api->returnError("Ingest Error"); // This part of the code should not be reachable
}

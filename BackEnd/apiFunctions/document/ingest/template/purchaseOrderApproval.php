<?php
//*************************************************************************************************
// FileName : purchaseOrderApproval.php
// FilePath : apiFunctions/document/ingest/template/
// Author   : Christian Marty
// Date		: 11.0..2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

if($api->isPost())
{
    $data = $api->getPostData();
    purchaseOrderDocumentIngest($data, 'Approval');
    $api->returnError("Ingest Error"); // This part of the code should not be reachable
}

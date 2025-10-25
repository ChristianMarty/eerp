<?php
//*************************************************************************************************
// FileName : upload.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../_document.php";

if($api->isPost(Permission::Document_Ingest_Upload))
{
    $result = \Document\Ingest\upload($_FILES["file"]);
    $api->returnData($result);
}

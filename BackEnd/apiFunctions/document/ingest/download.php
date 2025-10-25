<?php
//*************************************************************************************************
// FileName : download.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../_document.php";

if($api->isPost(Permission::Document_Ingest_Download))
{
    $data = $api->getPostData();
    if(!isset($data->Url)){
        $api->returnData(\Error\parameterMissing("Url"));
    }
    $url = $data->Url;
    if(!is_string($url) || $url === ""){
        $api->returnData(\Error\generic("Url is empty"));
    }

    $result = \Document\Ingest\download($url);
    $api->returnData($result);
}
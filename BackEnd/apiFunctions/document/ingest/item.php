<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/ingest/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../_document.php";

if($api->isPost(Permission::Document_Ingest_Save))
{
	$data = \Document\Ingest\validateRequest($api->getPostData());
    $result = \Document\Ingest\save($data);
    $api->returnData($result);
}
else if($api->isDelete(Permission::Document_Ingest_Delete))
{
    $data = $api->getPostData();
    if(!isset($data->FileName) OR $data->FileName == "" OR $data->FileName == null){
        $api->returnData(\Error\parameterMissing("FileName"));
    }

    $result = \Document\Ingest\delete($data->FileName);
    $api->returnData($result);
}

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
global $database;
global $api;

require_once __DIR__ . "/../_document.php";

if($api->isPost())
{
	$data = $api->getPostData();

    $result = ingest($data);

    if(is_int($result)) $api->returnEmpty();
    else $api->returnError($result['error']);
}

else if($api->isDelete())
{
    global $serverDataPath;
    global $ingestPath;

    $data = $api->getPostData();
	if(!isset($data->FileName) OR $data->FileName == "" OR $data->FileName == null) $api->returnError("File name is not set.");
	
	$src = $serverDataPath.$ingestPath."/".$data->FileName;
	
	if (unlink($src))  $api->returnEmpty();
	else  $api->returnError("File delete failed.");
}
?>
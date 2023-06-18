<?php
//*************************************************************************************************
// FileName : _getDocuments.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 17.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

function getDocumentsFromIds($dbLink, $documentIds): array
{
    if(empty($documentIds)) return [];

    global $dataRootPath;
    global $documentPath;

    $documents = array();

    if(isset($documentIds)) $DocIds = explode(",",$documentIds);
    else $DocIds = null;

    if(!empty($DocIds))
    {
        $baseQuery = "SELECT * FROM `document` WHERE Id IN(".implode(", ",$DocIds).")";

        $result = dbRunQuery($dbLink,$baseQuery);
        while($r = mysqli_fetch_assoc($result))
        {
            $r["FileName"] = $r['Path'];
            $r['Path'] = $dataRootPath.$documentPath."/".$r['Type']."/".$r['Path'];
            $r['Barcode'] = "Doc-".$r['DocumentNumber'];
            if($r['Barcode'] === null) $r['Barcode'] = "";
            $r['Note'] = $r['Note'];
            $documents[] = $r;
        }
    }

    return $documents;
}

function getDocuments($documentIds): array
{
	$dbLink = dbConnect();
    $documents = getDocumentsFromIds($dbLink, $documentIds);
	dbClose($dbLink);

	return $documents;
}




?>
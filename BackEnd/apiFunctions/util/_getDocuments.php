<?php
//*************************************************************************************************
// FileName : _getDocuments.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 17.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";
include_once __DIR__ . "/../util/_barcodeFormatter.php";

function getDocumentsFromIds(string|null $documentIds): array
{
    if(empty($documentIds)) return [];

    global $database;

    global $dataRootPath;
    global $documentPath;

    $DocIds = explode(",",$documentIds);
    if(empty($DocIds))  return [];

    $baseQuery = "SELECT * FROM `document` WHERE Id IN(".implode(", ",$DocIds).")";

    $documents = $database->query($baseQuery);
    foreach ($documents as $r)
    {
        $r->FileName = $r->Path;
        $r->Path = $dataRootPath.$documentPath."/".$r->Type."/".$r->Path;

        if($r->DocumentNumber === null) $r->Barcode = "";
        else $r->Barcode = barcodeFormatter_DocumentNumber($r->DocumentNumber);
    }
    return $documents;
}

function getDocuments(string|null $documentIds): array
{
    if(empty($documentIds)) return [];

    return getDocumentsFromIds( $documentIds);
}

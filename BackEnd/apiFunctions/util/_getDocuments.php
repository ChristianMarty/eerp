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

function _formatDocumentOutput(array $result): array
{
    global $dataRootPath;
    global $documentPath;
    foreach($result as $item) {
        $item->File = $item->Path;
        if($item->Name === null){
            $item->Name = $item->Path;
        }
        $item->DocumentNumber = intval($item->DocumentNumber);
        $item->Path = $dataRootPath.$documentPath."/".$item->Type."/".$item->Path;
        $item->ItemCode = barcodeFormatter_DocumentNumber($item->DocumentNumber);
        $item->Description = $item->Description??'';
    }
    return $result;
}

function getDocumentsFromIds(string|null $documentIds): array
{
    if($documentIds === null) return [];
    $documentIds = trim($documentIds);
    if(strlen($documentIds) === 0) return [];

    global $database;

    $docIds = explode(",", $documentIds);
    if (empty($docIds)) return [];
    $idList = implode(", ", $docIds);

    $query = <<< QUERY
        SELECT
            Id,
            DocumentNumber,
            Path,
            Type,
            LinkType,
            Name,
            Hash,
            CreationDate
        FROM document
        WHERE Id IN($idList)
        ORDER BY Id DESC;
    QUERY;

    $result = $database->query($query);
    return _formatDocumentOutput($result);
}

function getDocuments(): array
{
    global $database;
    $query = <<< QUERY
        SELECT 
            DocumentNumber,
            Path,
            Type,
            LinkType,
            Name,
            Hash,
            CreationDate
        FROM document
        ORDER BY CreationDate DESC
    QUERY;
    $result = $database->query($query);
    return _formatDocumentOutput($result);
}

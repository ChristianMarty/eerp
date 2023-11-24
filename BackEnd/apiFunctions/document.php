<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 21.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/util/_barcodeFormatter.php";

if($api->isGet("document.view"))
{
    $query = <<< QUERY
        SELECT 
            DocumentNumber,
            Path,
            Type,
            LinkType,
            Description,
            Hash,
            CreationDate
        FROM document
        ORDER BY Id DESC
    QUERY;
    $result = $database->query($query);

    global $dataRootPath;
    global $documentPath;
    foreach($result as &$item) {
        $item->FileName = $item->Path;
        $item->Path = $dataRootPath.$documentPath."/".$item->Type."/".$item->Path;
        $item->Barcode = barcodeFormatter_DocumentNumber($item->DocumentNumber);
        $item->DocumentBarcode = $item->Barcode;
        $item->Description = $item->Description??'';
    }
    $api->returnData($result);
}

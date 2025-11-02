<?php
//*************************************************************************************************
// FileName : migrateDocuments.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 19.10.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

$title = "Migrate Documents";
$description = "";


global $dataRootPath;
global $documentPath;
global $serverDataPath;

$query = "SELECT  `Id`, `Path`, `Name`, `Type`, `LinkType`, `CreationUserId`, `Hash`, `CreationDate`, `DocumentNumber` FROM `document`";

$result = $database->query($query);

foreach($result as &$item) {
    $item->OldPath = $serverDataPath . $documentPath . "/" . $item->Type . "/" . urlencode($item->Path);
    $item->NewName = $serverDataPath . $documentPath . "/" . "DOC-" . $item->DocumentNumber . "-1." . pathinfo($item->OldPath, PATHINFO_EXTENSION);

    if ($item->Name == null){
        $upd = [];
        $upd['Name'] = $item->Path;
        $database->update("document", $upd, "`Id` = $item->Id");
    }

    $rev = [];
    $rev['DocumentNumberId'] = $item->Id;
    $rev['RevisionNumber'] = 1;
    $rev['LinkType'] = $item->LinkType;
    $rev['Hash'] = $item->Hash;
    if($item->LinkType === 'External'){
        $rev['Path'] = $item->Path;
    }
    $rev['CreationUserId'] = $item->CreationUserId;
    $rev['CreationDate'] = $item->CreationDate;

    $database->insert("document_revision",$rev);

    copy($item->OldPath, $item->NewName);
}

$api->returnData($result);

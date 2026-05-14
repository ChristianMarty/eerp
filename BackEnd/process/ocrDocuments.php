<?php
//*************************************************************************************************
// FileName : ocrDocuments.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 11.04.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../apiFunctions/document/_document.php";
require_once __DIR__ . "/../apiFunctions/externalApi/lmStudio.php";

$title = "OCR Documents";
$description = "";
$parameter = null;

$query = <<<STR
    SELECT 
           document.DocumentNumber,
           document_revision.RevisionNumber,
           document_revision.Extension,
           document_revision.Id AS DocumentRevisionId
    FROM document_revision
    LEFT JOIN document on document_revision.DocumentNumberId = document.Id
    WHERE document_revision.Cache_Content IS NULL AND 
          (document.Category = 'Datasheet' OR 
           document.Category = 'Invoice' OR 
           document.Category = 'Receipt' OR 
           document.Category = 'Calibration' OR 
           document.Category = 'Unknown' OR 
           document.Category = 'Certificate' OR 
           document.Category = 'Software' OR 
           document.Category = 'Confirmation' OR 
           document.Category = 'DeliveryNote' OR 
           document.Category = 'Quote' OR 
           document.Category = 'Business Card')
    ORDER BY document_revision.Id DESC
    LIMIT 1;
STR;
$result = $database->query($query);
\Error\checkErrorAndExit($result);

$file = \Document\_formatDocumentName($result[0]);
$revisionId = $result[0]->DocumentRevisionId;

global $serverDataPath;
global $documentPath;
$filePath = $serverDataPath.$documentPath."/".$file;

$fileData = file_get_contents($filePath);

$imagick = new Imagick();
$imagick->setResolution(150,150);
$imagick->readImage($filePath);

$numberOfPages = $imagick->getNumberImages();

$pages = [];
for($i = 0; $i<$numberOfPages; $i++)
{
    $imagick->setIteratorIndex($i);
    $imagick->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
    $imagick->setImageFormat("PNG");
    $image = $imagick->getImageBlob();
    $pageData = \LmStudio\ocr($image);
    \Error\checkErrorAndExit($pageData);

    //$imagick->writeImage($filePath.'-'.$i.'.png');

    $pageData->pageNumber = $i+1;
    if(strlen($pageData->data)===0) continue;
    $pages[] = $pageData;
}

$updateData = [];
$updateData['Cache_Content'] = json_encode($pages);

$database->update('document_revision', $updateData, "`id` = $revisionId");

$api->returnData($pages);



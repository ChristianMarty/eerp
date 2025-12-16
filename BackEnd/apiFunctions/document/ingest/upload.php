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
    if(!isset($_FILES["file"])){
        $api->returnData(\Error\generic("File upload failed."));
    }

    $errorCode = $_FILES['file']['error'];
    if($errorCode !== 0){
        $errorMessage = match($errorCode) {
            0 => 'There is no error, the file uploaded with success',
            1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
            2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
            3 => 'The uploaded file was only partially uploaded',
            4 => 'No file was uploaded',
            6 => 'Missing a temporary folder',
            7 => 'Failed to write file to disk.',
            8 => 'A PHP extension stopped the file upload.',
        };
        $api->returnData(\Error\generic($errorMessage));
    }

    $result = \Document\Ingest\upload($_FILES["file"]);
    $api->returnData($result);
}

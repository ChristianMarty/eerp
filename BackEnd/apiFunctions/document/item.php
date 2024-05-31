<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;
global $user;

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/_functions.php";

if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->DocumentNumber)) $api->returnParameterMissingError("DocumentNumber");
    $documentNumber = barcodeParser_DocumentNumber($parameters->DocumentNumber);
    if($documentNumber == null) $api->returnParameterError("DocumentNumber");

    $query = <<< QUERY
        SELECT
            document.Id,
            DocumentNumber,
            Path,
            Name,
            Description,
            Type,
            LinkType,
            Hash,
            user.UserId AS CreatedBy,
            CreationDate
        FROM document
        LEFT JOIN user on document.CreationUserId = user.Id
        WHERE DocumentNumber = '$documentNumber';
    QUERY;
	$output = $database->query($query);

	if(count($output)== 0) $api->returnEmpty();
	$output = $output[0];

    global $dataRootPath;
	global $documentPath;
    $output->Path = $dataRootPath.$documentPath."/".$output->Type."/".$output->Path;
	$output->ItemCode  = barcodeFormatter_DocumentNumber($output->DocumentNumber);
	$output->Citations = getCitations($output->Id);
    $output->DocumentNumber = intval($output->DocumentNumber);
    $output->Description = $output->Description??"";
    unset($output->Id);
	
	$api->returnData($output);
}
else if($api->isPost())
{
    $data = $api->getPostData();

    global $serverDataPath;
    global $documentPath;
    $fileName = basename($_FILES["file"]["name"]);
    $fileDir  = $serverDataPath.$documentPath."/";
    $file = $_FILES["file"]["tmp_name"];

    // Check if file already exists
    $fileMd5 = md5_file($file);

    $query = <<< QUERY
        SELECT
            *
        FROM document
        WHERE Hash = '$fileMd5';
    QUERY;
    $result = $database->query($query);

    if(count($result)!=0) $api->returnError("The uploaded file already exists.");

	$output = array();
	$error = null;

    $sqlData = array();
    $sqlData['Path'] = $fileName;
    $sqlData['Type']  = $_POST["Type"];
    $sqlData['Description']  = $_POST["Description"];
    $sqlData['Hash']  = $fileMd5;
    $sqlData['CreationUserId'] = $user->userId();;

    $id = $database->insert("document", $sqlData);

    move_uploaded_file($file, $fileDir.$_POST["Type"]."/".$fileName);

    $query = "SELECT * FROM `document` WHERE `Id`='$id';";
    $result = $database->query($query);
    if(count($result))
    {
        $result = $result[0];
    }

    $output["message"]= "File uploaded successfully.";
	$output["fileInfo"]= $result;

	$api->returnData($output);
}

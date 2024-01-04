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

require_once __DIR__ . "/../util/_barcodeParser.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/_functions.php";

if($api->isGet())
{
	$parameter = $api->getGetData();

	$query = "";

	if(isset($parameter->DocId))
	{
		$docId = intval($parameter->DocId);
		$query = "SELECT * FROM `document` WHERE `Id` = '$docId';";
	}
	else if(isset($parameter->DocumentNumber))
	{
		$documentNumber = barcodeParser_DocumentNumber($parameter->DocumentNumber);
		if($documentNumber == null) $api->returnParameterError("DocumentNumber");
		$query = "SELECT * FROM `document` WHERE `DocumentNumber` = '$documentNumber';";
	}
	else
	{
		$api->returnParameterMissingError("DocId or DocumentNumber");
	}

	$output = $database->query($query);

	if(count($output)== 0) $api->returnEmpty();
	$output = $output[0];

    global $dataRootPath;
	global $documentPath;
    $output->Path = $dataRootPath.$documentPath."/".$output->Type."/".$output->Path;
	$output->DocumentBarcode  = barcodeFormatter_DocumentNumber($output->DocumentNumber);
	$output->Barcode = $output->DocumentBarcode; // TODO: Legacy->remove
	$output->Citations = getCitations($output->Id);
	
	$api->returnData($output);
}
else if($api->isPost())
{
	$output = array();
	$error = null;
	
	global $serverDataPath;
	global $documentPath;
	
	$fileName = basename($_FILES["file"]["name"]);
	$fileDir  = $serverDataPath.$documentPath."/";
	$file = $_FILES["file"]["tmp_name"];
	
	// Check if file already exists
	$fileMd5 = md5_file ($file);
	
	$query = "SELECT * FROM `document` WHERE `Hash`='".$fileMd5."'";
	$result = $database->query($query);
	
	if(count($result)==0)
	{
		$sqlData = array();
		$sqlData['Path'] = $fileName;
		$sqlData['Type']  = $_POST["Type"];
		$sqlData['Description']  = $_POST["Description"];
		$sqlData['Hash']  = $fileMd5;

		$id = $database->insert("document", $sqlData);

        move_uploaded_file($file, $fileDir.$_POST["Type"]."/".$fileName);

		$query = "SELECT * FROM `document` WHERE `Id`='$id';";
		$result = $database->query($query);
		if(count($result))
		{
			$result = $result[0];
		}
		
		$output["message"]= "File uploaded successfully.";
	}
	else
	{
		$output["message"]= "The uploaded file already exists.";
		$error= "The uploaded file already exists.";
	}	

	$output["fileInfo"]= $result;

	$api->returnData($output);
}

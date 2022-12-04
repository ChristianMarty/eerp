<?php
//*************************************************************************************************
// FileName : _functions.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";



function checkFileNotDuplicate($path): ?array
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	// Check if file already exists
	$fileMd5 = md5_file($path);
	
	$query = "SELECT * FROM document WHERE Hash='".$fileMd5."'";

	$result = dbRunQuery($dbLink,$query);
	$existingFile = null;
	if($result) 
	{
		$existingFile = mysqli_fetch_assoc($result);
	}
	dbClose($dbLink);
	
	$retuning = array();
	
	if($existingFile != null)
	{
		$retuning['preexisting'] = true;
		$retuning['hash'] = $existingFile['Hash'];
		$retuning['path'] = $existingFile['Path'];
		$retuning['type'] = $existingFile['Type'];
		$retuning['description'] = $existingFile['Description'];
	}
	else 
	{
		$retuning['preexisting'] = false;
		$retuning['hash'] = $fileMd5;
	}
	
	return $retuning;
}


/* Parameter
    $ingestData = array();
    $ingestData['FileName'] = '';
    $ingestData['Name'] = '';
    $ingestData['Type'] = null;
    $ingestData['Description'] = null;
    $ingestData['Note'] = null;
*/
function ingest($data): int|array
{

    $dbLink = dbConnect();

    $fileNameIllegalCharactersRegex = '/[ :"*?<>|\\/]+/';

    if(!isset($data['Name']) OR $data['Name'] == "" OR $data['Name'] == null) return array('error' => "Name is not set.");
    if(!isset($data['Type']) OR $data['Type'] == "" OR $data['Type'] == null) return array('error' => "Type is not set.");
    if(!isset($data['FileName']) OR $data['FileName'] == "" OR $data['FileName'] == null) return array('error' => "File name is not set.");

    if(preg_match($fileNameIllegalCharactersRegex,$data['FileName']) != 0) return array('error' => "File name contains illegal character.");
    if(preg_match($fileNameIllegalCharactersRegex,$data['Name']) != 0) return array('error' => "File name contains illegal character.");

    global $serverDataPath;
    global $ingestPath;
    global $documentPath;
    $src = $serverDataPath.$ingestPath."/".$data['FileName'];
    $dstFileName = $data['Name'].".".pathinfo($src, PATHINFO_EXTENSION);

    $dst = $serverDataPath.$documentPath."/".$data['Type']."/".$dstFileName;

    if(!file_exists($src)) return array('error' => "File path invalid.");
    if(file_exists($dst)) return array('error' => "File name already exists.");

    $fileHashCheck = checkFileNotDuplicate($src);

    if($fileHashCheck['preexisting'])
    {
        return array('error' => "File already exists as ".$fileHashCheck['path']." with type ".$fileHashCheck['type']);
    }

    if(!rename($src, $dst)) return array('error' => "File copy faild.");

    $sqlData = array();
    $sqlData['Path'] = $dstFileName;
    $sqlData['Type'] = $data['Type'];
    $sqlData['Description']['raw'] = dbStringNull(dbEscapeString($dbLink,$data['Description']));
    $sqlData['LinkType'] = "Internal";
    $sqlData['Note'] = $data['Note'];
    $sqlData['Hash'] = $fileHashCheck['hash'];
    $sqlData['DocumentNumber']['raw'] = "(SELECT generateItemNumber())";

    $query = dbBuildInsertQuery($dbLink,"document", $sqlData);

    $query .= " SELECT `Id` FROM `document` WHERE `Id` = LAST_INSERT_ID();";

    $output = null;
    $error = null;

    if(mysqli_multi_query($dbLink,$query))
    {
        do {
            if ($result = mysqli_store_result($dbLink)) {
                while ($row = mysqli_fetch_row($result)) {
                    $output = intval($row[0]);
                }
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($dbLink)) break;
        } while (mysqli_next_result($dbLink));
    }
    else
    {
        $error = "Error description: " . mysqli_error($dbLink);
    }


    dbClose($dbLink);

    if($output != null) return $output;
    return array('error' => $error);
}

?>
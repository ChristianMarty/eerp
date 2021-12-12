<?php
//*************************************************************************************************
// FileName : save.php
// FilePath : apiFunctions/project/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$data = json_decode(file_get_contents('php://input'),true);
	
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$bom = $data['Bom'];
	$projectId = dbEscapeString($dbLink, $data["ProjectId"]);
	
	foreach ( $bom as $line)
	{
		$sqlData = array();
		
		$sqlData['ProjectId'] = $projectId;
		$sqlData['ProductionPartNo'] = dbEscapeString($dbLink,$line['PartNo']);
		
		$refDes = explode(",",$line['RefDes']);
		
		foreach ( $refDes as $item)
		{
			$sqlData['ReferenceDesignator'] = dbEscapeString($dbLink,$item);
			
			$query = dbBuildInsertQuery($dbLink,"project_bom", $sqlData);
			dbRunQuery($dbLink,$query);
			
		}
	}
	
	dbClose($dbLink);	
	
	
	$projectData = array();
	
	sendResponse($projectData);
}

?>
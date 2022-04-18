<?php
//*************************************************************************************************
// FileName : getDocuments.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 17.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

function getDocuments($documentIds)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	global $documentRootPath;
		
	$documents = array();
	
	if(isset($documentIds)) $DocIds = explode(",",$documentIds);
	else $DocIds = null;
	
	if(!empty($DocIds))
	{
		$baseQuery = "SELECT * FROM `document` WHERE Id IN(".implode(", ",$DocIds).")";
		
		$result = dbRunQuery($dbLink,$baseQuery);
		while($r = mysqli_fetch_assoc($result))
		{
			$r['Path'] = $documentRootPath."/".$r['Type']."/".$r['Path'];
			$r['Barcode'] = "Doc-".$r['DocNo'];
			if($r['Barcode'] === null) $r['Barcode'] = "";
			array_push($documents, $r);
		}
	}
	
	dbClose($dbLink);

	
	return $documents;
}


?>
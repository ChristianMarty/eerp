<?php
//*************************************************************************************************
// FileName : specificationPart.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 29.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/databaseConnector.php";
require_once __DIR__ . "/../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            specificationPart.Id,
            specificationPart.Type,
            specificationPart.Title
        FROM specificationPart
    STR;
    $result = dbRunQuery($dbLink,$query);

	$output = array();
	while($r = mysqli_fetch_assoc($result)) 
	{
        $r['Id'] = intval($r['Id']);
        $output[] = $r;
	}
	dbClose($dbLink);	
	sendResponse($output);
}

?>
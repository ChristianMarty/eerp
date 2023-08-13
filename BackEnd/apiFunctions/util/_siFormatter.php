<?php
//*************************************************************************************************
// FileName : siFormatter.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 07.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

include_once __DIR__ . "/../databaseConnector.php";

function siFormatter($value, $scale = 1): string
{
    if(!is_numeric($value)) return $value;
    if($value == 0) return 0;

	$dbLink = dbConnect();
	
	$query = "SELECT * FROM unitOfMeasurement_prefix WHERE Strict = 1 ORDER BY Multiplier DESC";
	$result = dbRunQuery($dbLink,$query);
	
	$output = "";
    $value = $value*$scale;

	while($r = mysqli_fetch_assoc($result)) 
	{
        if(floatval($value) >= floatval($r['Multiplier']))
        {
            $output = ($value/floatval($r['Multiplier'])).$r['Symbol'];
            break;
        }
	}

	dbClose($dbLink);
	return $output;
}
?>
<?php
//*************************************************************************************************
// FileName : siFormatter.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 07.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function siFormatter($value, $scale = 1): string
{
    global $database;

    if(!is_numeric($value)) return $value;
    if($value == 0) return 0;

	$query = "SELECT * FROM unitOfMeasurement_prefix WHERE Strict = 1 ORDER BY Multiplier DESC";
	$result = $database->query($query);
	
	$output = "";
    $value = $value*$scale;

	foreach ($result as $r)
	{
        if(floatval($value) >= floatval($r->Multiplier))
        {
            $output = ($value/floatval($r->Multiplier)).$r->Symbol;
            break;
        }
	}

	return $output;
}

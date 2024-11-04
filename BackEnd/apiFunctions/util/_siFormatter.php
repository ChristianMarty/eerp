<?php
//*************************************************************************************************
// FileName : siFormatter.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 07.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

function siFormatter(string|int|float|null $value, float $scale = 1): string
{
    global $database;

    if(!is_numeric($value)) return $value;
    if($value == 0) return "0";

	$query = <<<QUERY
        SELECT 
            * 
        FROM unitOfMeasurement_prefix 
        WHERE Strict = 1 
        ORDER BY Multiplier DESC;
    QUERY;

	$result = $database->query($query);
	
	$output = "";
    $value = $value*$scale;
	foreach ($result as $r) {
        if(floatval($value) >= floatval($r->Multiplier)) {
            $output = ($value/floatval($r->Multiplier)).$r->Symbol;
            break;
        }
	}
	return $output;
}

<?php
//*************************************************************************************************
// FileName : _json.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 03.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function json_validateString($input): bool|string|null
{
	$jsonData = trim($input);
	if($jsonData == "") return null;
	
	json_decode($jsonData);
	if(json_last_error() !== JSON_ERROR_NONE) return false;
		
	return $jsonData;
}
?>
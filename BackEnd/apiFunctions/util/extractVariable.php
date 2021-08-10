<?php
//*************************************************************************************************
// FileName : extractVariable.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

// Extracts the content of a variable in a PHP file and returns it.

function extractVariable($path, $variableName)
{
	$file_contents  = file_get_contents($path, $maxlen = 1000);
	$startPos = strpos($file_contents, $variableName);
	if($startPos === false) return $variableName." not found";
	
	$file_contents = str_split($file_contents,$startPos)[1];
	$endPos = strpos($file_contents, ";");
	if($endPos === false) return $variableName." not found";
	
	$file_contents = str_split($file_contents,$endPos)[0];
	
	$array = explode("=", $file_contents);
	array_shift($array);
	$file_contents = implode("=", $array);
	
	$file_contents = str_replace('"', '',$file_contents);
	$file_contents = ltrim($file_contents, ' ');
	
	return $file_contents;
}

?>
<?php
//*************************************************************************************************
// FileName : databaseConnector.php
// FilePath : apiFunctions/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../config.php";

function dbConnect()
{
	global $databaseServerAddress;
	global $databasePort;
	global $databaseName;
	global $databaseUser;
	global $databasePassword; 

	$dbLink = new mysqli($databaseServerAddress, $databaseUser, $databasePassword, $databaseName, $databasePort);

	if ($dbLink->connect_errno) 
	{
		echo "Failed to connect to SQL Server: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
		return null;
	}

	mysqli_set_charset($dbLink, "utf8mb4");
	
	return $dbLink;
}

function dbStringNull($string)
{
	$isNull = false;
	if($string == null) $isNull = true;
	

	if(is_string($string))
	{
		if(strlen($string) == 0) $isNull = true;
	}
	else 
	{
		$isNull = true;
	}
	
	if($isNull) return "NULL";
	else return "'".$string."' ";
}

function dbClose($dbLink)
{
	mysqli_close($dbLink);
}

function dbEscapeString($dbLink, $string)
{
	return mysqli_real_escape_string($dbLink, $string);
}

function dbGetResult($result)
{
	return mysqli_fetch_assoc($result);
}

function dbRunQuery($dbLink, $query)
{
	return mysqli_query($dbLink,$query);
}

function dbGetErrorString($dbLink)
{
	return  mysqli_error($dbLink);
}

function dbBuildQuery($dbLink, $baseQuery, $queryParam)
{
	$query = $baseQuery;
	
	if(!empty($queryParam))
	{
		$query .= " WHERE ";
		
		$query .= $queryParam[0];
		unset($queryParam[0]);
		
		foreach ($queryParam as &$param) 
		{
			$query .= " AND ".$param;
		}
	}
	
	return $query;
}

function dbBuildInsertQuery($dbLink, $tableName, $data)
{
	$keys ="";
	$values ="";
		
	foreach ($data as $key => $value) 
	{
		$keys .= "`".$key."`, ";
		if(is_array($value))
		{
			$values .= $value['raw'].", ";
		}
		else
		{
			if($value == null) $values .= "NULL, ";
			else $values .= "'".mysqli_real_escape_string($dbLink,$value)."', ";
		}
	}
	
	$keys = rtrim($keys, ", ");
	$values = rtrim($values, ", ");
	
	return "INSERT INTO ".$tableName." (".$keys.") VALUES (".$values.");";	
}

function dbBuildUpdateQuery($dbLink, $tableName, $data, $condition = NULL)
{

	$pairs ="";
		
	foreach ($data as $key => $value) 
	{	
		$val = "";
		
		if(is_array($value))
		{
			$val .= $value['raw'];
		}
		else
		{
			if($value == null) $val = "NULL";
			else $val = "'".mysqli_real_escape_string($dbLink,$value)."'";
		}

		$pairs .= $key." = ".$val.", ";
	}
	
	$pairs = rtrim($pairs, ", ");
	
	if($condition == NULL) $condition = "";
	else $condition = " WHERE ".$condition;
	
	return "UPDATE ".$tableName." SET ".$pairs.$condition.";";	
}


?>
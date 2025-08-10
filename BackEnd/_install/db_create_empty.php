<?php
//*************************************************************************************************
// FileName : db_create_empty.php
// FilePath : _install/
// Author   : Christian Marty
// Date		: 01.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../core/database.php";

$database = new database();

$database->pdo()->query("DROP DATABASE eerpTest;");
$database->pdo()->query("CREATE DATABASE eerpTest;");
$database = new database();

$path = __DIR__."/../../SQL/install/";

$files = scandir($path);
natsort($files );
$files = array_diff($files, array('.', '..'));

foreach($files as $key => $file)
{	
	$query = file_get_contents($path.$file);

	try {
		$database->pdo()->query($query);
	}
	catch (\PDOException $e)
	{
		echo "FILE: ".$file."<br>";
		echo $e->getMessage()."<br>";
		exit();
	}
}

echo "The empty EERP Database has been created.";

?>

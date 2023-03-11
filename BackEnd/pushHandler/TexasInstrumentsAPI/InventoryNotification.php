<?php
//*************************************************************************************************
// FileName : InventoryNotification.php
// FilePath : pushHandler/TexasInstrumentsAPI/
// Author   : Christian Marty
// Date		: 21.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";

$token = "bI46xFjOLjAZ6I1ocS5n3bxvyCVPO6RC";

$data = file_get_contents('php://input');


global $serverDataPath;
global $ingestPath;
	
$path = $serverDataPath.$ingestPath."/tiApi".strval(time()).".txt";
$myfile = fopen($path, "w") or die("Unable to open file!");

fwrite($myfile, $data);
fclose($myfile);

header("Content-Type:application/json; charset=UTF-8");
echo json_encode(array("status" => "accepted"));


?>
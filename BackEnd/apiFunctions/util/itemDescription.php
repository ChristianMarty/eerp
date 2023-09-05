<?php
//*************************************************************************************************
// FileName : itemDescription.php
// FilePath : apiFunctions/utils/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/_description.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["Item"])) sendResponse(null,"No item specified");

    $data = description_generateSummary($_GET["Item"]);

    sendResponse($data['data'], $data['error']);
}
?>

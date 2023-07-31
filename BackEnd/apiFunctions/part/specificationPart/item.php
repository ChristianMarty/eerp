<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/specificationPart
// Author   : Christian Marty
// Date		: 01.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../../util/_barcodeFormatter.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	if(!isset($_GET["SpecificationPartBarcode"])) sendResponse(NULL, "Specification Part Barcode Undefined");
    $specificationPartBarcode= barcodeParser_SpecificationPart($_GET["SpecificationPartBarcode"]);

    $dbLink = dbConnect();

    $query = <<<STR
        SELECT 
            specificationPart.Id,
            specificationPart.Type,
            specificationPart.Title
        FROM specificationPart
        WHERE specificationPart.Id = $specificationPartBarcode
    STR;
    $result = dbRunQuery($dbLink,$query);

    $output = mysqli_fetch_assoc($result);
    $output['Id'] = intval($output['Id']);

    dbClose($dbLink);
    sendResponse($output);
}
else if($_SERVER['REQUEST_METHOD'] == 'POST')
{
    $data = json_decode(file_get_contents('php://input'),true);

    $dbLink = dbConnect();
    $sqlData = array();
    $sqlData['Type'] = $data['Type'];
    $sqlData['Title'] = $data['Title'];
    $query = dbBuildInsertQuery($dbLink,"specificationPart", $sqlData);

    $query .= <<< STR
        SELECT 
            specificationPart.Id
        FROM specificationPart
        WHERE specificationPart.Id = LAST_INSERT_ID();
    STR;

    $error = null;
    $output = array();

    if(mysqli_multi_query($dbLink,$query))
    {
        do {
            if ($result = mysqli_store_result($dbLink)) {
                while ($row = mysqli_fetch_row($result)) {
                    $output['Id'] = intval($row[0]);
                }
                mysqli_free_result($result);
            }
            if(!mysqli_more_results($dbLink)) break;
        } while (mysqli_next_result($dbLink));
    }
    else
    {
        $error = "Error description: " . mysqli_error($dbLink);
    }

    dbClose($dbLink);
    sendResponse($output,$error);
}

?>
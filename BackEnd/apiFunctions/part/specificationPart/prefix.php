<?php
//*************************************************************************************************
// FileName : prefix.php
// FilePath : apiFunctions/part/specificationPart/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
global $api;

$api->returnDeprecatedError();

require_once __DIR__ . "/../../databaseConnector.php";
if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

    $query = <<< STR
        SELECT
            Id,
            Prefix,
            Category,
            Name
        FROM numbering
        WHERE Category = 'SpecificationPart'
    STR;

    $result = dbRunQuery($dbLink,$query);
    $output = array();
    while($r = mysqli_fetch_assoc($result))
    {
        $r['Id'] = intval( $r['Id'] );
        $output[] = $r;
    }
    dbClose($dbLink);

    sendResponse($output);
}
?>
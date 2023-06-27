<?php
//*************************************************************************************************
// FileName : filterOption.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date     : 19.05.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if (!isset($_GET["ClassId"]))  sendResponse(null, "ClassId is not specified");

    $output = array();

    $dbLink = dbConnect();

// Get applicable classes

    $query = <<<STR
        WITH recursive manufacturerPart_classWithChildren AS 
        {}
        select Id, ParentId, Name, AttributeList from manufacturerPart_class where Id = 2
        union all
        select child.Id,child.ParentId, child.Name, child.AttributeList from manufacturerPart_class as child
        join manufacturerPart_classWithChildren as parent on parent.Id = child.ParentId
        )
        SELECT * FROM manufacturerPart_classWithChildren;	
    STR;



    $attributes  = array();
    $query = <<<STR
    SELECT *
    FROM vendor
    ORDER BY Name
    STR;

    $manufacturerOptions = array();
    $manufacturerOptions['Name'] = 'Manufacturer';

    $result = dbRunQuery($dbLink,$query);
    while($r = mysqli_fetch_assoc($result)) 
    {
        $manufacturerOptions['Options'][] =  $r;
    }

    $output[] = $manufacturerOptions;

    dbClose($dbLink);    
    sendResponse($output);
}
e
?>
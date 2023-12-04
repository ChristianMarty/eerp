<?php
//*************************************************************************************************
// FileName : filterOption.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 03.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameters = $api->getGetData();

    if (!isset($parameters->ClassId)) $api->returnParameterMissingError('ClassId');

    $output = array();

// Get applicable classes

    $query = <<<STR
        WITH recursive manufacturerPart_classWithChildren AS 
        (
        select Id, ParentId, Name, AttributeList from manufacturerPart_class where Id = 2
        union all
        select child.Id,child.ParentId, child.Name, child.AttributeList from manufacturerPart_class as child
        join manufacturerPart_classWithChildren as parent on parent.Id = child.ParentId
        )
        SELECT * FROM manufacturerPart_classWithChildren;	
    STR;


    $attributes  = array();
    $query = <<<STR
        SELECT *,
        vendor_displayName(Id) AS Name
        FROM vendor
        ORDER BY Name
    STR;

    $manufacturerOptions = array();
    $manufacturerOptions['Name'] = 'Manufacturer';

    $result = $database->query($query);
    foreach ($result as $r)
    {
        $manufacturerOptions['Options'][] =  $r;
    }

    $output[] = $manufacturerOptions;

    $api->returnData($output);
}

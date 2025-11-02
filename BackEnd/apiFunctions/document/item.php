<?php
//*************************************************************************************************
// FileName : item.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/_document.php";

if($api->isGet(Permission::Document_View))
{
    $parameters = $api->getGetData();

    if(!isset($parameters->DocumentNumber)) $api->returnData(\Error\parameterMissing("DocumentNumber"));
    $documentNumber = \Numbering\parser(\Numbering\Category::Document, $parameters->DocumentNumber);
    if($documentNumber == null) $api->returnData(\Error\parameter("DocumentNumber"));

    $meta = \Document\getDocumentMetaData($documentNumber);
    if($meta === null){
        $api->returnData(\Error\itemNotFound($parameters->DocumentNumber));
    }
    $output = $meta->jsonSerialize();
    $output->Revision = \Document\getRevisions($meta);
    $output->Citation = \Document\getCitations($meta);

	$api->returnData($output);
}

<?php
//*************************************************************************************************
// FileName : skuSearch.php
// FilePath : apiFunctions/purchasing/item/
// Author   : Christian Marty
// Date		: 05.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../externalApi/mouser/mouser.php";
require_once __DIR__ . "/../../externalApi/digikey/digikey.php";
require_once __DIR__ . "/../../externalApi/distrelec/distrelec.php";


global $mouserSupplierId;
global $digikeySupplierId;
global $distrelecSupplierId;
	
if($api->isGet())
{
    $parameters = $api->getGetData();

    if(!isset($parameters->SupplierId)) $api->returnParameterMissingError("SupplierId");
    if(!isset($parameters->SKU)) $api->returnParameterMissingError("SKU");

	$supplierId = $parameters->SupplierId;
	$sku = $parameters->SKU;

    $data = null;
	if($supplierId == $mouserSupplierId)
	{
        $data = null;
	}
	else if($supplierId == $digikeySupplierId)
	{
		$data = null;
	}
    else if($supplierId == $distrelecSupplierId)
    {
        $data = distrelec_skuSearch($sku);
    }
	else
	{
		$api->returnError("Supplier not supported!");
	}
		
	$api->returnData($data);
}

<?php
//*************************************************************************************************
// FileName : notification.php
// FilePath : apiFunctions/productionPart
// Author   : Christian Marty
// Date		: 01.12.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $query = <<<STR
        SELECT 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartNumber, 
            productionPart_getQuantity(numbering.Id, productionPart.Number) AS StockQuantity, 
            productionPart.StockMinimum, 
            productionPart.StockMaximum, 
            productionPart.StockWarning, 
            productionPart.Description
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE StockMinimum IS NOT Null OR StockMaximum IS NOT NULL OR StockWarning IS NOT Null
    STR;

	$result = $database->query($query);
	
	$rows = array();
    foreach ($result as $r)
	{
		$quantity = intval($r->StockQuantity);
		$minimum = intval($r->StockMinimum);
		$maximum = intval($r->StockMaximum);
		$warning = intval($r->StockWarning);
		
		$status = null;
		if( $quantity < $minimum and $minimum != Null) $status = "Minimum";
		else if( $quantity < $warning and $warning != Null) $status = "Warning";
		else if( $quantity > $maximum  and $maximum != Null ) $status = "Maximum";

		$r->Status = $status;
		
		if($status != null) $rows[] = $r;
	}

	$api->returnData($rows);
}

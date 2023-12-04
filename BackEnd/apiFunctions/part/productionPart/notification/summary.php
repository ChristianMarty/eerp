<?php
//*************************************************************************************************
// FileName : summary.php
// FilePath : apiFunctions/productionPart/notification
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
            productionPart_getQuantity(numbering.Id, productionPart.Number) as StockQuantity, 
            productionPart.StockMinimum, 
            productionPart.StockMaximum, 
            productionPart.StockWarning 
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE StockMinimum IS NOT Null OR StockMaximum IS NOT NULL OR StockWarning IS NOT Null
    STR;

	$result = $database->query($query);

	$warningNotifications = 0;
	$minimumNotifications = 0;
	$maximumNotifications = 0;
	
	foreach ($result as $r)
	{
		$quantity = intval($r->StockQuantity);
		$minimum = intval($r->StockMinimum);
		$maximum = intval($r->StockMaximum);
		$warning = intval($r->StockWarning);
		
		if( $quantity < $minimum and $minimum != Null) $minimumNotifications ++;
		else if( $quantity < $warning and $warning != Null) $warningNotifications ++;
		else if( $quantity > $maximum  and $maximum != Null ) $maximumNotifications ++;	
	}

	$output = array();
	
	$output['Warning'] = $warningNotifications;
	$output['Minimum'] = $minimumNotifications;
	$output['Maximum'] = $maximumNotifications;
	
	$api->returnData($output);
}

?>
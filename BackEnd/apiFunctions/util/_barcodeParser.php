<?php
//*************************************************************************************************
// FileName : _barcodeParser.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 03.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************


function barcodeParser_StockNumber(string|int $input): bool|string
{
    if(is_int($input)) $input = strval($input);

    $stockNumber = trim($input);
    $stockNumber = strtolower($stockNumber);

    if( substr_count($stockNumber, '-') == 0) // if only number is given
    {
        if(strlen($stockNumber) != 4) return false;
        return $stockNumber;
    }

    $stockNumberParts = explode('-',$stockNumber);

    if($stockNumberParts[0] != "stk") return false;
    if(strlen($stockNumberParts[1]) != 4) return false;

    return $stockNumberParts[1];
}

function barcodeParser_InventoryNumber($input): bool|int
{
	$inventoryCode = trim($input);
	$inventoryCode = strtolower($inventoryCode);
	
	if( substr_count($inventoryCode, '-') == 0) // if only number is given
	{
		if(!is_numeric($inventoryCode)) return false;
		return intval($inventoryCode);
	}
	
	$inventoryNumberParts = explode('-',$inventoryCode);

	if($inventoryNumberParts[0] != "inv") return false;
	if(!is_numeric($inventoryNumberParts[1])) return false;
	
	return intval($inventoryNumberParts[1]);
}

function barcodeParser_PurchaseOrderNumber($input): bool|int
{
    $poCode = trim($input);
    $poCode = strtolower($poCode);

    if( substr_count($poCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($poCode)) return false;
        return intval($poCode);
    }

    $poCodeParts = explode('-',$poCode);

    if($poCodeParts[0] != "po") return false;
    if(!is_numeric($poCodeParts[1])) return false;

    return intval($poCodeParts[1]);
}

function barcodeParser_ProductionPart($input): bool|string
{
    return $input;

    // TODO: Fix this

    $poCode = trim($input);
    $poCode = strtolower($poCode);

    if( substr_count($poCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($poCode)) return false;
        return intval($poCode);
    }

    $poCodeParts = explode('-',$poCode);

    //if($poCodeParts[0] != "po") return false; //TODO: check prefix
    if(!is_numeric($poCodeParts[1])) return false;

    return intval($poCodeParts[1]);
}
?>
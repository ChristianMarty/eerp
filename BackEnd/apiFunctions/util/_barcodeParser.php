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
    // TODO: Fix this
    return trim($input);
}

function barcodeParser_SpecificationPart($input): bool|string
{
    $specificationPartCode = trim($input);
    $specificationPartCode = strtolower($specificationPartCode);

    if( substr_count($specificationPartCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($specificationPartCode)) return false;
        return intval($specificationPartCode);
    }

    $specificationPartNumberParts = explode('-',$specificationPartCode);

    if($specificationPartNumberParts[0] != "spec") return false;
    if(!is_numeric($specificationPartNumberParts[1])) return false;

    return intval($specificationPartNumberParts[1]);
}

function barcodeParser_BillOfMaterial($input): bool|string
{
    if(is_int($input)) $input = strval($input);

    $bomCode = trim($input);
    $bomCode = strtolower($bomCode);

    if( substr_count($bomCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($bomCode)) return false;
        return intval($bomCode);
    }

    $bomCodeParts = explode('-',$bomCode);

    if($bomCodeParts[0] != "bom") return false;
    if(!is_numeric($bomCodeParts[1])) return false;

    return intval($bomCodeParts[1]);
}

function barcodeParser_WorkOrderNumber(string|int $input): bool|int
{
    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return false;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "wo") return false;
    if(!is_numeric($woCodeParts[1])) return false;

    return intval($woCodeParts[1]);
}

function barcodeParser_AssemblyNumber(string|int $input): bool|string
{
    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return false;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "asm") return false;
    if(!is_numeric($ashCodeParts[1])) return false;

    return intval($ashCodeParts[1]);
}

function barcodeParser_AssemblyUnitNumber(string|int $input): bool|string
{
    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return false;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "asu") return false;
    if(!is_numeric($ashCodeParts[1])) return false;

    return intval($ashCodeParts[1]);
}

function barcodeParser_AssemblyUnitHistoryNumber(string|int $input): bool|string
{
    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return false;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "ash") return false;
    if(!is_numeric($ashCodeParts[1])) return false;

    return intval($ashCodeParts[1]);
}

function barcodeParser_DocumentNumber(string|int $input): bool|int
{
    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return false;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "doc") return false;
    if(!is_numeric($woCodeParts[1])) return false;

    return intval($woCodeParts[1]);
}

function barcodeParser_LocationNumber(string|int $input): bool|int
{
    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return false;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "loc") return false;
    if(!is_numeric($woCodeParts[1])) return false;

    return intval($woCodeParts[1]);
}

function barcodeParser_CostCenter(string|int $input): bool|string
{
    $inventoryCode = trim($input);
    $inventoryCode = strtolower($inventoryCode);

    if( substr_count($inventoryCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($inventoryCode)) return false;
        return intval($inventoryCode);
    }

    $inventoryNumberParts = explode('-',$inventoryCode);

    if($inventoryNumberParts[0] != "cc") return false;
    if(!is_numeric($inventoryNumberParts[1])) return false;

    return intval($inventoryNumberParts[1]);
}

function barcodeParser_Project(string|int $input): bool|string
{
    $inventoryCode = trim($input);
    $inventoryCode = strtolower($inventoryCode);

    if( substr_count($inventoryCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($inventoryCode)) return false;
        return intval($inventoryCode);
    }

    $inventoryNumberParts = explode('-',$inventoryCode);

    if($inventoryNumberParts[0] != "pcjt") return false;
    if(!is_numeric($inventoryNumberParts[1])) return false;

    return intval($inventoryNumberParts[1]);
}

function barcodeParser_TestSystemNumber(string|int $input): bool|int
{
    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return false;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "tsy") return false;
    if(!is_numeric($woCodeParts[1])) return false;

    return intval($woCodeParts[1]);
}
?>
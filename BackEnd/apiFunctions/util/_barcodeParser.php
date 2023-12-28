<?php
//*************************************************************************************************
// FileName : _barcodeParser.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 03.09.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************


function barcodeParser_StockNumber(null|string|int $input): null|string
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $stockNumber = trim($input);
    $stockNumber = strtolower($stockNumber);

    if( substr_count($stockNumber, '-') == 0) // if only number is given
    {
        if(strlen($stockNumber) != 4) return null;
        return $stockNumber;
    }

    $stockNumberParts = explode('-',$stockNumber);

    if($stockNumberParts[0] != "stk") return null;
    if(strlen($stockNumberParts[1]) != 4) return null;

    return $stockNumberParts[1];
}

function barcodeParser_InventoryNumber(null|string|int $input): null|int
{
    if($input === null) return null;

	$inventoryCode = trim($input);
	$inventoryCode = strtolower($inventoryCode);
	
	if( substr_count($inventoryCode, '-') == 0) // if only number is given
	{
		if(!is_numeric($inventoryCode)) return null;
		return intval($inventoryCode);
	}
	
	$inventoryNumberParts = explode('-',$inventoryCode);

	if($inventoryNumberParts[0] != "inv") return null;
	if(!is_numeric($inventoryNumberParts[1])) return null;
	
	return intval($inventoryNumberParts[1]);
}

function barcodeParser_InventoryAccessoryNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    $inventoryCode = strtolower(trim($input));

    if( substr_count($inventoryCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($inventoryCode)) return null;
        return intval($inventoryCode);
    }

    $inventoryNumberParts = explode('-',$inventoryCode);

    if($inventoryNumberParts[0] != "inv") return null;
    if(!is_numeric($inventoryNumberParts[1])) return null;
    if(!is_numeric($inventoryNumberParts[2])) return null;

    return intval($inventoryNumberParts[2]);
}

function barcodeParser_PurchaseOrderNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    $poCode = trim($input);
    $poCode = strtolower($poCode);

    if( substr_count($poCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($poCode)) return null;
        return intval($poCode);
    }

    $poCodeParts = explode('-',$poCode);

    if($poCodeParts[0] != "po") return null;
    if(!is_numeric($poCodeParts[1])) return null;

    return intval($poCodeParts[1]);
}

function barcodeParser_ProductionPart(null|string|int $input): null|string
{
    // TODO: Fix this
    return trim($input);
}

function barcodeParser_SpecificationPart(null|string|int $input): null|string
{
    if($input === null) return null;

    $specificationPartCode = trim($input);
    $specificationPartCode = strtolower($specificationPartCode);

    if( substr_count($specificationPartCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($specificationPartCode)) return null;
        return intval($specificationPartCode);
    }

    $specificationPartNumberParts = explode('-',$specificationPartCode);

    if($specificationPartNumberParts[0] != "spec") return null;
    if(!is_numeric($specificationPartNumberParts[1])) return null;

    return intval($specificationPartNumberParts[1]);
}

function barcodeParser_BillOfMaterial(null|string|int $input): null|int
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $bomCode = trim($input);
    $bomCode = strtolower($bomCode);

    if( substr_count($bomCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($bomCode)) return null;
        return intval($bomCode);
    }

    $bomCodeParts = explode('-',$bomCode);

    if($bomCodeParts[0] != "bom") return null;
    if(!is_numeric($bomCodeParts[1])) return null;

    return intval($bomCodeParts[1]);
}

function barcodeParser_WorkOrderNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return null;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "wo") return null;
    if(!is_numeric($woCodeParts[1])) return null;

    return intval($woCodeParts[1]);
}

function barcodeParser_AssemblyNumber(null|string|int $input): null|string
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return null;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "asm") return null;
    if(!is_numeric($ashCodeParts[1])) return null;

    return intval($ashCodeParts[1]);
}

function barcodeParser_AssemblyUnitNumber(null|string|int $input): null|string
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return null;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "asu") return null;
    if(!is_numeric($ashCodeParts[1])) return null;

    return intval($ashCodeParts[1]);
}

function barcodeParser_AssemblyUnitHistoryNumber(null|string|int $input): null|string
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $ashCode = trim($input);
    $ashCode = strtolower($ashCode);

    if( substr_count($ashCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($ashCode)) return null;
        return intval($ashCode);
    }

    $ashCodeParts = explode('-',$ashCode);

    if($ashCodeParts[0] != "ash") return null;
    if(!is_numeric($ashCodeParts[1])) return null;

    return intval($ashCodeParts[1]);
}

function barcodeParser_DocumentNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return null;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "doc") return null;
    if(!is_numeric($woCodeParts[1])) return null;

    return intval($woCodeParts[1]);
}

function barcodeParser_LocationNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return null;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "loc") return null;
    if(!is_numeric($woCodeParts[1])) return null;

    return intval($woCodeParts[1]);
}

function barcodeParser_CostCenter(null|string|int $input): null|string
{
    if($input === null) return null;

    $inventoryCode = trim($input);
    $inventoryCode = strtolower($inventoryCode);

    if( substr_count($inventoryCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($inventoryCode)) return null;
        return intval($inventoryCode);
    }

    $inventoryNumberParts = explode('-',$inventoryCode);

    if($inventoryNumberParts[0] != "cc") return null;
    if(!is_numeric($inventoryNumberParts[1])) return null;

    return intval($inventoryNumberParts[1]);
}

function barcodeParser_Project(null|string|int $input): null|string
{
    if($input === null) return null;

    $inventoryCode = trim($input);
    $inventoryCode = strtolower($inventoryCode);

    if( substr_count($inventoryCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($inventoryCode)) return null;
        return intval($inventoryCode);
    }

    $inventoryNumberParts = explode('-',$inventoryCode);

    if($inventoryNumberParts[0] != "pcjt") return null;
    if(!is_numeric($inventoryNumberParts[1])) return null;

    return intval($inventoryNumberParts[1]);
}

function barcodeParser_TestSystemNumber(null|string|int $input): null|int
{
    if($input === null) return null;

    if(is_int($input)) $input = strval($input);

    $woCode = trim($input);
    $woCode = strtolower($woCode);

    if( substr_count($woCode, '-') == 0) // if only number is given
    {
        if(!is_numeric($woCode)) return null;
        return intval($woCode);
    }

    $woCodeParts = explode('-',$woCode);

    if($woCodeParts[0] != "tsy") return null;
    if(!is_numeric($woCodeParts[1])) return null;

    return intval($woCodeParts[1]);
}

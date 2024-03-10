<?php
//*************************************************************************************************
// FileName : _barcodeFormatter.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 12.06.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function barcodeFormatter_StockNumber(string|int $input): string
{
    return "STK-".strtoupper($input);
}

function barcodeFormatter_InventoryNumber(string|int $input, string|int|null $accessory = null): string
{
    $output = "Inv-".$input;
    if($accessory != null) {
        $output .= "-".$accessory;
    }
    return $output;
}

function barcodeFormatter_PurchaseOrderNumber(string|int $input, string|int|null $line = null): string
{
    $output =  "PO-".$input;
    if($line != null) {
        $output .= "#".$line;
    }
    return $output;
}

function barcodeFormatter_LocationNumber(string|int $input): string
{
    return "Loc-".$input;
}

function barcodeFormatter_ProductionPart(string|int $number, string|null $prefix = null ): string
{
    if($prefix === null) return $number; // TODO: Fix this
    else return $prefix."-".$number;
}

function barcodeFormatter_SpecificationPart(string|int $input): string
{
    return "Spec-".$input;
}

function barcodeFormatter_WorkOrderNumber(string|int|null $input): string|null
{
    if($input == null) return null;

    return "WO-".$input;
}

function barcodeFormatter_BillOfMaterial(string|int $input): string|null
{
    return "BOM-".$input;
}

function barcodeFormatter_AssemblyNumber(string|int $input): string
{
    return "ASM-".$input;
}

function barcodeFormatter_AssemblyUnitNumber(string|int $input): string
{
    return "ASU-".$input;
}

function barcodeFormatter_AssemblyUnitHistoryNumber(string|int $input): string
{
    return "ASH-".$input;
}

function barcodeFormatter_DocumentNumber(string|int $input): string
{
    return "Doc-".$input;
}

function barcodeFormatter_CostCenter(string|int $input): string
{
    return "CC-".$input;
}

function barcodeFormatter_Project(string|int|null $input): string|null
{
    return "Pjct-".$input;
}

function barcodeFormatter_ShipmentNumber(string|int $input): string
{
    return "Shp-".$input;
}

function barcodeFormatter_TestSystemNumber(string|int $input): string
{
    return "TSY-".$input;
}
?>
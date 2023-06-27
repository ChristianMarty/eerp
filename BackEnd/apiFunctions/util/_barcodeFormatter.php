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
    return "STK-".$input;
}

function barcodeFormatter_InventoryNumber(string|int $input): string
{
    return "Inv-".$input;
}

function barcodeFormatter_PurchaseOrderNumber(string|int $input ,string|int|null $line = null): string
{
    $output =  "PO-".$input;
    if($line != null)
    {
        $output .= "#".$line;
    }
    return $output;
}

function barcodeFormatter_ProductionPart(string|int $input): string
{
    return $input; // TODO: Fix this
}

function barcodeFormatter_WorkOrderNumber(string|int|null $input): string|null
{
    if($input == null) return null;

    return "WO-".$input;
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

function barcodeFormatter_Project(string|int $input): string
{
    return "Pjct-".$input;
}
?>
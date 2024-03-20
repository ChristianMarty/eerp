<?php
//*************************************************************************************************
// FileName : userSettings.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************

class userSettings
{

    static function devModeSettings():stdClass
    {
        return json_decode(self::$devModeSettings);
    }

    static private string $devModeSettings = <<< SETTINGS
    {	
        "Default": {
        "StockLabelPrinter": 1,
        "StockLabel": 1,
        "BomPrinter": 2,
        "AssemblyUnitHistory": {"PrinterId": 2, "RendererId": 3},
        "AssemblyUnit": {"PrinterId": 2, "RendererId": 12},
        "PartReceiptPrinter":2, 
        "PurchaseOrder": {"UoM": 29, "VAT": 1}}
    }
    SETTINGS;
}
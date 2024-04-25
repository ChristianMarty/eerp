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
        "StockItem": {"PrinterId": 3, "RendererId": 4},
        "StockHistory": {"PrinterId": 2, "RendererId": 8},
        "StockReceipt": {"PrinterId": 2, "RendererId": 11},
        "AssemblyUnitHistory": {"PrinterId": 2, "RendererId": 3},
        "AssemblyUnit": {"PrinterId": 2, "RendererId": 12},
        "LocationInventoryList": {"PrinterId": 2, "RendererId": 17},
        "BomPrinter": 2,
        "PurchaseOrder": {"UoM": 29, "VAT": 1}}
    }
    SETTINGS;
}
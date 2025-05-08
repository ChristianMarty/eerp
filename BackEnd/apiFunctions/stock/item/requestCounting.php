<?php
//*************************************************************************************************
// FileName : requestCounting.php
// FilePath : apiFunctions/stock/item/
// Author   : Christian Marty
// Date		: 27.01.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../../util/_barcodeParser.php";
require_once __DIR__ . "/../_stock.php";

if($api->isPost("stock.countingRequest"))
{
    $data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnParameterMissingError("StockCode");
    $stockNumber = barcodeParser_StockNumber($data->StockCode);
    if($stockNumber === false) $api->returnParameterError("StockNumber");

    \stock\stock::createCountingRequest(null, $stockNumber);

    $api->returnEmpty();
}


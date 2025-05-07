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
global $database;
global $api;
global $user;

require_once __DIR__ . "/../../util/_barcodeParser.php";

if($api->isGet())
{
    $api->returnEmpty();
}
else if($api->isPost("stock.countingRequest"))
{
    $data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnParameterMissingError("StockCode");
    $stockNumber = barcodeParser_StockNumber($data->StockCode);
    if($stockNumber === false) $api->returnParameterError("StockNumber");

    $stockNumber = $database->escape($stockNumber);

    $sqlData['CountingRequestUserId'] = $user->userId();
    $sqlData['CountingRequestDate']['raw'] = "current_timestamp()";

    $database->update("partStock", $sqlData, "StockNumber = $stockNumber");

    $api->returnEmpty();
}


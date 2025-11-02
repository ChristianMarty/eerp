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

require_once __DIR__ . "/../_stock.php";

if($api->isPost(\Permission::Stock_RequestCounting))
{
    $data = $api->getPostData();
    if(!isset($data->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockNumber = \Numbering\parser(\Numbering\Category::Stock, $data->StockCode);
    if($stockNumber === null) $api->returnData(\Error\parameter("StockCode"));

    $result = \Stock\Stock::createCountingRequest(null, $stockNumber);
    $api->returnData($result);
}

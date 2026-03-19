<?php
//*************************************************************************************************
// FileName : split.php
// FilePath : apiFunctions/stock/item/
// Author   : Christian Marty
// Date		: 09.11.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../_stock.php";

if ($api->isPost(\Permission::Stock_Split)) {

    $data = $api->getPostData();

    if(!isset($data->StockCode)) $api->returnData(\Error\parameterMissing("StockCode"));
    $stockCode= \Numbering\parser(\Numbering\Category::Stock, $data->StockCode);
    if($stockCode === null) $api->returnData(\Error\parameter("StockCode"));

    if(!isset($data->Quantity)) $api->returnData(\Error\parameterMissing("Quantity"));
    $quantity = floatval($data->Quantity);
    if($quantity === 0.0) $api->returnData(\Error\parameter("Quantity"));

    $newStockCode =  \Stock\Stock::split($stockCode, $quantity);
    \Error\checkErrorAndExit($newStockCode);

    $output = [];
    $output['StockNumber'] = $newStockCode;
    $output['ItemCode'] = \Numbering\format(\Numbering\Category::Stock, $newStockCode);
    $api->returnData($output);
}

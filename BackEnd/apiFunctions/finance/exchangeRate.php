<?php
//*************************************************************************************************
// FileName : exchangeRate.php
// FilePath : apiFunctions/finance
// Author   : Christian Marty
// Date		: 29.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../externalApi/europeanCentralBank.php";
require_once __DIR__ . "/../../core/finance.php";

if($api->isGet()) {
    $parameter = $api->getGetData();
    if (!isset($parameter->CurrencyCode) AND !isset($parameter->CurrencyId)) $api->returnParameterMissingError("CurrencyId and CurrencyCode");

    global $accountingCurrencyId;

    $query = "SELECT CurrencyCode FROM finance_currency WHERE Id = " . intval($accountingCurrencyId) . " LIMIT 1;";
    $sourceCurrencyCode = $database->query($query)[0]->CurrencyCode;

    $targetCurrencyCode = "";
    if (isset($parameter->CurrencyCode)) {
        $targetCurrencyCode = $parameter->CurrencyCode;

    } else if (isset($parameter->CurrencyId)) {
        $query = "SELECT CurrencyCode FROM finance_currency WHERE Id = " . intval($parameter->CurrencyId) . " LIMIT 1;";
        $targetCurrencyCode = $database->query($query)[0]->CurrencyCode;

    } else {
        $api->returnData(\Error\generic("CurrencyCode Error"));
    }

    if ($targetCurrencyCode == $sourceCurrencyCode) $exchangeRate = 1.0;
    else $exchangeRate = ecb_getExchangeRate(\Finance\Currency::fromCode($sourceCurrencyCode), \Finance\Currency::fromCode($targetCurrencyCode));

    if($exchangeRate instanceof \Error\Data){
        $api->returnData($exchangeRate);
    }

    $data = array();
    $data['ExchangeRate']  = $exchangeRate;
    $data['From'] = $sourceCurrencyCode;
    $data['To'] = $targetCurrencyCode;

    $api->returnData($data);
}

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
require_once __DIR__ ."/../externalApi/europeanCentralBank.php";

if($api->isGet()) {
    $parameter = $api->getGetData();
    if (!isset($parameter->CurrencyCode) AND !isset($parameter->CurrencyId)) $api->returnParameterMissingError("CurrencyId and CurrencyCode");

    global $accountingCurrencyId;

    $query = "SELECT CurrencyCode FROM finance_currency WHERE Id = " . intval($accountingCurrencyId) . " LIMIT 1;";
    $sourceCurrencyCode = $database->query($query)[0]->CurrencyCode;

    $targetCurrencyCode = null;
    if (isset($parameter->CurrencyCode)) {
        $targetCurrencyCode = $parameter->CurrencyCode;
    } else if (isset($parameter->CurrencyId)) {
        $query = "SELECT CurrencyCode FROM finance_currency WHERE Id = " . intval($parameter->CurrencyId) . " LIMIT 1;";
        $targetCurrencyCode = $database->query($query)[0]->CurrencyCode;
    } else {
        $api->returnError("CurrencyCode Error");
    }

    $data = array();
    if ($targetCurrencyCode == $sourceCurrencyCode) $data['ExchangeRate'] = 1.0;
    else $data['ExchangeRate'] = ecb_getExchangeRate($sourceCurrencyCode, $targetCurrencyCode);

    if ($data['ExchangeRate'] === null) $api->returnError("Unable to retrieve exchange rate");

    $data['From'] = $sourceCurrencyCode;
    $data['To'] = $targetCurrencyCode;
    $api->returnData($data);
}

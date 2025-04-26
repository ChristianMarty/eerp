<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 29.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $parameter = $api->getGetData();
    if(!isset($parameter->Year)) $api->returnParameterMissingError("Year");
    $year = $database->escape($parameter->Year);

    $query = <<<STR
        SELECT  
           MONTH(PurchaseDate) AS Month, 
           SUM(purchaseOrder_itemOrder.Quantity * purchaseOrder_itemOrder.Price / purchaseOrder.ExchangeRate ) AS Merchandise,
            SUM(purchaseOrder_itemOrder.Quantity * purchaseOrder_itemOrder.Price * (finance_tax.Value/100) / purchaseOrder.ExchangeRate ) + COALESCE(shipping.VAT,0)  AS VAT,
            COALESCE(shipping.Shipping, 0) AS Shipping
        FROM purchaseOrder
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
        LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_itemOrder.VatTaxId
        LEFT JOIN  (
           SELECT  
               MONTH(PurchaseDate) AS Month, 
               SUM(purchaseOrder_additionalCharges.Quantity * purchaseOrder_additionalCharges.Price) AS Shipping, 
               SUM(purchaseOrder_additionalCharges.Quantity * purchaseOrder_additionalCharges.Price * (finance_tax.Value/100)) AS VAT 
           FROM purchaseOrder
           LEFT JOIN purchaseOrder_additionalCharges ON purchaseOrder_additionalCharges.PurchaseOrderId  = purchaseOrder.Id
           LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_additionalCharges.VatTaxId
           WHERE purchaseOrder_additionalCharges.Type = 'Shipping' AND YEAR(PurchaseDate) = $year
           GROUP BY MONTH(PurchaseDate)
        )shipping ON shipping.Month = MONTH(PurchaseDate)
        WHERE  YEAR(PurchaseDate) = $year
        GROUP BY MONTH(PurchaseDate)
    STR;

    $month = array();
    for($i = 1; $i<=12; $i++)
    {
        $month[$i] = array();
        $month[$i]['Month'] = $i;
        $month[$i]['Merchandise'] = 0;
        $month[$i]['Shipping'] = 0;
        $month[$i]['VAT'] = 0;
        $month[$i]['Total'] = 0;
    }
    $totalMerchandise = 0;
    $totalShipping = 0;
    $totalVAT = 0;
    $totalTotal = 0;

    $result = $database->query($query);
    foreach($result as $item) {
        $item->Merchandise = round(floatval($item->Merchandise),4);
        $item->Shipping = round(floatval($item->Shipping),4);
        $item->VAT = round(floatval($item->VAT),4);

        if($item->Merchandise == null) $item->Merchandise = 0;
        if($item->Shipping == null) $item->Shipping = 0;
        if($item->VAT == null) $item->VAT = 0;

        $item->Total = round($item->Merchandise + $item->Shipping + $item->VAT,4);

        $totalMerchandise += $item->Merchandise;
        $totalShipping += $item->Shipping;
        $totalVAT += $item->VAT;
        $totalTotal += $item->Total;

        $month[$item->Month] = $item;
    }

    $output['TotalMerchandise'] = round($totalMerchandise, 4);
    $output['TotalShipping'] = round($totalShipping, 4);
    $output['TotalVAT'] = round($totalVAT, 4);
    $output['TotalTotal'] = round($totalTotal, 4);

    $output['MonthTotal'] = $month;

    $api->returnData($output);
}

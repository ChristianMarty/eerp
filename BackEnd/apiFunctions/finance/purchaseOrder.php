<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : apiFunctions/finance/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["Year"])) sendResponse(Null,"Year not set");

    $dbLink = dbConnect();

    $year = dbEscapeString($dbLink,$_GET["Year"]);

    $query = <<<STR
        SELECT  MONTH(PurchaseDate) AS Month, SUM(purchaseOrder_itemOrder.Quantity*purchaseOrder_itemOrder.Price) AS Merchandise,
        SUM(purchaseOrder_itemOrder.Quantity*purchaseOrder_itemOrder.Price*(finance_tax.Value/100)) + shipping.VAT AS VAT,
        shipping.Shipping
        
        FROM purchaseOrder
        LEFT JOIN purchaseOrder_itemOrder ON purchaseOrder_itemOrder.PurchaseOrderId = purchaseOrder.Id
        LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_itemOrder.VatTaxId
        
        LEFT JOIN  (
            SELECT  
                MONTH(PurchaseDate) AS Month, 
                SUM(purchaseOrder_additionalCharges.Quantity* purchaseOrder_additionalCharges.Price) AS Shipping, 
                SUM(purchaseOrder_additionalCharges.Quantity*purchaseOrder_additionalCharges.Price*(finance_tax.Value/100)) AS VAT 
            FROM purchaseOrder
            LEFT JOIN purchaseOrder_additionalCharges ON purchaseOrder_additionalCharges.PurchaseOrderId  = purchaseOrder.Id
            LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_additionalCharges.VatTaxId
            WHERE purchaseOrder_additionalCharges.Type = 'Shipping' AND YEAR(PurchaseDate) = $year
            GROUP BY MONTH(PurchaseDate)
        )shipping ON shipping.Month = MONTH(PurchaseDate)
        WHERE  YEAR(PurchaseDate) = $year
        GROUP BY MONTH(PurchaseDate)
    STR;

    $result = dbRunQuery($dbLink,$query);
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
    while($r = mysqli_fetch_assoc($result)) {

        $r['Month'] = intval($r['Month']);
        $r['Merchandise'] = round(floatval($r['Merchandise']),4);
        $r['Shipping'] = round(floatval($r['Shipping']),4);
        $r['VAT'] = round(floatval($r['VAT']),4);

        if($r['Merchandise'] == null) $r['Merchandise'] = 0;
        if($r['Shipping'] == null) $r['Shipping'] = 0;
        if($r['VAT'] == null) $r['VAT'] = 0;

        $r['Total'] = round($r['Merchandise'] + $r['Shipping'] + $r['VAT'],4);

        $totalMerchandise += $r['Merchandise'];
        $totalShipping += $r['Shipping'];
        $totalVAT += $r['VAT'];
        $totalTotal += $r['Total'];

       $month[$r['Month']] = $r;
    }

    $output['TotalMerchandise'] = $totalMerchandise;
    $output['TotalShipping'] = $totalShipping;
    $output['TotalVAT'] = $totalVAT;
    $output['TotalTotal'] = $totalTotal;

    $output['MonthTotal'] = $month;

    dbClose($dbLink);
    sendResponse($output);
}



?>
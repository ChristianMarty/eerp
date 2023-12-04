<?php
//*************************************************************************************************
// FileName : _function.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/item/_line.php";


function getPurchaseOrderData($purchaseOrderNo): ?array
{
	global $database;
	
	$vat = array();
    $query = <<<STR
        SELECT * FROM finance_tax
    STR;
	$result = $database->query($query);
	
	foreach ($result as $r)
	{
		$vat[$r->Id] = $r;
	}

    $query = <<<STR
        SELECT 
            Carrier, 
            PaymentTerms, 
            InternationalCommercialTerms, 
            HeadNote, 
            FootNote, 
            VendorContactId, 
            VendorAddressId, 
            ShippingContactId, 
            BillingContactId, 
            PurchaseContactId, 
            purchaseOrder.DocumentIds, 
            purchaseOrder.PoNo AS PurchaseOrderNumber, 
            purchaseOrder.CreationDate, 
            purchaseOrder.PurchaseDate, 
            purchaseOrder.Title, 
            purchaseOrder.Description, 
            purchaseOrder.Status, 
            purchaseOrder.Id AS PoId ,
            vendor_displayName(vendor.Id) AS SupplierName, 
            vendor.Id AS SupplierId, 
            AcknowledgementNumber, 
            OrderNumber, 
            finance_currency.CurrencyCode, 
            finance_currency.Digits AS CurrencyDigits,  
            finance_currency.Id AS CurrencyId, 
            ExchangeRate, 
            purchaseOrder.QuotationNumber 
        FROM purchaseOrder
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId 
        LEFT JOIN finance_currency ON finance_currency.Id = purchaseOrder.CurrencyId
    STR;

	if(isset($purchaseOrderNo) and $purchaseOrderNo !== null)
	{
		$query.= " WHERE PoNo = ".$purchaseOrderNo;		
	}

    $r = $database->query($query)[0];


    $purchaseOrderNumber= $r->PurchaseOrderNumber;
    $r->PurchaseOrderBarcode = barcodeFormatter_PurchaseOrderNumber($purchaseOrderNumber);
    $purchaseOrderId = $r->PoId;
    $status = $r->Status;

    unset($r->PoId);
    $output['MetaData'] = $r;

	$orderLimeResult = $database->query(purchaseOrderItem_getLineQuery($purchaseOrderId));

	$lines = array();
	foreach ($orderLimeResult as $r)
	{
		$orderLineId = purchaseOrderItem_getLineIdFromQueryResult($r);
			
		if(!array_key_exists($orderLineId,$lines))
		{
            $lines[$r->OrderLineId] = purchaseOrderItem_getDataFromQueryResult($purchaseOrderNumber, $r);

            $query = purchaseOrderItem_getCostCenterQuery($r->OrderLineId);
            $lines[$r->OrderLineId]['CostCenter'] = purchaseOrderItem_getCostCenterData($database->query($query));

			if($status == "Confirmed" or $status == "Closed")
            {
				$lines[$r->OrderLineId]['QuantityReceived'] = 0;
			}
            $r->Price = floatval($r->Price);
		}
		
		if( $r->ReceiveId != null and ($status == "Confirmed" or $status == "Closed"))
		{
			if(!array_key_exists("Received",$lines[$r->OrderLineId])) $lines[$r->OrderLineId]['Received'] = array();
			
			$received = array();
			$received['AddedStockQuantity'] = intval($r->AddedStockQuantity);
            $received['QuantityReceived'] = intval($r->QuantityReceived);
			$lines[$r['OrderLineId']]['QuantityReceived'] += $received['QuantityReceived'];
			$received['ReceivalDate'] = $r->ReceivalDate;
			$received['ReceivalId'] = intval($r->ReceiveId);
			
			$lines[$r->OrderLineId]['Received'][] = $received;
		}
	}

	$output['Lines'] = array_values($lines);
	
	$additionalCharges = Array();
    $query = <<<STR
	SELECT purchaseOrder_additionalCharges.Id AS AdditionalChargesLineId, 
	       purchaseOrder_additionalCharges.LineNo, 
	       purchaseOrder_additionalCharges.Type, 
	       purchaseOrder_additionalCharges.Price, 
	       purchaseOrder_additionalCharges.Quantity, 
	       purchaseOrder_additionalCharges.Description, 
	       finance_tax.Value AS VatValue, 
	       finance_tax.Id AS VatTaxId
	FROM purchaseOrder_additionalCharges
	LEFT JOIN finance_tax ON finance_tax.Id = purchaseOrder_additionalCharges.VatTaxId
	WHERE PurchaseOrderId = $purchaseOrderId 
	ORDER BY LineNo
	STR;
    $additionalCharges = $database->query($query);
	foreach ($result as $r)
	{
		$r->Total = floatval($r->Price * intval($r->Quantity));
	}

	$output['AdditionalCharges'] = $additionalCharges;
	
	$totalNet = 0;
	$totalVat = 0; 
	$totalAdditionalCharges = 0;
	$totalDiscount = 0; 
	$total = 0;
	
	foreach( $lines as $line)
	{
		$net = $line['QuantityOrdered']*$line['Price'];
		$discount = $net*($line['Discount']/100);
		$vat = ($net-$discount)*($line['VatValue']/100);
		
		$totalVat += $vat;
		$totalNet += $net;
		$totalDiscount += $discount;
	}
	
	foreach( $additionalCharges as $line)
	{
		$lineTotal = $line['Quantity']*$line['Price'];
		$vat = $lineTotal*($line['VatValue']/100);
		
		$totalVat += $vat;
		$totalAdditionalCharges += $lineTotal;
	}
	
	$total = ($totalNet - $totalDiscount) + $totalVat + $totalAdditionalCharges;
	
	$digits = $output['MetaData']['CurrencyDigits'];
	
	$output['Total'] = array(	"Net"=> round($totalNet, $digits, PHP_ROUND_HALF_UP),
								"Vat"=> round($totalVat, $digits, PHP_ROUND_HALF_UP),
								"AdditionalCharges"=> round($totalAdditionalCharges, $digits, PHP_ROUND_HALF_UP),								
								"Discount"=> round(-1*$totalDiscount, $digits, PHP_ROUND_HALF_UP), 
								"Total"=> round($total, $digits, PHP_ROUND_HALF_UP),
								"CurrencyCode"=> $output['MetaData']['CurrencyCode']
								);
	
	return $output;
}

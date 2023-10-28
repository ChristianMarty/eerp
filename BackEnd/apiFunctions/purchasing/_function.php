<?php
//*************************************************************************************************
// FileName : _function.php
// FilePath : apiFunctions/purchasing/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_getDocuments.php";
require_once __DIR__ . "/item/_line.php";


function getPurchaseOrderData($purchaseOrderNo): ?array
{
	$dbLink = dbConnect();
	
	$vat = array();
    $query = <<<STR
        SELECT * FROM finance_tax
    STR;
	$result = dbRunQuery($dbLink,$query);
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vat[$r['Id']] = $r;
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
            purchaseOrder.PoNo, 
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

	$result = dbRunQuery($dbLink,$query);

	$r = mysqli_fetch_assoc($result);

    $purchaseOrderNumber= $r['PoNo'];
    $r['PurchaseOrderNumber'] = $r['PoNo'];
    $r['PurchaseOrderBarcode'] = "PO-".$r['PoNo'];
    $r['CurrencyId'] = intval($r['CurrencyId']);
    $r['VendorContactId'] = intval($r['VendorContactId']);
    $r['VendorAddressId'] = intval($r['VendorAddressId']);
    $r['ShippingContactId'] = intval($r['ShippingContactId']);
    $r['BillingContactId'] = intval($r['BillingContactId']);
    $r['PurchaseContactId'] = intval($r['PurchaseContactId']);
    $purchaseOrderId = $r['PoId'];
    $status = $r['Status'];

    unset($r['PoId']);
    $output['MetaData'] = $r;

	
	$output['Lines'] = Array();
	
	$query = purchaseOrderItem_getLineQuery($purchaseOrderId);

	$orderLimeResult = dbRunQuery($dbLink,$query);

	$lines = array();
	while($r = mysqli_fetch_assoc($orderLimeResult))
	{
		$orderLineId = purchaseOrderItem_getLineIdFromQueryResult($r);
			
		if(!array_key_exists($orderLineId,$lines))
		{
            $lines[$r['OrderLineId']] = purchaseOrderItem_getDataFromQueryResult($purchaseOrderNumber, $r);

            $query = purchaseOrderItem_getCostCenterQuery($r['OrderLineId']);
            $lines[$r['OrderLineId']]['CostCenter'] = purchaseOrderItem_getCostCenterData(dbRunQuery($dbLink,$query));

			if($status == "Confirmed" or $status == "Closed")
            {
				$lines[$r['OrderLineId']]['QuantityReceived'] = 0;
			}
            $r['Price'] = floatval($r['Price']);
		}
		
		if( $r['ReceiveId'] != null and ($status == "Confirmed" or $status == "Closed"))
		{
			if(!array_key_exists("Received",$lines[$r['OrderLineId']])) $lines[$r['OrderLineId']]['Received'] = array();
			
			$received = array();
			$received['QuantityReceived'] = intval($r['QuantityReceived']);
			$lines[$r['OrderLineId']]['QuantityReceived'] += $received['QuantityReceived'];
			$received['ReceivalDate'] = $r['ReceivalDate'];
			$received['ReceivalId'] = intval($r['ReceiveId']);
			
			$lines[$r['OrderLineId']]['Received'][] = $received;
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
	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AdditionalChargesLineId'] = intval( $r['AdditionalChargesLineId']);
		$r['LineNo'] = intval( $r['LineNo']);
        $r['Price'] = floatval($r['Price']);
		$r['Total'] = floatval($r['Price'] * intval($r['Quantity']));
		$r['VatTaxId'] = intval($r['VatTaxId']);
		
		$additionalCharges[] = $r;
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

	dbClose($dbLink);	
	
	return $output;
}

?>
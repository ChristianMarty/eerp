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
require_once __DIR__ . "/../util/getDocuments.php";

function getPurchaseOrderData($purchaseOrderNo)
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$vat = array();
	$query = "SELECT * FROM finance_tax";
	$result = dbRunQuery($dbLink,$query);
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$vat[$r['Id']] = $r;
	}
	

	$query = "SELECT Carrier, PaymentTerms, InternationalCommercialTerms, Note, VendorContactId, VendorAddressId, ShippingContactId, BillingContactId, PurchaseContactId, vendor.OrderImportSupported, purchasOrder.DocumentIds, purchasOrder.PoNo, purchasOrder.CreationDate, purchasOrder.PurchaseDate, purchasOrder.Title, purchasOrder.Description, purchasOrder.Status, purchasOrder.Id AS PoId ,vendor.Name AS SupplierName, vendor.Id AS SupplierId, AcknowledgementNumber, OrderNumber, finance_currency.CurrencyCode, finance_currency.Digits AS CurrencyDigits,  finance_currency.Id AS CurrencyId, ExchangeRate, purchasOrder.QuotationNumber FROM purchasOrder ";
	$query .= "LEFT JOIN vendor ON vendor.Id = purchasOrder.VendorId ";
	$query .= "LEFT JOIN finance_currency ON finance_currency.Id = purchasOrder.CurrencyId ";
	
	if(isset($purchaseOrderNo) and $purchaseOrderNo !== null)
	{
		$purchaseOrderNo = dbEscapeString($dbLink, $purchaseOrderNo);
		$purchaseOrderNo = strtolower($purchaseOrderNo);
		$purchaseOrderNo = str_replace("po-","",$purchaseOrderNo);
		$query.= "WHERE PoNo = ".$purchaseOrderNo;		
	}
	
	$result = dbRunQuery($dbLink,$query);
	$output = array();
	$PoId = 0;
	$status = null;
	
	
	
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['CurrencyId'] = intval($r['CurrencyId']);
		$r['VendorContactId'] = intval($r['VendorContactId']);
		$r['VendorAddressId'] = intval($r['VendorAddressId']);
		$r['ShippingContactId'] = intval($r['ShippingContactId']);
		$r['BillingContactId'] = intval($r['BillingContactId']);
		$r['PurchaseContactId'] = intval($r['PurchaseContactId']);
		
		$r['OrderImportSupported'] = filter_var($r['OrderImportSupported'], FILTER_VALIDATE_BOOLEAN);
		$PoId = $r['PoId'];
		$status = $r['Status'];
		
		unset($r['PoId']);
		$output['MetaData'] = $r;
	}
	
	$output['Lines'] = Array();
	$query = "SELECT *, purchasOrder_itemOrder.Description, purchasOrder_itemOrder.Type AS LineType, finance_tax.Value AS VatValue, unitOfMeasurement.Symbol AS UnitOfMeasurementSymbol, purchasOrder_itemOrder.Id AS OrderLineId,  purchasOrder_itemReceive.Id AS ReceiveId ";
	$query .= "FROM purchasOrder_itemOrder ";
	$query .= "LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id ";
	$query .= "LEFT JOIN unitOfMeasurement ON unitOfMeasurement.Id = purchasOrder_itemOrder.UnitOfMeasurementId ";
	$query .= "LEFT JOIN finance_tax ON finance_tax.Id = purchasOrder_itemOrder.VatTaxId ";
	$query .= "WHERE PurchasOrderId = ".$PoId." ";
	$query .= "ORDER BY LineNo";
	
	$result = dbRunQuery($dbLink,$query);
	
	$lines = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$orderLineId = intval( $r['OrderLineId'], 10);
		$receivalLineId = intval( $r['ReceiveId'], 10);
			
		if(!array_key_exists($orderLineId,$lines))
		{
			$lines[$r['OrderLineId']]['LineNo'] = intval($r['LineNo']);
			$lines[$r['OrderLineId']]['Price'] = $r['Price'];
			$lines[$r['OrderLineId']]['SupplierSku'] = $r['Sku'];
			$lines[$r['OrderLineId']]['LineType'] = $r['LineType'];
			$lines[$r['OrderLineId']]['QuantityOrderd'] = intval($r['Quantity']);
			$lines[$r['OrderLineId']]['OrderLineId'] = intval($r['OrderLineId']);
			$lines[$r['OrderLineId']]['UnitOfMeasurement'] = $r['UnitOfMeasurementSymbol'];
			$lines[$r['OrderLineId']]['UnitOfMeasurementId'] =  intval($r['UnitOfMeasurementId']);
			$lines[$r['OrderLineId']]['PurchasOrderId'] = intval($r['PurchasOrderId']);
			$lines[$r['OrderLineId']]['PartNo'] = $r['PartNo'];
			$lines[$r['OrderLineId']]['ManufacturerName'] = $r['ManufacturerName'];
			$lines[$r['OrderLineId']]['ManufacturerPartNumber'] = $r['ManufacturerPartNumber'];
			$lines[$r['OrderLineId']]['Description'] = $r['Description'];
			$lines[$r['OrderLineId']]['OrderReference'] = $r['OrderReference'];
			$lines[$r['OrderLineId']]['Note'] = $r['Note'];
			$lines[$r['OrderLineId']]['ExpectedReceiptDate'] = $r['ExpectedReceiptDate'];
			$lines[$r['OrderLineId']]['VatTaxId'] = intval($r['VatTaxId']);
			$lines[$r['OrderLineId']]['VatValue'] = $r['VatValue'];
			$lines[$r['OrderLineId']]['Discount'] = $r['Discount'];
			$lines[$r['OrderLineId']]['StockPart'] = filter_var($r['StockPart'], FILTER_VALIDATE_BOOLEAN);
			$lines[$r['OrderLineId']]['LinePrice'] = $r['Price']*((100-$r['Discount'])/100);
			$lines[$r['OrderLineId']]['Total'] = $lines[$r['OrderLineId']]['LinePrice'] * intval($r['Quantity']);
			$lines[$r['OrderLineId']]['FullTotal'] = round($lines[$r['OrderLineId']]['Total'] *(1+($r['VatValue']/100)), 2, PHP_ROUND_HALF_UP);
			
			if($status == "Confirmed" or $status == "Closed")
			{
				$lines[$r['OrderLineId']]['QuantityReceived'] = 0;
			}
		}
		
		if( $r['ReceiveId'] != null and ($status == "Confirmed" or $status == "Closed"))
		{
			if(!array_key_exists("Received",$lines[$r['OrderLineId']])) $lines[$r['OrderLineId']]['Received'] = array();
			
			$received = array();
			$received['QuantityReceived'] = intval($r['QuantityReceived']);
			$lines[$r['OrderLineId']]['QuantityReceived'] += $received['QuantityReceived'];
			$received['ReceivalDate'] = $r['ReceivalDate'];
			$received['ReceivalId'] = intval($r['ReceiveId']);
			
			array_push($lines[$r['OrderLineId']]['Received'],$received);
		}

	}

	$output['Lines'] = array_values($lines);
	
	
	$totalNet = 0;
	$totalVat = 0; 
	$totalDiscount = 0; 
	$total = 0;
	
	foreach( $lines as $line)
	{
		$net = $line['QuantityOrderd']*$line['Price'];
		$discount = $net*($line['Discount']/100);
		$vat = ($net-$discount)*($line['VatValue']/100);
		
		
		$totalVat += $vat;
		$totalNet += $net;
		$totalDiscount += $discount;
		
		$total += ($net-$discount)+ $vat;
	}
	
	$digits = $output['MetaData']['CurrencyDigits'];
	
	$output['Total'] = array(	"Net"=> round($totalNet, $digits, PHP_ROUND_HALF_UP),
								"Vat"=> round($totalVat, $digits, PHP_ROUND_HALF_UP), 
								"Discount"=> round(-1*$totalDiscount, $digits, PHP_ROUND_HALF_UP), 
								"Total"=> round($total, $digits, PHP_ROUND_HALF_UP),
								"CurrencyCode"=> $output['MetaData']['CurrencyCode']
								);

	dbClose($dbLink);	
	
	return $output;
}

?>
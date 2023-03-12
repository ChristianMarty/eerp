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

	$query = "SELECT Carrier, PaymentTerms, InternationalCommercialTerms, HeadNote, FootNote, VendorContactId, VendorAddressId, ShippingContactId, BillingContactId, PurchaseContactId, vendor.OrderImportSupported, vendor.SkuSearchSupported, purchasOrder.DocumentIds, purchasOrder.PoNo, purchasOrder.CreationDate, purchasOrder.PurchaseDate, purchasOrder.Title, purchasOrder.Description, purchasOrder.Status, purchasOrder.Id AS PoId ,vendor.Name AS SupplierName, vendor.Id AS SupplierId, AcknowledgementNumber, OrderNumber, finance_currency.CurrencyCode, finance_currency.Digits AS CurrencyDigits,  finance_currency.Id AS CurrencyId, ExchangeRate, purchasOrder.QuotationNumber FROM purchasOrder ";
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
		$purchaseOrderNumber= $r['PoNo'];
		$r['PurchaseOrderNumber'] = $r['PoNo'];
        $r['PurchaseOrderBarcode'] = "PO-".$r['PoNo'];
		$r['CurrencyId'] = intval($r['CurrencyId']);
		$r['VendorContactId'] = intval($r['VendorContactId']);
		$r['VendorAddressId'] = intval($r['VendorAddressId']);
		$r['ShippingContactId'] = intval($r['ShippingContactId']);
		$r['BillingContactId'] = intval($r['BillingContactId']);
		$r['PurchaseContactId'] = intval($r['PurchaseContactId']);

        $r['SkuSearchSupported'] = filter_var($r['SkuSearchSupported'], FILTER_VALIDATE_BOOLEAN);
		$r['OrderImportSupported'] = filter_var($r['OrderImportSupported'], FILTER_VALIDATE_BOOLEAN);
		$PoId = $r['PoId'];
		$status = $r['Status'];
		
		unset($r['PoId']);
		$output['MetaData'] = $r;
	}
	
	$output['Lines'] = Array();
	
	$query = <<<STR
	SELECT 
	purchasOrder_itemOrder.LineNo,
	purchasOrder_itemOrder.Price,
	purchasOrder_itemOrder.Sku,
	purchasOrder_itemOrder.Type AS LineType,
	purchasOrder_itemOrder.Quantity,
	purchasOrder_itemOrder.Id AS OrderLineId, 
	unitOfMeasurement.Symbol AS UnitOfMeasurementSymbol, 
	unitOfMeasurement.Id AS UnitOfMeasurementId,
	purchasOrder_itemOrder.PurchasOrderId,
	purchasOrder_itemOrder.PartNo,
	purchasOrder_itemOrder.ManufacturerName,
	purchasOrder_itemOrder.ManufacturerPartNumber,
	purchasOrder_itemOrder.SupplierPartId,
	purchasOrder_itemOrder.Description,
	purchasOrder_itemOrder.OrderReference,
	purchasOrder_itemOrder.Note,
	purchasOrder_itemOrder.ExpectedReceiptDate,
	purchasOrder_itemOrder.VatTaxId,
	finance_tax.Value AS VatValue, 
	purchasOrder_itemOrder.Discount,
	purchasOrder_itemOrder.StockPart,

	purchasOrder_itemOrder.ManufacturerPartNumber AS  ManufacturerPartNumber,  
	manufacturerPart.Id AS  ManufacturerPartId, 
	purchasOrder_itemReceive.Id AS ReceiveId,
	purchasOrder_itemReceive.QuantityReceived,
	purchasOrder_itemReceive.ReceivalDate

	FROM purchasOrder_itemOrder 
	LEFT JOIN purchasOrder_itemReceive ON purchasOrder_itemReceive.ItemOrderId = purchasOrder_itemOrder.Id 
	LEFT JOIN unitOfMeasurement ON unitOfMeasurement.Id = purchasOrder_itemOrder.UnitOfMeasurementId 
	LEFT JOIN finance_tax ON finance_tax.Id = purchasOrder_itemOrder.VatTaxId 
	LEFT JOIN supplierPart ON purchasOrder_itemOrder.SupplierPartId = supplierPart.Id
	LEFT JOIN manufacturerPart ON manufacturerPart.Id = supplierPart.ManufacturerPartId
	WHERE PurchasOrderId = $PoId 
	ORDER BY LineNo
	STR;
	
	
	$result = dbRunQuery($dbLink,$query);
	
	$lines = array();

	while($r = mysqli_fetch_assoc($result)) 
	{
		$orderLineId = intval( $r['OrderLineId'], 10);
		$receivalLineId = intval( $r['ReceiveId'], 10);
			
		if(!array_key_exists($orderLineId,$lines))
		{
			$lineNumber = intval($r['LineNo']);

			$lines[$r['OrderLineId']]["PurchaseOrderBarcode"] = "PO-".$purchaseOrderNumber."#".$lineNumber;
			$lines[$r['OrderLineId']]['LineNo'] = $lineNumber;
			$lines[$r['OrderLineId']]['LineNumber'] = $lineNumber;
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
			$lines[$r['OrderLineId']]['ManufacturerPartId'] = $r['ManufacturerPartId'];
			if($r['SupplierPartId'] != null)$lines[$r['OrderLineId']]['SupplierPartId'] = intval($r['SupplierPartId']);
			else $lines[$r['OrderLineId']]['SupplierPartId'] = null;
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
			
			$lines[$r['OrderLineId']]['Received'][] = $received;
		}
	}

	$output['Lines'] = array_values($lines);
	
	
	$additionalCharges = Array();
	$query  = "SELECT purchasOrder_additionalCharges.Id AS AdditionalChargesLineId, purchasOrder_additionalCharges.LineNo, purchasOrder_additionalCharges.Type, purchasOrder_additionalCharges.Price, purchasOrder_additionalCharges.Quantity, purchasOrder_additionalCharges.Description, finance_tax.Value AS VatValue, finance_tax.Id AS VatTaxId ";
	$query .= "FROM purchasOrder_additionalCharges ";
	$query .= "LEFT JOIN finance_tax ON finance_tax.Id = purchasOrder_additionalCharges.VatTaxId ";
	$query .= "WHERE PurchasOrderId = ".$PoId." ";
	$query .= "ORDER BY LineNo";
	
	$result = dbRunQuery($dbLink,$query);
	while($r = mysqli_fetch_assoc($result)) 
	{
		$r['AdditionalChargesLineId'] = intval( $r['AdditionalChargesLineId']);
		$r['LineNo'] = intval( $r['LineNo']);
		$r['Total'] = $r['Price'] * intval($r['Quantity']);
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
		$net = $line['QuantityOrderd']*$line['Price'];
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
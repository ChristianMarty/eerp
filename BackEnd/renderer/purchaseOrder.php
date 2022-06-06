<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 01.04.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>PO-<?php echo $_GET["PurchaseOrderNo"]; ?> </title>
<link  media="print" />
 <link rel="stylesheet" href="documentTemplate_A4.css">
</head>

<?php 

if(!isset($_GET["PurchaseOrderNo"]))
{
	echo "<p>Parameter error</p>";
	exit;
}
?>

<style>
	
	div.header{
		width:100%;
		height:12mm;
		border-bottom: 0.5mm solid black;
	}
	
	img.header{
		height:10mm;
		float: right;
	}
	
	h1.header{
		font-size: large;
	}

	div.header_left{
		display: inline;
		float: left;
		width: 33.3%;
	}
	div.header_right{
		display: inline;
		float: right;
		width: 33.3%;
	}
	
	div.header_center{
		display: inline;
		width: 33.3%;
		float: left;
	}
	
	h2.header_center{
		font-size: small;
		text-align: center;
		margin-bottom: 0;
	
	}
	p.header_center{
		margin-top: 0;
	
		font-size: small;
		text-align: center;
	}

	table.header{
		border: none;
		padding: 1mm;
	}
	
	td.header, th.header{
		border: none;
		font-size: x-small;
		padding: 0mm;
		padding-right: 2mm;
	}
	
	div.main{
		margin-top: 5mm;
	}
	
	div.meta{
		display: grid;
		grid-template-columns: auto, auto;
		grid-gap: 10px;
		grid-auto-rows: auto,auto,auto;
	}
	
	div.note{
		padding-top: 5mm;
		width:100%;
		clear:both;
		margin-bottom: 5mm;
	}
	
	p.address{
		text-align: left;
		font-size: x-small;
		margin: 1mm;
	}
	
	div.footer{
		height:12mm;
		border-top: 0.5mm solid black; 
	}
	
	p.footer{
		text-align: center;
		margin: 5px;
		font-size: small;
	}
	
	table.lines {
	  font-family: arial, sans-serif;
	  border-collapse: collapse;
	  width: 100%;
	}
	
	td.lines, th.lines {
	  border-bottom: 1px solid #a0a0a0;
	  text-align: left;
	  padding: 8px;
	  font-size: x-small;
	}
	
	td.lines_total, th.lines_total {
	  text-align: right;
	  padding: 8px;
	  font-size: x-small;
	}
	
	td.lines_total_sum, th.lines_total_sum {
	  border-top: 1px solid #a0a0a0;
	  text-align: right;
	  padding: 8px;
	  font-size: x-small;
	}
	
	table.total {
	  font-family: arial, sans-serif;
	  border-collapse: collapse;

	  float: right;
	  width:30%;
	}
	
	td.total, th.total {
	  
	  text-align: left;
	  padding: 0px;
	  font-size: x-small;
	  text-align: right
	}

</style>


<?php

require_once __DIR__ . "/../apiFunctions/databaseConnector.php";
require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/../apiFunctions/purchasing/_function.php";
require_once __DIR__ . "/../apiFunctions/vendor/_function.php";

$poData = getPurchaseOrderData($_GET["PurchaseOrderNo"]);

$vendor = getVenderContact($poData["MetaData"]["VendorContactId"]);
$shipping = getVenderContact($poData["MetaData"]["ShippingContactId"]);
$billing = getVenderContact($poData["MetaData"]["BillingContactId"]);
$buyer = getVenderContact($poData["MetaData"]["PurchaseContactId"]);

global $addressId;
$footer = getVenderAddress($addressId);

$meta = new stdClass;

$meta->poNo = $_GET["PurchaseOrderNo"];

$buyerName = $buyer['LastName'];
if(isset($vendor['LastName'])) 
{
	$buyerName = $buyer['FirstName']." ".$buyer['LastName'];
}

$meta->date = $poData["MetaData"]["PurchaseDate"];
$meta->paymentTerms= $poData["MetaData"]["PaymentTerms"];
$meta->note = $poData["MetaData"]["HeadNote"];
$meta->footNote = $poData["MetaData"]["FootNote"];
$meta->carrier = $poData["MetaData"]["Carrier"];
$meta->incoterms = $poData["MetaData"]["InternationalCommercialTerms"];
$meta->name = $buyerName;
$meta->phone = $buyer['Phone'];
$meta->email = $buyer['E-Mail'];

$meta->footerLine1 = $footer['VendorName'];
$meta->footerLine2 = $footer['Street'].", ".$footer['PostalCode']." ".$footer['City'].", ".$footer['CountryName'];
$meta->footerLine3 = "";
if(isset($footer['VatTaxNumber'])) $meta->footerLine3 =  "VAT-Nr.: ".$footer['VatTaxNumber'];
if(isset($footer['CustomsAccountNumber'])) $meta->footerLine3 .= ", ZAZ-Nr.: ".$footer['CustomsAccountNumber'];

$meta->billingAddress = new stdClass;
$meta->billingAddress->name = $billing['VendorName'];
$meta->billingAddress->company = $billing['VendorName'];
$meta->billingAddress->street = $billing['Street'];
$meta->billingAddress->postalCode = $billing['PostalCode'];
$meta->billingAddress->city = $billing['City'];
$meta->billingAddress->country = $billing['CountryName'];

$meta->shippingAddress = new stdClass;
$meta->shippingAddress->name = $shipping['VendorName'];
$meta->shippingAddress->company = $shipping['VendorName'];
$meta->shippingAddress->street = $shipping['Street'];
$meta->shippingAddress->postalCode = $shipping['PostalCode'];
$meta->shippingAddress->city = $shipping['City'];
$meta->shippingAddress->country = $shipping['CountryName'];


$meta->vendor = new stdClass;
$meta->vendor->name = $vendor['LastName'];
if(isset($vendor['LastName'])) 
{
	$meta->vendor->name = $vendor['FirstName']." ".$meta->vendor->name;
}
$meta->vendor->phone = $vendor['Phone'];
$meta->vendor->email = $vendor['E-Mail'];
$meta->vendor->company = $vendor['VendorName'];
$meta->vendor->street = $vendor['Street'];
$meta->vendor->postalCode = $vendor['PostalCode'];
$meta->vendor->city = $vendor['City'];
$meta->vendor->country = $vendor['CountryName'];

$meta->page = new stdClass;
$meta->page->current = 1;
$meta->page->total = 1;

$lines = array();
$hasVat = false;
$hasDiscount = false;
foreach( $poData['Lines'] AS $srcLine)
{
	$line = new stdClass;
	
	$line->lineNo = $srcLine['LineNo'];
	$line->quantity =$srcLine['QuantityOrderd'];
	$line->symbol = $srcLine['UnitOfMeasurement'];
	$line->price = $srcLine['LinePrice'];
	$line->total = $srcLine['Total'];
	$line->sku = $srcLine['SupplierSku'];
	$line->date = $srcLine['ExpectedReceiptDate'];
	$line->discount = $srcLine['Discount'];
	$line->vat = $srcLine['VatValue'];
	$line->description = $srcLine['Description'];
	
	if(intval($srcLine['Discount']) != 0) $hasDiscount = true;
	if(intval($srcLine['VatValue']) != 0) $hasVat = true;
	
	array_push($lines, $line);
}

function add_meta($meta)
{
	$temp = "<div class='meta'>";
	
	$temp .= "<div style='grid-column: 1; grid-row: 1;'>";
	$temp .= "<table class='header'>";
	$temp .= "<tr><td class='header'><b>PO Number:</b></td><td class='header'>{$meta->poNo}</td></tr>";
	$temp .= "<tr><td class='header'><b>Date:</b></td><td class='header'>{$meta->date}</td></tr>";
	$temp .= "<tr><td class='header'><b>Payment Terms:</b></td><td class='header'>{$meta->paymentTerms}</td></tr>";
	$temp .= "<tr><td class='header'><b>Incoterms:</b></td><td class='header'>{$meta->incoterms}</td></tr>";
	$temp .= "<tr><td class='header'><b>Carrier:</b></td><td class='header'>{$meta->carrier}</td></tr>";
	$temp .= "</table>";
	$temp .= "</div>";
	
	$temp .= "<div style='grid-column: 1; grid-row: 2;'>";
	$temp .= "<p class='address'><b>Vendor:</b></p>";
	$temp .= "<p class='address'>{$meta->vendor->company}</p>";
	$temp .= "<p class='address'>{$meta->vendor->street}</p>";
	$temp .= "<p class='address'>{$meta->vendor->postalCode} {$meta->vendor->city}</p>";
	$temp .= "<p class='address'>{$meta->vendor->country}</p>";
	$temp .= "</div>";
	
	$temp .= "<div style='grid-column: 1; grid-row: 3;'>";
	$temp .= "<table class='header'>";
	$temp .= "<tr><td class='header'><b>Contact:</b></td><td class='header'>{$meta->vendor->name}</td></tr>";
	$temp .= "<tr><td class='header'><b>Phone:</b></td><td class='header'>{$meta->vendor->phone}</td></tr>";
	$temp .= "<tr><td class='header'><b>E-Mail:</b></td><td class='header'>{$meta->vendor->email}</td></tr>";
	$temp .= "</table>";
	$temp .= "</div>";

	$temp .= "<div style='grid-column: 2; grid-row: 1;'>";
	$temp .= "<p class='address'><b>Shipping Address:</b></p>";
	$temp .= "<p class='address'>{$meta->shippingAddress->company}</p>";
	$temp .= "<p class='address'>{$meta->shippingAddress->street}</p>";
	$temp .= "<p class='address'>{$meta->shippingAddress->postalCode} {$meta->shippingAddress->city}</p>";
	$temp .= "<p class='address'>{$meta->shippingAddress->country}</p>";
	$temp .= "</div>";

	$temp .= "<div style='grid-column: 2; grid-row: 2;'>";
	$temp .= "<p class='address'><b>Billing Address:</b></p>";
	$temp .= "<p class='address'>{$meta->billingAddress->company}</p>";
	$temp .= "<p class='address'>{$meta->billingAddress->street}</p>";
	$temp .= "<p class='address'>{$meta->billingAddress->postalCode} {$meta->billingAddress->city}</p>";
	$temp .= "<p class='address'>{$meta->billingAddress->country}</p>";
	$temp .= "</div>";
	
	$temp .= "<div style='grid-column: 2; grid-row: 3;'>";
	$temp .= "<table class='header'>";
	$temp .= "<tr><td class='header'><b>Contact:</b></td><td class='header'>{$meta->name}</td></tr>";
	$temp .= "<tr><td class='header'><b>Phone:</b></td><td class='header'>{$meta->phone}</td></tr>";
	$temp .= "<tr><td class='header'><b>E-Mail:</b></td><td class='header'>{$meta->email}</td></tr>";
	$temp .= "</table>";
	$temp .= "</div>";

	$temp .= "</div>";
	
	$temp .= "<div class='note'>";
	if($meta->note != null) $temp .= "<p class='address'><b>Note:</b> {$meta->note}</p>";
	$temp .= "</div>";
	
	return $temp;
}

function table_start()
{
	global $hasDiscount;
	global $hasVat;
	
	$temp = "<table class='lines'><tr class='lines'>";
    $temp .= "<th class='lines' style='text-align: right;'>Line</th>";
	$temp .= "<th class='lines'>Part No</th>";
    $temp .= "<th class='lines'>Description</th>";
    $temp .= "<th class='lines' style='text-align: right;'>Qty</th>";
	$temp .= "<th class='lines' style='text-align: center;'>Unit</th>";
	$temp .= "<th class='lines' style='text-align: center;'>Date</th>";
	$temp .= "<th class='lines' style='text-align: right;' >Unit Price</th>";
	if($hasDiscount) $temp .= "<th class='lines' style='text-align: right;' >%</th>";
	if($hasVat) $temp .= "<th class='lines' style='text-align: right;' >VAT</th>";
	$temp .= "<th class='lines' style='text-align: right;' >Total</th>";
	$temp .= "</tr>";
	
	return $temp;
}

function table_addLine($line)
{
	global $hasDiscount;
	global $hasVat;
	
	$temp = "<tr class='lines'>";
    $temp .= "<td class='lines'  style='text-align: right;'>{$line->lineNo}</td>";
	$temp .= "<td class='lines'>{$line->sku}</td>";
    $temp .= "<td class='lines'>{$line->description}</td>";
	$temp .= "<td class='lines'  style='text-align: right;'>{$line->quantity}</td>";
	$temp .= "<td class='lines'  style='text-align: center;'>{$line->symbol}</td>";
	$temp .= "<td class='lines'  style='text-align: center;'>{$line->date}</td>";
	$temp .= "<td class='lines'  style='text-align: right;'>".price_formater($line->price)."</td>";
	if($hasDiscount) $temp .= "<td class='lines'  style='text-align: right;'>{$line->discount}</td>";
	if($hasVat) $temp .= "<td class='lines'  style='text-align: right;'>{$line->vat}</td>";
	$temp .= "<td class='lines'  style='text-align: right;'>".price_formater($line->total)."</td>";
	$temp .= "</tr>";
	
	return $temp;
}

function table_end()
{
	return "</table>";
}

function table_total($total)
{
	global $hasDiscount;
	global $hasVat;
	
	$colTotalOffset = 7;
	if($hasDiscount) $colTotalOffset++;
	if($hasVat) $colTotalOffset++;
	
	$temp  = "";
	$temp .= "<tr class='lines'>";
	$temp .= "<td class='lines_total' colspan='{$colTotalOffset}'>";

	if($hasDiscount OR $hasVat) $temp .= "<b>Total Net:</b></br>";
	if($hasDiscount) $temp .= "<b>Total Discount:</b></br>";
	if($hasVat) $temp .= "<b>Total VAT:</b></br>";
	$temp .= "</td>";
	
	$temp .= "<td class='lines_total'>";
	if($hasDiscount OR $hasVat) $temp .= total_formater($total["Net"]).'<span style="color: White;">00</span></br>';
	if($hasDiscount) $temp .= total_formater($total["Discount"]).'<span style="color: White;">00</span></br>';
	if($hasVat) $temp .= total_formater($total["Vat"]).'<span style="color: White;">00</span></br>';
	$temp .= "</td></tr>";
	
	$temp .= "<tr class='lines_total'>";
	$temp .= "<td class='lines_total' colspan='{$colTotalOffset}'>";
	$temp .= "<b>Total [{$total["CurrencyCode"]}]:</b></td>";
	if($hasDiscount OR $hasVat) $temp .= "<td class='lines_total_sum'>";
	else $temp .= "<td class='lines_total'>";
	$temp .= "<b>".total_formater($total["Total"]).'<span style="color: White;">00</span><b/></td>';
	$temp .= "</tr>";
	
	return $temp;
}

function price_formater($price)
{
	return number_format($price,4,".","´");
}

function total_formater($price)
{
	return number_format($price,2,".","´");
}

function add_page($metaData, $content)
{
	global $assetsRootPath;
	
	echo "<div class='page'>";
	echo "<div class='content'>";
	
	echo "<div class='header'>";
	echo "<div class='header_left'><h1 class='header'>Purchase Order</h1></div>";
	echo "<div class='header_center'><h2 class='header_center'>PO-{$metaData->poNo}</h2>";
	echo "<p class='header_center'>Page {$metaData->page->current} of {$metaData->page->total}</p></div>";
	echo "<div class='header_right'><img class='header' src='{$assetsRootPath}/logo.png' alt='logo'></div>";
	echo "</div>";
	
	echo "<div class='main'>";
	echo $content;
	echo "</div>";
	
	echo "<div class='footer'>";
	echo "<p class='footer'><b>{$metaData->footerLine1}</b></p>";
	echo "<p class='footer'>{$metaData->footerLine2}</p>";
	echo "<p class='footer'>{$metaData->footerLine3}</p>";
	echo "</div>";
		
	echo "</div> </div>";
}


function footnote($metaData)
{
	$temp .= "<div class='note'>";
	if($metaData->footNote != null) $temp .= "<p class='address'>{$metaData->footNote}</p>";
	$temp .= "</div>";
	
	return $temp;
}

$content1 = add_meta($meta);
$content1 .= table_start();
foreach( $lines as $line)
{
	$content1 .= table_addLine($line);
}
$content1 .= table_total($poData['Total']);
$content1 .= table_end();

$content1 .= footnote($meta);

$meta->page->current = 1;
$meta->page->total = 2;

add_page($meta,$content1);

$meta->page->current = 2;
//add_page($meta,$content1);
require "purchaseOrder_attachment.php";


?>
  

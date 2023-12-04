<?php
//*************************************************************************************************
// FileName : _functions.php
// FilePath : apiFunctions/document/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/_barcodeFormatter.php";
require_once __DIR__ . "/../util/_barcodeParser.php";

function checkFileNotDuplicate($path): ?array
{
    global $database;
	
	// Check if file already exists
	$fileMd5 = md5_file($path);
	
	$query = "SELECT * FROM document WHERE Hash='$fileMd5';";

	$result = $database->query($query);

	$retuning = array();
	if(count($result))
	{
        $existingFile = $result[0];
		$retuning['preexisting'] = true;
		$retuning['hash'] = $existingFile->Hash;
		$retuning['path'] = $existingFile->Path;
		$retuning['type'] = $existingFile->Type;
		$retuning['description'] = $existingFile->Description;
	}
	else 
	{
		$retuning['preexisting'] = false;
		$retuning['hash'] = $fileMd5;
	}
	
	return $retuning;
}

function getCitations($documentId): array
{
    global $database;
    $output = array();

// Get documents from inventory
    $query = <<< STR
        SELECT 
            inventory.InvNo,
            inventory.Title,
            inventory.Type,
            inventory.Manufacturer
        FROM inventory 
        WHERE replace(json_array(DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category']= 'Inventory';
        $temp['Barcode']= barcodeFormatter_InventoryNumber($r->InvNo);
        $temp['Description']= $r->Title." - ".$r->Manufacturer." ".$r->Type;
        $output[] = $temp;
    }


// Get documents from inventory_history
    $query = <<< STR
        SELECT 
            inventory.InvNo,
            inventory.Title,
            inventory.Type,
            inventory.Manufacturer,
            inventory_history.Description,
            inventory_history.Type AS HistoryType
        FROM inventory_history 
        LEFT JOIN inventory ON inventory.Id = inventory_history.InventoryId
        WHERE replace(json_array(inventory_history.DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category']= 'Inventory History';
        $temp['Barcode']= barcodeFormatter_InventoryNumber($r->InvNo);
        $temp['Description']= $r->HistoryType." - ".$r->Description." - ".$r->Manufacturer." ".$r->Type;
        $output[] = $temp;
    }

// Get documents from manufacturerPart_series
    $query = <<< STR
        SELECT 
            manufacturerPart_series.Title,
            manufacturerPart_series.Description,
            vendor_displayName(vendor.Id) AS VendorName
        FROM manufacturerPart_series 
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_series.VendorId
        WHERE replace(json_array(manufacturerPart_series.DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category']= 'Manufacturer Part Series';
        $temp['Barcode']= 'TBD';
        $temp['Description']= $r->VendorName." ".$r->Title." - ".$r->Description;
        $output[] = $temp;
    }

// Get documents from manufacturerPart_Item
    $query = <<< STR
        SELECT 
            manufacturerPart_item.Number,
            manufacturerPart_item.Description,
            vendor_displayName(vendor.Id) AS VendorName
        FROM manufacturerPart_item
        LEFT JOIN vendor ON vendor.Id = manufacturerPart_item.VendorId
        WHERE replace(json_array(manufacturerPart_item.DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category']= 'Manufacturer Part Item';
        $temp['Barcode']= 'TBD';
        $temp['Description']= $r->VendorName." ".$r->Number." - ".$r->Description;
        $output[] = $temp;
    }

// Get documents from purchaseOrder
    $query = <<< STR
        SELECT 
            purchaseOrder.PoNo,
            purchaseOrder.Description,
            vendor_displayName(vendor.Id) AS VendorName
        FROM purchaseOrder 
        LEFT JOIN vendor ON vendor.Id = purchaseOrder.VendorId
        WHERE replace(json_array(purchaseOrder.DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category'] = 'Purchase Order';
        $temp['Barcode'] = barcodeFormatter_PurchaseOrderNumber($r->PoNo);
        $temp['Description'] = $r->VendorName." - ".$r->Description;
        $output[] = $temp;
    }


// Get documents from shipment
    $query = <<< STR
        SELECT 
            shipment.ShipmentNumber,
            shipment.Direction,
            shipment.Description
        FROM shipment 
        WHERE replace(json_array(DocumentIds), ',', '","') LIKE '%"$documentId"%'
    STR;

    $result =  $database->query($query);
    foreach ($result as $r)
    {
        $temp = array();
        $temp['Category'] = 'Shipment';
        $temp['Barcode'] = barcodeFormatter_ShipmentNumber($r->ShipmentNumber);
        $temp['Description'] = $r->Direction." - ".$r->Description;
        $output[] = $temp;
    }

    return $output;
}




/* Parameter
    $ingestData = array();
    $ingestData['FileName'] = '';
    $ingestData['Name'] = '';
    $ingestData['Type'] = null;
    $ingestData['Description'] = null;
    $ingestData['Note'] = null;
*/
function ingest(array $data): null|int|array
{

    global $database;

    $fileNameIllegalCharactersRegex = '/[ %:"*?<>|\\/]+/';

    if(!isset($data['Name']) OR $data['Name'] == "" OR $data['Name'] == null) return array('error' => "Name is not set.");
    if(!isset($data['Type']) OR $data['Type'] == "" OR $data['Type'] == null) return array('error' => "Type is not set.");
    if(!isset($data['FileName']) OR $data['FileName'] == "" OR $data['FileName'] == null) return array('error' => "File name is not set.");

    if(preg_match($fileNameIllegalCharactersRegex,$data['Name']) != 0) return array('error' => "File name contains illegal character.");

    global $serverDataPath;
    global $ingestPath;
    global $documentPath;
    $src = $serverDataPath.$ingestPath."/".$data['FileName'];
    $dstFileName = $data['Name']."_".time().".".pathinfo($src, PATHINFO_EXTENSION);

    $dst = $serverDataPath.$documentPath."/".$data['Type']."/".$dstFileName;

    if(!file_exists($src)) return array('error' => "File path invalid.");
    if(file_exists($dst)) return array('error' => "File name already exists.");

    $fileHashCheck = checkFileNotDuplicate($src);

    if($fileHashCheck['preexisting'])
    {
        return array('error' => "File already exists as ".$fileHashCheck['path']." with type ".$fileHashCheck['type']);
    }

    if(!rename($src, $dst)) return array('error' => "File copy failed.");

    $sqlData = array();
    $sqlData['Path'] = $dstFileName;
    $sqlData['Type'] = $data['Type'];
    $sqlData['Description'] = $data['Description'] ?? null;
    $sqlData['LinkType'] = "Internal";
    $sqlData['Note'] = $data['Note'];
    $sqlData['Hash'] = $fileHashCheck['hash'];
    $sqlData['DocumentNumber']['raw'] = "(SELECT generateItemNumber())";

    return $database->insert("document", $sqlData);
}

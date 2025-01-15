<?php
//*************************************************************************************************
// FileName : labelPage.php
// FilePath : apiFunctions/inventory/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../apiFunctions/util/_barcodeParser.php";
require_once __DIR__ . "/../apiFunctions/util/_barcodeFormatter.php";

require_once __DIR__ . "/../config.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Inventory Label</title>
<link  media="print" />
</head>

<style>
    div.page{
        position: absolute;
        width:192.8mm;
        height:254.4mm;

        margin-top:19.5mm;
        margin-left:6mm;
    }
    
	table, th, td {
		
	}
	div.label{
		table-layout: fixed;
		width:45.7mm;
		height:21.2mm;
		float: left;
		margin-left:1.25mm;
		margin-right:1.25mm;
		margin-top:0;
		margin-bottom:0;
			
	}
	h1.label{
		text-align: center;
		font-size: 3mm;
		margin-top:2mm;
		margin-bottom:1mm;
	}
	
	p.label{
		text-align: left;
		font-size: 2mm;
		margin-left: 3mm;
		margin-top: 0.5mm;
		margin-bottom:0.5mm;
		font-weight: bold;
	}
	img.label{
		display: block;
		margin-left: auto; 
		margin-right: auto;	
	}
</style>

<div class="page">
<?php
if($api->isGet())
{
    $parameter = $api->getGetData();

    $inventoryNumbers = [];
    if (isset($parameter->InventoryNumber)) {
        $inventoryNumbers = explode(",", $parameter->InventoryNumber);

        if(!count($inventoryNumbers)){
            echo "Inventory number list empty";
            exit;
        }

        foreach ($inventoryNumbers as &$item) {
            $item = barcodeParser_InventoryNumber($item);
            unset($item);
        }
    }

    $fieldOffset = 0;
    if (isset($parameter->Offset)) {
        $fieldOffset = $parameter->Offset;
    }

    $inventoryNumbersString = implode(", ", $inventoryNumbers);
    if(strlen($inventoryNumbersString)=== 0){
        echo "Inventory number list is empty.";
        exit;
    }

    $query = <<< STR
        SELECT 
            InventoryNumber,
            Title,
            Manufacturer,
            Type
        FROM inventory
        WHERE InventoryNumber IN( $inventoryNumbersString );
    STR;

    $rows = $database->query($query);

	for ($i = 0; $i < $fieldOffset; $i++) {
        echo "<div class='label'>";
        echo "</div>";
    }

    global $companyName;
    global $rendererRootPath;

	foreach ($rows as $row)
    {
        $category = $row->Manufacturer . " " . $row->Type;
        $invNo = $row->InventoryNumber . " ";
        $title = $row->Title . " ";

        $content = "<h1 class='label'>" . $companyName . "</h1>";
        $content .= "<p class='label'>" . $title . " </p>";
        $content .= "<p class='label'>" . $category . " </p>";
        $content .= "<p class='label'>Inv Nr. " . $invNo . " </p>";
        $content .= "<img class='label' src='" . $rendererRootPath . "/barcode/barcode?text=Inv-" . $invNo . "'/>";

        echo "<div class='label'>";
        echo $content;
        echo "</div>";
    }
}
?>
</div>
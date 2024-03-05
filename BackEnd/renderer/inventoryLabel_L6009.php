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
	table, th, td {
		
	}
	div{
		table-layout: fixed;
		width:45.7mm;
		height:21.2mm;
		float: left;
		margin-left:1.25mm;
		margin-right:1.25mm;
		margin-top:0;
		margin-bottom:0;
			
	}
	h1{
		text-align: center;
		font-size: 3mm;
		margin-top:2mm;
		margin-bottom:1mm;
	}
	
	p{
		text-align: left;
		font-size: 2mm;
		margin-left: 3mm;
		margin-top: 0.5mm;
		margin-bottom:0.5mm;
		font-weight: bold;
	}
	img{
		display: block;
		margin-left: auto; 
		margin-right: auto;	
	}
</style>

<div style="width:193mm;height:254mm;border: none;margin-top:14.5mm;margin-left:2mm;">

<?php
if($api->isGet())
{
    $parameter = $api->getGetData();

	$invNo = array();
	if (isset($parameter->invNo)) {
        $invNo = explode(",", $parameter->invNo);

        foreach ($invNo as &$item) {
            $item = barcodeParser_InventoryNumber($item);
        }
    }

    $invNoList = implode(", ", $invNo);
    $query = <<< STR
        SELECT 
            InventoryNumber,
            Title,
            Manufacturer,
            Type
        FROM inventory
        WHERE InventoryNumber IN( $invNoList );
    STR;

    $rows = $database->query($query);

	$field_offset = 0;
	if (isset($parameter->offset)) {
        $field_offset = $parameter->offset;
    }
	
	for ($i = 0; $i < $field_offset; $i++) {
        echo "<div>";
        echo "</div>";
    }

    global $companyName;
    global $rendererRootPath;

	foreach ($rows as $row)
    {
        $category = $row->Manufacturer . " " . $row->Type;
        $invNo = $row->InventoryNumber . " ";
        $title = $row->Title . " ";

        $content = "<h1>" . $companyName . "</h1>";
        $content .= "<p>" . $title . " </p>";
        $content .= "<p>" . $category . " </p>";
        $content .= "<p>Inv Nr. " . $invNo . " </p>";
        $content .= "<img src='" . $rendererRootPath . "/barcode/barcode?text=Inv-" . $invNo . "'/>";

        echo "<div>";
        echo $content;
        echo "</div>";
    }
}
?>
</div>
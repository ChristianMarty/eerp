<?php
//*************************************************************************************************
// FileName : locationBoxLabel_L4773.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

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
        width:200mm;
        height:271mm;

        margin-top:11mm;
        margin-left:2mm;

    }
    div.label{
		table-layout: fixed;
        float: left;
        width:63.5mm;
        height:33.9mm;
		margin-left:1.25mm;
		margin-right:1.25mm;
		margin-top:0;
		margin-bottom:0;

	}
	h1.label{
		text-align: center;
		font-size: 10mm;
        margin-top: 0.5mm;
        margin-bottom:0.5mm;
	}
	p.label{
		text-align: center;
		font-size: 4mm;
		margin-top: 0.5mm;
		margin-bottom:0.5mm;
		font-weight: bold;
	}
    div.title {
        table-layout: fixed;
        float: left;
        transform: rotate(90deg);

        width:49%;
        height:100%;

        position: relative;
        left: 30mm;
    }
    div.barcode {
        table-layout: fixed;
        float: left;
        transform: rotate(90deg);

        width:49%;
        height:100%;

        position: relative;
        left: -42mm;
    }
	img.label{
        width:30mm;
        height:10mm;
		display: block;
		margin-left: auto;
		margin-right: auto;
	}
</style>

<div class="page">

<?php

$parameter = $api->getGetData();

$locationNumbers = [];
if (isset($parameter->LocationNumber)) {
    $locationNumbers = explode(",", $parameter->LocationNumber);

    if(!count($locationNumbers)){
        echo "Location number list empty";
        exit;
    }

    foreach ($locationNumbers as &$item) {
        $item = \Numbering\parser(\Numbering\Category::Location, $item);
        unset($item);
    }
}

$field_offset = 0;
if (isset($parameter->Offset)) {
    $field_offset = $parameter->Offset;
}

$locationNumbersString = implode(", ", $locationNumbers);
if(strlen($locationNumbersString)=== 0){
    echo "Location number list is empty.";
    exit;
}
$query = <<< STR
    SELECT 
        LocationNumber,
        Name
    FROM location
    WHERE LocationNumber IN( $locationNumbersString );
STR;

$rows = $database->query($query);

for ($i = 0; $i < $field_offset; $i++) {
    echo "<div class='label'>";
    echo "</div>";
}

global $rendererRootPath;

foreach ($rows as $row)
{
    foreach([1,2,3,4] as $i) // 4 labels each
    {

        $locNo = $row->LocationNumber . " ";
        $name = $row->Name . " ";
        $locationBarcode = \Numbering\format(\Numbering\Category::Location, $locNo);

        $content  = "<div class='title'>";
        $content .= "<h1 class='label'>Box</h1>";
        $content .= "<h1 class='label'>$name</h1>";
        $content .= "</div>";

        $content .= "<div class='barcode'>";
        $content .= "<p class='label'>$locationBarcode</p>";
        $content .= "<img class='label' src='" . $rendererRootPath . "/barcode/barcode?text=" . $locationBarcode . "'/>";
        $content .= "</div>";

        echo "<div class='label'>";
        echo $content;
        echo "</div>";
    }
}

?>
</div>
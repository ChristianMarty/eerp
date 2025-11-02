<?php
//*************************************************************************************************
// FileName : Treston_6020-4ESD.php
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
        margin-left:5mm;
    }
    div.label{
		table-layout: fixed;
        float: left;
        width:175mm;
        height:41mm;
        
        border-style: dotted;
        border-width: 1px;
	}
    div.margin{
		table-layout: fixed;
        float: left;
        width:10mm;
        height:41mm;
        
        border-style: solid;
        border-width: 1px;
	}
	h1.title{
		text-align: center;
		font-size: 12mm;
        margin-top: 4mm;
        margin-bottom:2mm;
	}
    h2.title{
		text-align: center;
		font-size: 8mm;
        margin-top: 4mm;
        margin-bottom:2mm;
	}
    h1.box{
		text-align: center;
		font-size: 10mm;
        margin-top: 5mm;
        margin-bottom:2mm;
	}
    h2.box{
		text-align: center;
		font-size: 10mm;
        margin-top: 4mm;
        margin-bottom:2mm;
	}
    div.title {
        table-layout: fixed;
        float: left;

        width:80%;
        height:100%;
    }
    div.box {
        table-layout: fixed;
        float: left;

        width:19%;
        height:100%;
        border-left: 5px solid;
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
        Name,
        Title,
        Description
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
    $title = $row->Title;
    $description = $row->Description;
    $name = $row->Name;

    $content  = "<div class='title'>";
    $content .= "<h1 class='title'>$title</h1>";
    $content .= "<h2 class='title'>$description</h2>";
    $content .= "</div>";

    $content .= "<div class='box'>";
    $content .= "<h1 class='box'>Box</h1>";
    $content .= "<h2 class='box'>$name</h2>";
    $content .= "</div>";

    echo "<div class='margin'></div><div class='label'>";
    echo $content;
    echo "</div><div class='margin'></div>";
}

?>
</div>
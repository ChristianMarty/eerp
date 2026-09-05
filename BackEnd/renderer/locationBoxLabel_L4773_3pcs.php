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
require_once __DIR__ ."/_renderer.php";

global $assetsRootPath;
?>
<!DOCTYPE html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Location Description 3x L4773</title>

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
</head>

<div class="page">
<?php
    global $rendererRootPath;
    $locationData = \Renderer\parseLocationParameter($api->getGetData());
    if($locationData->isEmpty()){
        echo "Location number list is empty.";
        exit;
    }

    $items =[];
    for ($i = 0; $i < $locationData->offset; $i++) {
        $items[] = null;
    }

    $locationNumbersString = $locationData->sqlInString();
    $query = <<< STR
        SELECT 
            LocationNumber,
            Name
        FROM location
        WHERE LocationNumber IN( $locationNumbersString );
    STR;
    $items = array_merge($items, $database->query($query));

    $items = array_slice($items,0,8);
    foreach ($items as $row)
    {
        foreach([1,2,3] as $i) // 3 labels each
        {
            $content = "";
            if($row !== null) {
                $name = $row->Name . " ";
                $locationBarcode = \Numbering\format(\Numbering\Category::Location,  $row->LocationNumber);

                $content .= "<div class='title'>";
                $content .= "<h1 class='label'>Box</h1>";
                $content .= "<h1 class='label'>$name</h1>";
                $content .= "</div>";

                $content .= "<div class='barcode'>";
                $content .= "<p class='label'>$locationBarcode</p>";
                $content .= "<img class='label' src='" . $rendererRootPath . "/barcode/barcode?text=" . $locationBarcode . "'/>";
                $content .= "</div>";
            }

            echo "<div class='label'>";
            echo $content;
            echo "</div>";
        }
    }
?>
</div>
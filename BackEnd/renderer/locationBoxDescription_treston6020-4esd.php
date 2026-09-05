<?php
//*************************************************************************************************
// FileName : locationBoxDescription_treston6020-4esd.php
// FilePath : renderer
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
    <title>Location Description Treston 6020-4</title>

    <style>
        @font-face {
            font-family: jost;
            src: url(<?php echo $assetsRootPath; ?>/font/jost/Jost-300-Light.otf) format("opentype");
        }
        @font-face {
            font-family: jost;
            font-weight: bold;
            src: url(<?php echo $assetsRootPath; ?>/font/jost/Jost-700-Bold.otf) format("opentype");
        }
        body {
            font-family: 'jost', sans-serif;
        }
        header {
            width:100%;
            height:11mm;
        }
        div.page{
            margin-left: 11mm;
            border-top: 1px dotted;
            break-after: page;
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media not print {
            div.page{
                width:200mm;
                height:282mm;
            }
        }

        div.label{
            table-layout: fixed;
            float: left;
            width:175mm;
            height:41mm;

            border-bottom: 1px dotted;
            border-left: 1px dotted;
            border-right: 1px dotted;
        }

        div.left {
            table-layout: fixed;
            float: left;

            width:80%;
            height:100%;
        }
        div.right {
            table-layout: fixed;
            float: left;

            width:19%;
            height:100%;
            border-left: 5px solid;
        }

        div.title{
            height:20mm;
            margin: 0;

            display: flex;
            justify-content: center;
            align-items: center;
        }
        p.title{
            font-size: 16mm;
            font-weight: bold;
            width: 100%;
            margin: 0;

            text-align: center;
            word-wrap: break-word;
        }
        div.description{
            height:20mm;
            margin: 0;

            display: flex;
            justify-content: center;
            align-items: center;
        }
        p.description{
            font-size: 10mm;
            width: 100%;
            margin: 0;

            text-align: center;
            word-wrap: break-word;
        }

        p.box{
            text-align: center;
            font-size: 8mm;
            margin-top: 2mm;
            margin-bottom:1mm;
            font-weight: bold;
        }

        img.barcode {
            margin: 0;
            padding: 0;
            width:100%;
            height:25px;
        }
        p.barcode {
            padding: 0;
            text-align: center;
            font-size: 2mm;
            margin-top: 0;
            margin-bottom:0.5mm;
        }
    </style>
</head>

<header></header>
<div class='page'>
<?php
    global $rendererRootPath;
    $locationData = \Renderer\parseLocationParameter($api->getGetData());
    if($locationData->isEmpty()){
        echo "Location number list is empty.";
        exit;
    }

    $items =[];
    for ($i = 0; $i < $locationData->offset; $i++) {
        $items[] = [];
    }

    $locationNumbersString = $locationData->sqlInString();
    $query = <<< STR
        SELECT 
            LocationNumber,
            Name,
            Title,
            Description
        FROM location
        WHERE LocationNumber IN( $locationNumbersString );
    STR;
    $items = array_merge($items, $database->query($query));

    $i = 0;
    foreach ($items as $row)
    {
        $title = $row->Title??'';
        $description = $row->Description??'';
        $name = $row->Name??'';
        $locationBarcode = \Numbering\format(\Numbering\Category::Location, $row->LocationNumber??null);

        // Title / Description
        $content  = "<div class='left'>";
        $content .= "<div class='title'><p class='title'>$title</p></div>";
        $content .= "<div class='description'><p class='description'>$description</p></div>";
        $content .= "</div>";

        // Box Name / Barcode
        if($locationBarcode) {
            $content .= "<div class='right'>";
            $content .= "<p class='box'>Box</p>";
            $content .= "<p class='box'>$name</p>";
            $content .= "<div class='barcode'>";
            $content .= "<p class='barcode'>$locationBarcode</p>";
            $content .= "<img class='barcode' src='" . $rendererRootPath . "/barcode/barcode?text=" . $locationBarcode . "'/>";
            $content .= "</div></div>";
        }

        echo "<div class='label'>";
        echo $content;
        echo "</div>";

        $i++;
        if(!($i%6) && $i !== count($items)) {
            echo "</div> <header></header> <div class='page'>";
        }
    }
?>
</div>
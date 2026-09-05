<?php
//*************************************************************************************************
// FileName : locationBoxDescription_A7.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 05.09.2026
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
    <title>Location Description A7</title>

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
        div.print{
            margin-left: 4mm;
            border-top: 1px dotted;
            break-after: page;
        }
        @page {
            size: A4 portrait;
            margin: 0;
        }
        @media not print {
            div.print{
                width:200mm;
                height:282mm;
            }
        }

        div.label{
            table-layout: fixed;
            float: left;
            width: 94mm;
            height:74mm;

            border-bottom: 1px dotted;
        }
        div.margin{
            table-layout: fixed;
            float: left;
            width: 5mm;
            height:74mm;
            border-bottom: 1px dotted;
        }
        div.marginLeft{
            table-layout: fixed;
            float: left;
            width: 5mm;
            height:74mm;

            border-left: 1px dotted;
            border-bottom: 1px dotted;
        }
        div.title{
            height:37mm;
            margin: 0;

            display: flex;
            justify-content: center;
            align-items: center;
        }
        h1.title{
            font-size: 10mm;
            font-weight: bold;
            width: 100%;
            margin: 0;

            text-align: center;
            word-wrap: break-word;
        }
        div.description{
            height:37mm;
            margin: 0;

            display: flex;
            justify-content: center;
            align-items: center;
        }
        p.description{
            font-size: 7mm;
            width: 100%;
            margin: 0;

            text-align: center;
            word-wrap: break-word;
        }
    </style>
</head>

<header></header>
<div class='print'>
<?php
$parameter = $api->getGetData();

$locationNumbers = \Renderer\parseLocationList($parameter->LocationNumber??null);
if($locationNumbers === null){
    echo "Location number list is empty.";
    exit;
}

$items =[];
if (isset($parameter->Offset)) {
    for ($i = 0; $i < $parameter->Offset; $i++) {
        $items[] = [];
    }
}

$locationNumbersString = implode(", ", $locationNumbers);
$query = <<< STR
    SELECT 
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

    echo "<div class='label'>";
    echo "<div class='title'><h1 class='title'>$title</h1></div>";
    echo "<div class='description'><p  class='description'>$description</p></div>";
    echo "</div>";

    if(!($i%2)) {
        echo "<div class='margin'></div>";
        echo "<div class='marginLeft'></div>";
    }

    $i++;
    if(!($i%6) && $i !== count($items)) {
        echo "</div> <header></header> <div class='print'>";
    }
}
?>
</div>
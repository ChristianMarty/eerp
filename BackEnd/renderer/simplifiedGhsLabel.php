<?php
//*************************************************************************************************
// FileName : simplifiedGhsLabel.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 04.12.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $api;

require_once __DIR__ . "/../config.php";

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Simplified GHS Label</title>
<link  media="print" />
</head>

<style>
    div.page{
        position: absolute;
        width:205mm;
        height:271mm;

        margin-top:12mm;
        margin-left:2mm;

    }
    div.label{
        border: none;
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
		font-size: 6mm;
        margin-top: 5mm;
        margin-bottom:0.5mm;
	}
    h2.label{
        text-align: center;
        font-size: 4mm;
        margin-top: 2mm;
        margin-bottom:1mm;
    }
	p.label{
		text-align: center;
		font-size: 4mm;
		margin-top: 1mm;
		margin-bottom:1mm;
        height:10mm;
	}
    
    div.content {
        table-layout: fixed;
        float: left;
        
        width:63.5mm;
        height:33.9mm;
        
        transform: translateX(16.95mm) rotate(90deg);        
    }
    div.symbol {
        height:32mm;
    }
	img.label{
        width:15mm;
        height:15mm;
		display: block;
		margin-left: auto;
		margin-right: auto;
	}
</style>

<div class="page">

<?php

class GhsData {
    public string $name = "Brennsprit";
    public string $description = "";
    public string $signalWord = "Gefahr";
    public bool $ghs01 = false;
    public bool $ghs02 = true;
    public bool $ghs03 = false;
    public bool $ghs04 = false;
    public bool $ghs05 = false;
    public bool $ghs06 = false;
    public bool $ghs07 = true;
    public bool $ghs08 = false;
    public bool $ghs09 = false;
}


$parameter = $api->getGetData();

$field_offset = 0;

$rows[] = new GhsData();
$rows[0]->name ="Kühlschmierstoff";
$rows[0]->description ="Stabilo Cut Extreme<br>10% in Wasser";
$rows[0]->signalWord ="Achtung";
$rows[0]->ghs02 = false;

$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();
$rows[] = new GhsData();


for ($i = 0; $i < $field_offset; $i++) {
    echo "<div class='label'>";
    echo "</div>";
}

global $assetPath;

foreach ($rows as $row)
{

    $content  = "<div class='content'>";
    $content .= "<h1 class='label'>$row->name</h1>";
    $content .= "<p class='label'>$row->description</p>";


    $content .= "<div class='symbol'>";
    if($row->ghs01){
        $content .= "<img class='label' src='$assetPath/ghs/GHS01_explosive.svg'/>";
    }
    if($row->ghs02){
        $content .= "<img class='label' src='$assetPath/ghs/GHS02_flammable.svg'/>";
    }
    if($row->ghs03){
        $content .= "<img class='label' src='$assetPath/ghs/GHS03_oxidising.svg'/>";
    }
    if($row->ghs04){
        $content .= "<img class='label' src='$assetPath/ghs/GHS04_gasesUnderPressure.svg'/>";
    }
    if($row->ghs05){
        $content .= "<img class='label' src='$assetPath/ghs/GHS05_corrosive.svg'/>";
    }
    if($row->ghs06){
        $content .= "<img class='label' src='$assetPath/ghs/GHS06_toxic.svg'/>";
    }
    if($row->ghs07){
        $content .= "<img class='label' src='$assetPath/ghs/GHS07_harmful.svg'/>";
    }
    if($row->ghs08){
        $content .= "<img class='label' src='$assetPath/ghs/GHS08_hazardousToHealth.svg'/>";
    }
    if($row->ghs09){
        $content .= "<img class='label' src='$assetPath/ghs/GHS09_naturePolluting.svg'/>";
    }
    $content .= "</div>";

    $content .= "<h2 class='label'>$row->signalWord</h2>";
    $content .= "</div>";


    echo "<div class='label'>";
    echo $content;
    echo "</div>";
}

?>
</div>
<?php
//*************************************************************************************************
// FileName : target3001.php
// FilePath : apiFunctions/billOfMaterial/analyze
// Author   : Christian Marty
// Date		: 03.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

$title = "Target 3001!";
$description = "";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$output = array();
	$bom = array();
	
	$PriceTotal = 0;
	
	$data = json_decode(file_get_contents('php://input'),true);
	$csvDataStr =  $data["csv"];
	$csvDataStr = str_replace("\r","",$csvDataStr);

    $flat = false;
    if(isset($data['Flat']) && filter_var($data['Flat'],FILTER_VALIDATE_BOOLEAN)){
        $flat = true;
    }

    $buildQuantity = null;
    if(isset($data['BuildQuantity'])) $buildQuantity = intval($data['BuildQuantity']);

	$CSVLines = explode(";\n",$csvDataStr);
	
	$output = array();
	
	foreach($CSVLines as $i => $line)
	{
        $line = isset($line) ? trim($line) : false;
		if(empty($line)) continue;
		
		$temp  = str_getcsv($line,";");

        $outputLine = array();

        $outputLine["ReferenceDesignator"] = $temp["0"];
        $outputLine["Description"] =  $temp["1"];
        $outputLine["XPosition"] = $temp["2"];
        $outputLine["YPosition"] = $temp["3"];
		if($temp["4"] == "oben") $outputLine["Layer"]  = "Top";
		else if($temp["4"] == "unten") $outputLine["Layer"]  = "Bottom";
		else $outputLine["Layer"]  = "Other";
        $outputLine["Rotation"] = trim($temp["5"],"°");
        $outputLine["ProductionPartBarcode"] = $temp["6"];

        $output[] = $outputLine;
	}

    if(!$flat){
        $outputFlat = array();

        foreach($output as $i => $line)
        {
            $metaLine = array();
            $metaLine['ReferenceDesignator'] = $line['ReferenceDesignator'];
            $metaLine['XPosition'] = $line['XPosition'];
            $metaLine['YPosition'] = $line['YPosition'];
            $metaLine['Layer'] = $line['Layer'];
            $metaLine['Rotation'] = $line['Rotation'];

            if(array_key_exists($line["ProductionPartBarcode"],$outputFlat))
            {
                $outputFlat[$line["ProductionPartBarcode"]]['Quantity']++;
                $outputFlat[$line["ProductionPartBarcode"]]['Meta'][] = $metaLine;
            } else {
                $partLine = array();
                $partLine['ProductionPartBarcode'] = $line['ProductionPartBarcode'];
                $partLine['Description'] = $line['Description'];
                $partLine['Quantity'] = 1;
                $partLine['Meta'][] = $metaLine;

                $outputFlat[$line["ProductionPartBarcode"]] = $partLine;
            }
        }
        $output = $outputFlat;
    }

    if($buildQuantity)
    {
        foreach($output as $i => $line)
        {
            $productionPartData = getStockData($line["ProductionPartBarcode"]);
            unset($line['ProductionPartBarcode']);
            $output[$i] = array_merge($productionPartData,$line);
        }
    }

	sendResponse(array_values($output));
}

function getStockData($productionPartNumber)
{
    $dbLink = dbConnect();

    $productionPartNumber = dbEscapeString($dbLink, $productionPartNumber);
    $query = <<<STR
        SELECT 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartBarcode,
            productionPart.Description,
            productionPart_getQuantity(numbering.Id, productionPart.Number) AS StockQuantity,
            Cache_ReferencePrice_WeightedAverage AS ReferencePriceWeightedAverage,
            Cache_ReferencePrice_Minimum AS ReferencePriceMinimum,
            Cache_ReferencePrice_Maximum AS ReferencePriceMaximum,
            Cache_ReferenceLeadTime_WeightedAverage AS ReferenceLeadTimeWeightedAverage,
            Cache_PurchasePrice_WeightedAverage AS PurchasePriceWeightedAverage
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = '$productionPartNumber'
    STR;

    $result = dbRunQuery($dbLink, $query);
    $output = array();

    if ($r = mysqli_fetch_assoc($result)) {
        $output = $r;
    }
    else
    {
        $output["ProductionPartBarcode"] = "Unknown - ".$productionPartNumber;
        $output["ManufacturerPartNumber"] = "";
        $output["StockQuantity"] = 0;
    }

    dbClose($dbLink);
    return $output;
}

?>
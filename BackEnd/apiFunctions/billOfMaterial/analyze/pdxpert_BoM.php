<?php
//*************************************************************************************************
// FileName : pdxpert.php
// FilePath : apiFunctions/billOfMaterial/analyze
// Author   : Christian Marty
// Date		: 03.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;


require_once __DIR__ . "/../../../config.php";

$title = "PDXpert BOM";
$description = "";

if($api->isPost())
{
	$output = array();
	$bom = array();

	$data = json_decode(file_get_contents('php://input'),true);
	
	$flat = false;
    if(isset($_GET['Flat']) && filter_var($_GET['Flat'],FILTER_VALIDATE_BOOLEAN)){
        $flat = true;
    }

    $buildQuantity = null;
    if(isset($data['BuildQuantity'])) $buildQuantity = intval($data['BuildQuantity']);

	$bomCsvFile = tmpfile();
	fwrite($bomCsvFile, $data["csv"]);
	fseek($bomCsvFile, 0);
	
	$firstLine = fgetcsv($bomCsvFile, 1000, ",",'"',"\\");
	
	$refDesIndex = array_search("RefDes",$firstLine);
	$descriptionIndex = array_search("Description",$firstLine);
	$partNoIndex = array_search("Number",$firstLine);
	
	$i = 0;
	$BoMData = array();
	while (($bomLine = fgetcsv($bomCsvFile, 1000, ",",'"',"\\")) !== FALSE) 
	{
        $BoMData[$i]["PartNo"] = $bomLine[$partNoIndex];
		
		if($descriptionIndex === false) $BoMData[$i]["Value"] = "";
        else $BoMData[$i]["Value"] =  $bomLine[$descriptionIndex];
        if($refDesIndex === false) $BoMData[$i]["RefDes"] = "";
        $BoMData[$i]["RefDes"] = $bomLine[$refDesIndex];
		
		$i++;
    }
	fclose($bomCsvFile); 
	
	$BomClean = array();
	foreach ($BoMData as $line)
	{
		$refDeses = explode(",",$line['RefDes']);
		foreach ($refDeses as $refDes)
		{
			$line['RefDes'] = $refDes;
			$BomClean[] = $line;
		}
		
	}
	
	$output = array();
	
	foreach($BomClean as $i => $temp)
	{
		$refDes = trim($temp['RefDes']);
		if($refDes != "")
		{
			$line = array();
			$line["ReferenceDesignator"] = $refDes;
			$line["Description"] =  $temp['Value'];
			$line["XPosition"] = "0";
			$line["YPosition"] = "0";
			$line["Layer"]  = "Other";
			$line["Rotation"] = "0";
			$line["ProductionPartBarcode"] = "GCT-".$temp["PartNo"];
			$output[] = $line;
		}
	}

	if($flat !== true)
    { 
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
        $output = array_values($outputFlat);
    }

    if($buildQuantity)
    {
        foreach($output as $i => $line)
        {
            $productionPartData = getStockData($line["ProductionPartBarcode"]);
            unset($line['ProductionPartBarcode']);
            $output[$i] = array_merge($productionPartData, $line);
        }
    }

	$api->returnData($output);
}

function getStockData($productionPartNumber): array
{
    global $database;
    $productionPartNumber = $database->escape($productionPartNumber);

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
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = $productionPartNumber
        LIMIT 1
    STR;

    $result = $database->query($query);

    $output = array();

    if (count($result)) {
        $output = (array)$result[0];
    }
    else
    {
        $output["ProductionPartBarcode"] = "Unknown - ".$productionPartNumber;
        $output["ManufacturerPartNumber"] = "";
        $output["StockQuantity"] = 0;
    }
    return $output;
}
?>
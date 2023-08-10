<?php
//*************************************************************************************************
// FileName : pdxpert_PickList.php
// FilePath : apiFunctions/billOfMaterial/analyze
// Author   : Christian Marty
// Date		: 21.02.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

$title = "PDXpert Pick List";
$description = "";

if($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$bom = array();
	
	$PriceTotal = 0;
	
	$data = json_decode(file_get_contents('php://input'),true);

	$buildQuantity = intval($data["BuildQuantity"], 10);
	
	
	$bomCsvFile = tmpfile();
	fwrite($bomCsvFile, $data["csv"]);
	fseek($bomCsvFile, 0);
	
	$firstLine = fgetcsv($bomCsvFile, 1000, ",",'"',"\\");

	$descriptionIndex = array_search("Name",$firstLine);
	$partNoIndex = array_search("Number",$firstLine);
	$quantityIndex = array_search("Usage",$firstLine); 

	
	$i = 0;
	while (($bomLine = fgetcsv($bomCsvFile, 1000, ",",'"',"\\")) !== FALSE) 
	{
        $BoMData[$i]["PartNo"] = $bomLine[$partNoIndex];

		if($descriptionIndex === false) $BoMData[$i]["Value"] = "";
        else $BoMData[$i]["Value"] =  $bomLine[$descriptionIndex];
        if($quantityIndex === false) $BoMData[$i]["Quantity"] = 0;
        $BoMData[$i]["Quantity"] = intval($bomLine[$quantityIndex]);
		
		$i++;
    }
	
	fclose($bomCsvFile); 

	$BoM = array();
	$index = 1;
	
	
	// Sort and combine by PartNo
	foreach ($BoMData as $PartDataLine)
	{
	
		if($PartDataLine["Value"] == "DNP") $PartDataLine["PartNo"] = "DNP";
		
		if(array_key_exists($PartDataLine["PartNo"],$BoM))
		{
			if(strlen($PartDataLine["PartNo"])>1)
			{
				$BoM[$PartDataLine["PartNo"]]["RefDes"] .= ", ".$PartDataLine["RefDes"];
				$BoM[$PartDataLine["PartNo"]]["Quantity"] += $PartDataLine["Quantity"];
			}
			else
			{
				$BoMadd = array("Value"=>$PartDataLine["Value"],"PartNo"=>$index,"Quantity"=>$PartDataLine["Quantity"]);
				$BoM[$index] = $BoMadd;
				$index++;
			}
		}
		else
        {
            $PartNo = $PartDataLine["PartNo"];
            if ($PartNo == Null) continue;

            $BoMadd = array();

            $partNo = dbEscapeString($dbLink, $PartNo);
            $query = <<<STR
                SELECT productionPart.Number AS  PartNo, productionPart.Description, productionPart_getQuantity(numbering.Id ,productionPart.Number) AS StockQuantity, GROUP_CONCAT(manufacturerPart.ManufacturerPartNumber, "")  AS ManufacturerPartNumbers, 
                       Cache_ReferencePrice_WeightedAverage, Cache_PurchasePrice_WeightedAverage, Cache_ReferencePrice_Minimum, Cache_ReferencePrice_Maximum, Cache_ReferenceLeadTime_WeightedAverage
                FROM productionPart
                LEFT JOIN productionPart_manufacturerPart_mapping ON productionPart_manufacturerPart_mapping.ProductionPartId = productionPart.Id
                LEFT JOIN manufacturerPart ON  manufacturerPart.Id = productionPart_manufacturerPart_mapping.ManufacturerPartId 
                LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id
                LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
                WHERE productionPart.Number ='$partNo'
                GROUP BY manufacturerPart.Id
            STR;

            $result = dbRunQuery($dbLink, $query);

            if ($r = mysqli_fetch_assoc($result))
            {
                $BoMadd["PartNo"] = $PartDataLine["PartNo"];

                //$BoMadd["ManufacturerPartNumber"] = substr($r["ManufacturerPartNumbers"], 0,-1);
                $BoMadd["ManufacturerPartNumber"] = $r["ManufacturerPartNumbers"];
                $BoMadd["Stock"] = $r["StockQuantity"];

                $BoMadd["Cache_ReferencePrice_WeightedAverage"] = $r["Cache_ReferencePrice_WeightedAverage"];
                $BoMadd["Cache_PurchasePrice_WeightedAverage"] = $r["Cache_PurchasePrice_WeightedAverage"];
				$BoMadd["Cache_ReferencePrice_Minimum"] = $r["Cache_ReferencePrice_Minimum"];
				$BoMadd["Cache_ReferencePrice_Maximum"] = $r["Cache_ReferencePrice_Maximum"];

                $BoMadd["RefDes"] = "";
                if ($PartDataLine["Value"] == "DNP") $BoMadd["Quantity"] = 0;
                else  $BoMadd["Quantity"] = $PartDataLine["Quantity"];

                $BoMadd["Value"] = $PartDataLine["Value"];
                $BoMadd["Description"] = $r["Description"];
            }
            else
            {
                $BoMadd["PartNo"] = "Unknown " . $PartDataLine["PartNo"];
                $BoMadd["ManufacturerPartNumber"] = "";
                $BoMadd["Stock"] = 0;
            }

            $BoM[$PartDataLine["PartNo"]] = $BoMadd;
		}
	}

	// Display data
	foreach ($BoM as $PartDataLine)
	{
		$bomLine = array();
		
		//$PriceTotal += $PartDataLine["PaidPrice"]*$PartDataLine["Quantity"];

		$bomLine['RefDes'] = $PartDataLine["RefDes"];
		$bomLine['Quantity'] = $PartDataLine["Quantity"];
		$bomLine['PartNo'] = "GCT-".$PartDataLine["PartNo"];
		$bomLine['Name'] = $PartDataLine["ManufacturerPartNumber"];
		$bomLine['Value'] = $PartDataLine["Value"];
		$bomLine['Stock'] = $PartDataLine["Stock"];
        $bomLine["Description"] = $PartDataLine["Description"];
        $bomLine["ReferencePriceMinimum"] = $PartDataLine["Cache_ReferencePrice_Minimum"];
        $bomLine["ReferencePriceWeightedAverage"] = $PartDataLine["Cache_ReferencePrice_WeightedAverage"];
        $bomLine["ReferencePriceMaximum"] = $PartDataLine["Cache_ReferencePrice_Maximum"];
        $bomLine["PurchasePriceWeightedAverage"] = $PartDataLine["Cache_PurchasePrice_WeightedAverage"];
        $bomLine["ReferenceLeadTimeWeightedAverage"] = $PartDataLine["Cache_ReferenceLeadTime_WeightedAverage"];  
		$bom[] = $bomLine;
	}
	

	
	$output['bom'] = $bom;

	dbClose($dbLink);	
	sendResponse($output);
}

?>
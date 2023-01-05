<?php
//*************************************************************************************************
// FileName : pdxpert.php
// FilePath : apiFunctions/project/analyze
// Author   : Christian Marty
// Date		: 03.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

$titel = "PDXpert";
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
	
	$refDesIndex = array_search("RefDes",$firstLine);
	$descriptionIndex = array_search("Description",$firstLine);
	$partNoIndex = array_search("Number",$firstLine);
	
	$i = 0;
	while (($bomLine = fgetcsv($bomCsvFile, 1000, ",",'"',"\\")) !== FALSE) 
	{
		$BoMData[$i]["RefDes"] = $bomLine[$refDesIndex];
		$BoMData[$i]["Value"] =  $bomLine[$descriptionIndex];
		$BoMData[$i]["PartNo"] = $bomLine[$partNoIndex];
		
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
				$BoM[$PartDataLine["PartNo"]]["Quantity"] += 1;
			}
			else
			{
				$BoMadd = array("RefDes"=>$PartDataLine["RefDes"],"Value"=>$PartDataLine["Value"],"PartNo"=>$index,"Quantity"=>1);
				$BoM[$index] = $BoMadd;
				$index++;
			}
		}
		else
		{
			$BoMadd = array();
			
			$PartNo = $PartDataLine["PartNo"];
			$result = false;
			if($PartNo!= Null)
			{
                $partNo = dbEscapeString($dbLink, $PartNo);
                $query = <<<STR
                    SELECT *, productionPart_getQuantity(productionPart.PartNo) AS StockQuantity 
                    FROM productionPart
                    LEFT JOIN productionPartMapping ON productionPartMapping.ProductionPartId = productionPart.Id
                    LEFT JOIN manufacturerPart ON  manufacturerPart.Id = productionPartMapping.ManufacturerPartId 
                    LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id
                    WHERE productionPart.PartNo ='$partNo'
                    GROUP BY manufacturerPart.Id
                STR;
				
				$result = dbRunQuery($dbLink,$query);
			}
			$PartData  = array();
			$TotalQuantity = 0;
			$ManufacturerParts = "";
			
			if($result)
			{
				while($r = mysqli_fetch_assoc($result)) 
				{
					$PartData = $r;
					$TotalQuantity = $PartData["StockQuantity"];
					$ManufacturerParts = $ManufacturerParts.", ".$PartData["ManufacturerPartNumber"];
				}
			}
			
			$ManufacturerParts = substr($ManufacturerParts, 2);
			
			$BoMadd["PartNo"] = $PartDataLine["PartNo"];
			
			if(!empty($PartData))
			{
				$BoMadd["ManufacturerPartNumber"]= $ManufacturerParts;
				$BoMadd["Stock"] = $TotalQuantity;
			}
			else
			{
				$BoMadd["PartNo"] = "Unknown ".$PartDataLine["PartNo"];
				$BoMadd["ManufacturerPartNumber"]="";
				$BoMadd["Stock"] = 0;
			}
			
			$BoMadd["PaidPrice"] = "";
			
			$BoMadd["RefDes"] = $PartDataLine["RefDes"];
			$BoMadd["Quantity"] = 1;
			$BoMadd["Value"] = $PartDataLine["Value"];
			
			if($PartDataLine["Value"] == "DNP") $PartData["PaidPrice"] = 0;
		
			$BoM[$PartDataLine["PartNo"]] = $BoMadd ;
		}
	}

	// Display data
	foreach ($BoM as $PartDataLine)
	{
		$bomLine = array();
		
		//$PriceTotal += $PartDataLine["PaidPrice"]*$PartDataLine["Quantity"];

		$bomLine['RefDes'] = $PartDataLine["RefDes"];
		

		
		
		$bomLine['Quantity'] = count(explode(",", $PartDataLine["RefDes"]));//$PartDataLine["Quantity"];
		$bomLine['PartNo'] = $PartDataLine["PartNo"];
		$bomLine['Name'] = $PartDataLine["ManufacturerPartNumber"];
		$bomLine['Value'] = $PartDataLine["Value"];
		$bomLine['Price'] = $PartDataLine["PaidPrice"];
		$bomLine['Stock'] = $PartDataLine["Stock"];
		
		array_push($bom, $bomLine);
	}
	

	
	$output['bom'] = $bom;

	dbClose($dbLink);	
	sendResponse($output);
}

?>
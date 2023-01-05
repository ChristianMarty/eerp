<?php
//*************************************************************************************************
// FileName : target3001.php
// FilePath : apiFunctions/project/analyze
// Author   : Christian Marty
// Date		: 03.01.2022
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";


$titel = "Target 3001";
$description = "";



// TODO: This is not working -> fix  it all


if($_SERVER['REQUEST_METHOD'] == 'POST')
{

	$dbLink = dbConnect();
	if($dbLink == null) return null;
	
	$output = array();
	$bom = array();
	
	$PriceTotal = 0;
	
	$data = json_decode(file_get_contents('php://input'),true);
	$csvDataStr =   $data["csv"];
	$csvDataStr = str_replace("\r","",$csvDataStr);

	$CSVLines = explode(";\n",$csvDataStr);
	
	$BoMData = array();
	
	foreach($CSVLines as $i => $Line)
	{
		$Line = isset($Line) ? trim($Line) : false;
		if(empty($Line)) continue;
		
		$temp  = str_getcsv($Line,";");	

		$BoMData[$i]["RefDes"] = $temp["0"];
		$BoMData[$i]["Value"] =  $temp["1"];
		$BoMData[$i]["PartNo"] = $temp["5"];
	}

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
			
			$PartNo = explode('#', $PartDataLine["PartNo"])[0];
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
				}
			}
			
			$ManufacturerParts = substr($ManufacturerParts, 2);
			
			$BoMadd["PartNo"] = $PartDataLine["PartNo"];
			
			if(!empty($PartData))
			{
				$BoMadd["Stock"] = $TotalQuantity;
			}
			else
			{
				$BoMadd["PartNo"] = "Unknown ".$PartDataLine["PartNo"];
				$BoMadd["Stock"] = 0;
			}
			
			$BoMadd["RefDes"] = $PartDataLine["RefDes"];
			$BoMadd["Quantity"] = 1;
			$BoMadd["Value"] = $PartDataLine["Value"];
			
			$BoM[$PartDataLine["PartNo"]] = $BoMadd ;
		}
	}

	// Display data
	foreach ($BoM as $PartDataLine)
	{
		$bomLine = array();

		$bomLine['RefDes'] = $PartDataLine["RefDes"];
		$bomLine['Quantity'] = count(explode(",", $PartDataLine["RefDes"]));
		$bomLine['PartNo'] = $PartDataLine["PartNo"];
		$bomLine['Value'] = $PartDataLine["Value"];
		if(isset($PartDataLine["Stock"])) $bomLine['Stock'] = $PartDataLine["Stock"];
		else $bomLine['Stock'] = 0;

		array_push($bom, $bomLine);
	}

	
	$output['bom'] = $bom;

	dbClose($dbLink);	
	sendResponse($output);
}

?>
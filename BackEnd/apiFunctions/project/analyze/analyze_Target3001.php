<?php
//*************************************************************************************************
// FileName : bomView.php
// FilePath : apiFunctions/bom
// Author   : Christian Marty
// Date		: 30.11.2021
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

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
		$BoMData[$i]["PartNo"] = $temp["2"];
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
			
			$PartNo = $PartDataLine["PartNo"];
			$result = false;
			if($PartNo!= Null)
			{
				$query = "SELECT *, productionPart_getQuantity(productionPart.PartNo) AS StockQuantity FROM manufacturerPart ";
				$query .= "LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id ";
				$query .= "LEFT JOIN productionPart ON productionPart.ManufacturerPartId = manufacturerPart.Id ";
				$query .= "WHERE productionPart.PartNo ='".dbEscapeString($dbLink, $PartNo)."'";  
				$query .= " GROUP BY manufacturerPart.Id ";
				
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
		$bomLine['Stock'] = $PartDataLine["Stock"];
		
		array_push($bom, $bomLine);
	}

	
	$output['bom'] = $bom;

	dbClose($dbLink);	
	sendResponse($output);
}

?>
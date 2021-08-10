<?php
//*************************************************************************************************
// FileName : bomView.php
// FilePath : apiFunctions/bom
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";

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
				$query = "SELECT * FROM `electronicParts` WHERE `PartNo`='".dbEscapeString($dbLink, $PartNo)."'";  
				
				$result = dbRunQuery($dbLink,$query);
			}
			$PartData  = array();
			if($result)
			{
				while($r = mysqli_fetch_assoc($result)) 
				{
					$PartData = $r;
				}
			}
			if(!empty($PartData))
			{
				
				$BoMadd["PartNo"] = $PartDataLine["PartNo"];
				$BoMadd["ManufacturerPartNo"]= $PartData["ManufacturerPartNo"];
				$BoMadd["PaidPrice"] = $PartData["PaidPrice"];
				$BoMadd["Stock"] = $PartData["Stock"];
			}
			else
			{
				$BoMadd["PartNo"] = "Unknown Part";
				$BoMadd["ManufacturerPartNo"]="";
				$BoMadd["PaidPrice"] = "0";
				$BoMadd["Stock"] = 0;
			}
			
			$BoMadd["RefDes"] = $PartDataLine["RefDes"];
			$BoMadd["Quantity"] = 1;
			$BoMadd["Value"] = $PartDataLine["Value"];
			
			if($PartDataLine["Value"] == "DNP") $PartData["PaidPrice"] = 0;
		
			//$BoMadd = array("RefDes"=>$PartDataLine["RefDes"],"Value"=>$PartDataLine["Value"],"PartNo"=>$PartDataLine["PartNo"],"Quantity"=>1,);
			$BoM[$PartDataLine["PartNo"]] = $BoMadd ;
		}
	}

	// Display data
	foreach ($BoM as $PartDataLine)
	{
		$bomLine = array();
		
		$PriceTotal += $PartDataLine["PaidPrice"]*$PartDataLine["Quantity"];

		$bomLine['RefDes'] = $PartDataLine["RefDes"];
		$bomLine['Quantity'] = $PartDataLine["Quantity"];
		$bomLine['PartNo'] = $PartDataLine["PartNo"];
		$bomLine['Name'] = $PartDataLine["ManufacturerPartNo"];
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
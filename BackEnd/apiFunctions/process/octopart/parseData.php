<?php
//*************************************************************************************************
// FileName : parseData.php
// FilePath : apiFunctions/process/octopart
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************


$title = "Octopart Parse Data";
$description = "Parse Octopart Data and converts it to BlueNova Part Attributes.";


if($_SERVER['REQUEST_METHOD'] == 'GET')
{
	$dbLink = dbConnect();
	if($dbLink == null) return null;

    $query = <<<STR
        SELECT Id, OctopartPartData 
        FROM manufacturerPart_item
        WHERE OctopartPartData IS NOT NULL AND PartData IS NULL
    STR;

	$queryResult = dbRunQuery($dbLink,$query);
	
	dbClose($dbLink);
	
	while($partQueryData = mysqli_fetch_assoc($queryResult))
	{
		$PartData = array();
		if(is_null($partQueryData['OctopartPartData'])) continue;
		
		$OctopartPartData = json_decode($partQueryData['OctopartPartData']);
		
		$Lifecycle = null;
		$Package = null;
		
		if(!isset($OctopartPartData->specs)) continue;
		
		for($i = 0; $i < count($OctopartPartData->specs); $i++)
		{
			$attributeName = str_replace("\\","", $OctopartPartData->specs[$i]->attribute->name);
			$attributeValue = str_replace("\\","", $OctopartPartData->specs[$i]->display_value);
			
			if($attributeName == "Resistance")
			{
				$vlaue = rtrim($attributeValue, "u03a9");
				$PartData["13"] = phrase_si($vlaue);
			}
			else if($attributeName == "Capacitance")
			{
				$vlaue = rtrim($attributeValue, "F");
				$PartData["33"] = phrase_si($vlaue);
			}
			else if(($attributeName == "Voltage Rating") || ($attributeName == "Voltage Rating (DC)"))
			{
				$vlaue = rtrim($attributeValue, "V");
				$PartData["19"] = phrase_si($vlaue);
			}
			else if($attributeName == "Forward Current")
			{
				$vlaue = rtrim($attributeValue, "A");
				$PartData["26"] = phrase_si($vlaue);
			}
			else if($attributeName == "Max Reverse Voltage (DC)")
			{
				$vlaue = rtrim($attributeValue, "V");
				$PartData["24"] = phrase_si($vlaue);
			}
			else if($attributeName == "Min Supply Voltage")
			{
				$vlaue = rtrim($attributeValue, "V");
				if(!is_array($PartData["19"])) $PartData["19"] = array(null,null,null);
				$PartData["19"][0] = $vlaue;
			}
			else if($attributeName == "Max Supply Voltage")
			{
				$vlaue = rtrim($attributeValue, "V");
				if(!is_array($PartData["19"])) $PartData["19"] = array(null,null,null);
				$PartData["19"][2] = $vlaue;
			}
			else if($attributeName == "Zener Voltage")
			{
				$vlaue = rtrim($attributeValue, "V");
				$PartData["109"] = phrase_si($vlaue);
			}
			else if($attributeName == "Forward Voltage")
			{
				$vlaue = rtrim($attributeValue, "V");
				$PartData["21"] = phrase_si($vlaue);
			}
			else if($attributeName == "Max Power Dissipation")
			{
				$vlaue = rtrim($attributeValue, "W");
				$PartData["59"] = phrase_si($vlaue);
			}
			else if($attributeName == "Power Rating")
			{
				$vlaue = rtrim($attributeValue, "W");
				$PartData["38"] = phrase_si($vlaue);
			}
			else if($attributeName == "Life (Hours)")
			{
				$vlaue = rtrim($attributeValue, "hours");
				$PartData["61"] = phrase_si($vlaue);
			}
			else if($attributeName == "Leakage Current")
			{
				$vlaue = rtrim($attributeValue, "A");
				$PartData["106"] = phrase_si($vlaue);
			}
			else if($attributeName == "Ripple Current")
			{
				$vlaue = rtrim($attributeValue, "A");
				$PartData["28"] = phrase_si($vlaue);
			}
			else if($attributeName == "Dielectric")
			{
				$PartData["100"] = $attributeValue;
			}
			else if($attributeName == "ESR (Equivalent Series Resistance)")
			{
				$vlaue = rtrim($attributeValue, "u03a9");
				$PartData["18"] = phrase_si($vlaue);
			}
			else if($attributeName == "Frequency")
			{
				$vlaue = rtrim($attributeValue, "Hz");
				$PartData["52"] = phrase_si($vlaue);
			}
			else if($attributeName == "RoHS")
			{
				$PartData["108"] = $attributeValue;
			}
			else if($attributeName == "Frequency Stability")
			{
				$PartData["85"] = $attributeValue;
			}
			else if($attributeName == "Tolerance")
			{
				$vlaue = rtrim($attributeValue, "%");
				$PartData["86"] = $vlaue;
			}
			else if($attributeName == "Min Operating Temperature")
			{
				$vlaue = rtrim($attributeValue, "u00b0C");
				if(!is_array($PartData["53"])) $PartData["53"] = array(null,null,null);
				$PartData["53"][0] = phrase_si($vlaue);
			}
			else if($attributeName == "Max Operating Temperature")
			{
				$vlaue = rtrim($attributeValue, "u00b0C");
				if(!is_array($PartData["53"])) $PartData["53"] = array(null,null,null);
				$PartData["53"][2] = phrase_si($vlaue);
			}
			else if($attributeName == "Lifecycle Status")
			{
				$Lifecycle = $attributeValue;
				$Lifecycle = explode(" ",$Lifecycle)[0];
			}
			else if($attributeName == "Case/Package")
			{
				$Package = $attributeValue;
			}
			else if($attributeName == "Number of Contacts")
			{
				$PartData["64"] = $attributeValue;
			}
			else if($attributeName == "Number of Pins")
			{
				$PartData["64"] = $attributeValue;
			}
			else if($attributeName == "Number of Rows")
			{
				$PartData["110"] = $attributeValue;
			}
			else if($attributeName == "Orientation")
			{
				$PartData["111"] = $attributeValue;
			}
			else if($attributeName == "Gender")
			{
				$PartData["112"] = $attributeValue;
			}
			else if($attributeName == "Pitch")
			{
				$PartData["113"] = $attributeValue;
			}
			else if($attributeName == "Color")
			{
				$PartData["89"] = $attributeValue;
			}
			else if($attributeName == "Polarity")
			{
				$PartData["102"] = $vlaue;
			}
			else if($attributeName == "Drain to Source Breakdown Voltage")
			{
				$vlaue = rtrim($attributeValue, "V");
				$PartData["22"] = phrase_si($vlaue);
			}
		}
		
		$dbLink = dbConnect();
		if($dbLink == null) return null;
		
		$query = "UPDATE manufacturerPart SET PartData = '".json_encode($PartData)."' ";
		if(!is_null($Lifecycle)) $query .= ",Status = '".$Lifecycle."' ";
		if(!is_null($Package)) $query .= ",PackageId = (SELECT id FROM partPackage where Name = '".$Package."' OR Alias = '".$Package."')";
		$query .= " WHERE Id = ".$partQueryData['Id'];
		
		dbRunQuery($dbLink,$query);
		dbClose($dbLink);

	}
	
	sendResponse($partData);
}


function phrase_si($inputVlaue)
{
	$p = explode(" ",$inputVlaue);
	
	$value = intval($p[0],10);
	
	if(isset($p[1]))
	{
		if($p[1] == 'u00b5') $value = $value/1000000; // mikro
		else if($p[1] == 'ppm') $value = $value/1000000; 
		else
		{
			$si = substr($p[1],0,1);
			
			if( $si == 'k') $value = $value*1000;
			else if($si == 'M') $value = $value*1000000;
			else if($si == 'm') $value = $value/1000;
			else if($si == 'u') $value = $value/1000000; // mikro
			else if($si == 'n') $value = $value/1000000000;
			else if($si == 'p') $value = $value/1000000000000;
		}
		
		
	}
	return $value;
}

?>
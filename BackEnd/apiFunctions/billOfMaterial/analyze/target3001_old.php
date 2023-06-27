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


$title = "Target 3001";
$description = "";



// TODO: This is not working -> fix  it all


if($_SERVER['REQUEST_METHOD'] == 'POST')
{
	$dbLink = dbConnect();

    $data = json_decode(file_get_contents('php://input'),true);

    $bomCsvFile = tmpfile();
    fwrite($bomCsvFile, $data["csv"]);
    fseek($bomCsvFile, 0);

    $firstLine = fgetcsv($bomCsvFile, 1000, ";",'"',"\\");

    $nameIndex = array_search("Name",$firstLine);
    $wertIndex = array_search("Wert",$firstLine);
    //$posXIndex = array_search("PosX",$firstLine);
    //$posYIndex = array_search("PosY",$firstLine);
    //$woIndex = array_search("Wo",$firstLine);
    //$rotationIndex = array_search("Rotation",$firstLine);
    $lagerIndex = array_search("ARTICLE_LAGER",$firstLine);

    $BoMData = array();
    while (($bomLine = fgetcsv($bomCsvFile, 1000, ";",'"',"\\")) !== FALSE)
    {
        $temp = array();
        $temp["PartNo"] = $bomLine[$lagerIndex];
        if($wertIndex === false) $temp["Value"] = "";
        else $temp["Value"] =  $bomLine[$wertIndex];
        if($nameIndex === false) $temp["RefDes"] = "";
        $temp["RefDes"] = $bomLine[$nameIndex];

        $BoMData[] = $temp;
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
            $PartNo = $PartDataLine["PartNo"];
            if ($PartNo == Null) continue;

            $BoMadd = array();

            $partNo = dbEscapeString($dbLink, $PartNo);
            $query = <<<STR
                SELECT PartNo, productionPart.Description, productionPart_getQuantity(productionPart.PartNo) AS StockQuantity, GROUP_CONCAT(manufacturerPart.ManufacturerPartNumber, "")  AS ManufacturerPartNumbers
                FROM productionPart
                LEFT JOIN productionPartMapping ON productionPartMapping.ProductionPartId = productionPart.Id
                LEFT JOIN manufacturerPart ON  manufacturerPart.Id = productionPartMapping.ManufacturerPartId 
                LEFT JOIN partStock On partStock.ManufacturerPartId = manufacturerPart.Id
                WHERE productionPart.PartNo ='$partNo'
                GROUP BY manufacturerPart.Id
            STR;

            $result = dbRunQuery($dbLink, $query);

            if ($r = mysqli_fetch_assoc($result))
            {
                $BoMadd["PartNo"] = $PartDataLine["PartNo"];

               //$BoMadd["ManufacturerPartNumber"] = substr($r["ManufacturerPartNumbers"], 0,-1);
                $BoMadd["ManufacturerPartNumber"] = $r["ManufacturerPartNumbers"];
                $BoMadd["Stock"] = $r["StockQuantity"];


                $BoMadd["RefDes"] = $PartDataLine["RefDes"];
                if ($PartDataLine["Value"] == "DNP") $BoMadd["Quantity"] = 0;
                else  $BoMadd["Quantity"] = 1;

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
        $bomLine['Quantity'] = count(explode(",", $PartDataLine["RefDes"]));//$PartDataLine["Quantity"];
        $bomLine['PartNo'] = $PartDataLine["PartNo"];
        $bomLine['Name'] = $PartDataLine["ManufacturerPartNumber"];
        $bomLine['Value'] = $PartDataLine["Value"];
        $bomLine['Stock'] = $PartDataLine["Stock"];
        $bomLine["Description"] = $PartDataLine["Description"];

        $bom[] = $bomLine;
    }



    $output['bom'] = $bom;

    dbClose($dbLink);
    sendResponse($output);
}

?>
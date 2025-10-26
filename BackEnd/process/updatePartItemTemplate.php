<?php
//*************************************************************************************************
// FileName : updatePartItemTemplate.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 20.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../util/siFormatter.php";
require_once  __DIR__ . "/../part/manufacturerPart/_function.php";

$title = "Update Part Item Template";
$description = "Update Part Item Template from Part Series Template";
$parameter = null;

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    $dbLink = dbConnect();

// Get Series
    echo "Get Part Series Data \n";
    $query = <<<STR
        SELECT * FROM manufacturerPart_series
    STR;
    $seriesResults = dbRunQuery($dbLink, $query);

    $seriesDataList = array();
    while($seriesItemData = mysqli_fetch_assoc($seriesResults))
    {
        $id = intval($seriesItemData['Id']);
        $seriesDataList[$id] = $seriesItemData;
    }

    echo "Get Items \n";
    $query = <<<STR
        SELECT * FROM manufacturerPart_item
        WHERE SeriesId IS NOT NULL
    STR;
    $itemResults = dbRunQuery($dbLink, $query);

    while($itemData = mysqli_fetch_assoc($itemResults))
    {
        $itemId = intval($itemData['Id']);
        $partNumber = $itemData['Number'];

        if($partNumber == null) continue;
        if(str_contains($partNumber,'{')) continue; // the number is already a template

        $seriesId = intval($itemData['SeriesId']);
        if($seriesId== null) continue;

        $seriesData = $seriesDataList[$seriesId];
        $numberTemplate = $seriesData['NumberTemplate'];

        if($seriesData['Parameter'] == null) continue;
        $templateParameter = json_decode($seriesData['Parameter'], true);

        $options = decodeNumberTemplateParameters($numberTemplate,$templateParameter);
        $decodedNumberParts =  decodeNumber($partNumber,$options);

        if($decodedNumberParts == null) continue;

        // compile template
        $output = "";
        foreach($decodedNumberParts as $part)
        {
            if($part['Type'] == "PackagingUnit") {
                $output .= "{".$part['Name']."}";
            }else{
                $output .= $part['Value'];
            }
        }

     /*   echo "<pre>";
        var_dump($numberTemplate);
        var_dump($number);
        var_dump($output);
        var_dump($decodedNumberParts);
        echo "</pre>";
        exit;  //*/

        if(strlen($output) == 0) continue;

        $query = <<<STR
            UPDATE manufacturerPart_item SET Temp_NumberTemplate = '$output'
            WHERE manufacturerPart_item.Id = $itemId
        STR;
        dbRunQuery($dbLink, $query);

    }
    dbClose($dbLink);
    echo "Done \n";
    exit;
}
?>
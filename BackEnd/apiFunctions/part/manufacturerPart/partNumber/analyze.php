<?php
//*************************************************************************************************
// FileName : analyze.php.php
// FilePath : apiFunctions/manufacturerPart/partNumber/
// Author   : Christian Marty
// Date		: 25.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../../../databaseConnector.php";
require_once __DIR__ . "/../../../../config.php";
require_once __DIR__ . "/../_function.php";


function generateItemNumberTemplate($numberTemplate, $parameter, $number) :string
{
    if($numberTemplate == null) return "";
    if($number == null) return "";
    if($parameter == null) return "";

    $numberTemplate = trim($numberTemplate);
    $number = trim($number);
    if($numberTemplate == "") return "";
    if($number == "") return "";

    $options = decodeNumberTemplateParameters($numberTemplate,$parameter);
    if($options == null || !count($options)) return "";

    $size = $options[0]['position'];
    $output =  substr($number, 0,$size);

    foreach($options as $o)
    {
        // var_dump($o);
        $numberPart = substr($number,$size);

        if(str_starts_with($numberPart,'{'.$o["parameter"]['Name'].'}'))
        {
            $size += strlen('{'.$o["parameter"]['Name'].'}');
            continue;
        }

        if(isset($o['parameter']["Values"])) {
            foreach ($o['parameter']["Values"] as $v) {
                if (str_starts_with($numberPart, $v['Value'])) {
                    $size += strlen($v['Value']);
                    if($o['parameter']['Type'] == 'Functional' || $o['parameter']['Type'] == "Fill") {
                        if ($v['Value'] !== null) $output .= $v['Value'];
                    }else{
                        if ($v['Value'] !== null) $output .= "{".$o['parameter']['Name']."}";
                    }
                    break;
                }
            }
        }else{

            $length = intval($o['parameter']["Length"]);
            $decoder = $o['parameter']["Decoder"];
            $part = substr($numberPart, 0,$length);

            $output .= $part;
            $size += $length;
        }
    }

    if(str_starts_with($output, "; ")) $output = substr($output,2);

    return $output;
}

if($_SERVER['REQUEST_METHOD'] == 'GET')
{
    if(!isset($_GET["VendorId"])) sendResponse(NULL, "VendorId not specified");
    if(!is_numeric($_GET["VendorId"])) sendResponse(NULL, "VendorId not numeric");
    if(!isset($_GET["PartNumber"])) sendResponse(NULL, " PartNumber not specified");

    $output = array();
    $dbLink = dbConnect();

    $vendorId = intval($_GET["VendorId"]);
    $partNumber = dbEscapeString($dbLink, trim($_GET["PartNumber"]));

    // Try to match manufacturer part series
    $manufacturerPartSeries = seriesDataFromNumber($dbLink, $vendorId, $partNumber);
    $partParameter = array();
    if($manufacturerPartSeries == null)
    {
        $output['SeriesMatch'] = false;
        $output['SeriesData'] = null;
    }
    else
    {
        $partParameter = getParameter($dbLink, $manufacturerPartSeries['SeriesId']);
        $manufacturerPartSeries['PartNumberDescription'] = descriptionFromNumber( $manufacturerPartSeries['NumberTemplate'], $partParameter, $partNumber);

        $output['SeriesMatch'] = true;
        $output['SeriesData'] = $manufacturerPartSeries;
    }

    // Try to match manufacturer part number
    $manufacturerPartNumberData = partNumberDataFromNumber($dbLink, $vendorId, $partNumber);

    if($manufacturerPartNumberData == null)
    {
        $output['PartNumberPreExisting'] = false;
        $output['PartNumberData'] = null;
        $output['ItemId'] = null;
        $output['ItemMatch'] = false;
        $output['ItemData'] = null;
    }
    else
    {
        $output['PartNumberPreExisting'] = true;
        $output['PartNumberData'] = $manufacturerPartNumberData;
        $output['ItemId'] = $manufacturerPartNumberData['ItemId'];
        $output['ItemMatch'] = true;
        $output['ItemData'] = itemDataFromItemId($dbLink, $output['ItemId']);
    }


    // Try to match manufacturer part item
    if($output['ItemId'] == null) // If part number did not exist search for an item
    {
        $temp = array();
        $temp['Number'] = generateItemNumberTemplate($manufacturerPartSeries['NumberTemplate'], $partParameter, $partNumber);
        $output['ItemData'] = $temp;
    }

    dbClose($dbLink);
    sendResponse($output);
}
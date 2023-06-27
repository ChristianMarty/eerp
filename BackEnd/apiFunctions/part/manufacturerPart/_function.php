<?php
//*************************************************************************************************
// FileName : _function.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 30.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
require_once __DIR__ . "/../../databaseConnector.php";
require_once __DIR__ . "/../../../config.php";

function seriesDataFromNumber($dbLink, $vendorId, $partNumber) :array | null
{
    $vendorId = intval($vendorId);

    $query = <<<STR
        SELECT 
               manufacturerPart_series.Title,
               manufacturerPart_series.Description,
               manufacturerPart_series.NumberTemplate,
               manufacturerPart_series.SeriesNameMatch,
               manufacturerPart_series.Description,
               manufacturerPart_series.Id AS SeriesId, 
               vendor.Name AS VendorName 
        FROM manufacturerPart_series
        LEFT JOIN vendor on vendor.Id = manufacturerPart_series.VendorId
        WHERE manufacturerPart_series.VendorId = '$vendorId'
    STR;

    $result = mysqli_query($dbLink,$query);

    if(!$result) return null;

    while($r = mysqli_fetch_assoc($result))
    {
        if( preg_match($r['SeriesNameMatch'], trim($partNumber))) {
            $r['SeriesId'] = intval($r['SeriesId']);
            return $r;
        }
    }
    return null;
}

function partNumberDataFromNumber($dbLink, $vendorId, $partNumber) :array | null
{
    $vendorId = intval($vendorId);
    $partNumber = dbEscapeString($dbLink,trim($partNumber));

    $query = <<<STR
        SELECT 
               manufacturerPart_partNumber.Number,
               manufacturerPart_partNumber.MarkingCode,
               manufacturerPart_series.Id AS SeriesId,
               manufacturerPart_item.Id AS ItemId,
               manufacturerPart_item.Number AS NumberTemplate
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item ON manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series ON manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        WHERE (manufacturerPart_partNumber.VendorId = '$vendorId' OR manufacturerPart_item.VendorId = '$vendorId' OR manufacturerPart_series.VendorId = '$vendorId') AND manufacturerPart_partNumber.Number = '$partNumber'
    STR;

    $result = mysqli_query($dbLink,$query);
    if(!$result) return null;

    $r = mysqli_fetch_assoc($result);
    if(!$r) return null;

    if(!is_null($r['SeriesId'])) $r['SeriesId'] = intval($r['SeriesId']);
    if(!is_null($r['ItemId'])) $r['ItemId'] = intval($r['ItemId']);

    return $r;
}

function itemDataFromItemId($dbLink, $itemId) :array | null
{
    $itemId = intval($itemId);
    $query = <<<STR
        SELECT * 
        FROM manufacturerPart_item

        WHERE manufacturerPart_item.Id = '$itemId'
    STR;

    $result = mysqli_query($dbLink,$query);

    if(!$result) return null;

    return  mysqli_fetch_assoc($result);
}



function optionsSort($a, $b)
{
    return $a['position'] - $b['position'];
}

function descriptionFromNumber($numberTemplate, $parameter, $number) :string
{
    if($numberTemplate == null) return "";
    if($number == null) return "";
    if($parameter == null) return "";

    $numberTemplate = trim($numberTemplate);
    $number = trim($number);
    if($numberTemplate == "") return "";
    if($number == "") return "";


    // Find parameters in template sting and order by occurrence
    $options = array();
    foreach($parameter as $p)
    {
        $tmp = array();
        $tmp['position'] = strpos($numberTemplate,'{'.$p['Name'].'}');
        $tmp['parameter'] = $p;

        if($tmp['position']) $options[] = $tmp;
    }
    usort($options, "optionsSort");

    if(!count($options)) return "";


    // Fill in non-parameters in the middle of the part number e.g {}-{}
    $lastPos = 0;
    $lastLength = 0;
    foreach ($options as $key=>$o)
    {
        if($key != 0)
        {
            if( $lastPos+$lastLength == $o['position']) continue;

            $tmp = array();
            $tmp['position'] = $lastPos+$lastLength;

            $parameter = array();
            $values = array();
            $values['Value'] = substr($numberTemplate,$lastPos+$lastLength,$o['position']-($lastPos+$lastLength));
            $values['Description'] = null;

            $parameter['Values'][] = $values;
            $parameter['Name'] = null;
            $tmp['parameter'] = $parameter;

            if($tmp['position']) $options[] = $tmp;
        }

        $lastPos = $o['position'];
        $lastLength = strlen('{'.$o['parameter']['Name'].'}');
    }
    usort($options, "optionsSort");


    $size = $options[0]['position'];
    $output = "";

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
                    if ($v['Description'] !== null) $output .= "; " . $v['Description'];
                    break;
                }
            }
        }else{


            $length = intval($o['parameter']["Length"]);
            $decoder = $o['parameter']["Decoder"];
            $part = substr($numberPart, 0,$length);

            ob_start();
            $input = $part;
            eval($decoder);
            $description = ob_get_contents();
            ob_end_clean();

            $output .= "; " . $description;
            $size += $length;
        }
    }

    if(str_starts_with($output, "; ")) $output = substr($output,2);

    return $output;
}

function getParameter($dbLink,$manufacturerPartSeriesId) :array|null
{
    $manufacturerPartSeriesId = intval($manufacturerPartSeriesId);

    $query = <<<STR
        SELECT *
        FROM manufacturerPart_series
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
    STR;

    $result = mysqli_query($dbLink,$query);
    $r = mysqli_fetch_assoc($result);
    if($r == null) return null;
    $parameter = $r['Parameter'];

    if($parameter == null) return null;
    if(trim($parameter) == "") return null;
    return json_decode($parameter,true);
}

function getAttributes($dbLink) :array
{
    $attributes  = array();
    $query = <<<STR
    SELECT manufacturerPart_attribute.Id, manufacturerPart_attribute.ParentId, manufacturerPart_attribute.Name, 
           manufacturerPart_attribute.Type, manufacturerPart_attribute.Scale, 
           unitOfMeasurement.Name AS UnitName, unitOfMeasurement.Unit, unitOfMeasurement.Symbol
    FROM manufacturerPart_attribute
    LEFT JOIN unitOfMeasurement On unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
    STR;

    $result = mysqli_query($dbLink,$query);

    while($r = mysqli_fetch_assoc($result))
    {
        $id = $r['Id'];
        unset($r['Id']);
        $attributes[$id] = $r;
    }
    return $attributes;
}
function decodeAttributes($attributes, $partDataInput) :array
{
    $partData = array();

    if($partDataInput != null)
    {
        $partDataRaw = json_decode($partDataInput);
        foreach ($partDataRaw as $key =>$value)
        {
            $dataSet = array();
            $attributeName = $attributes[$key]['Name'];
            if(is_array($value))
            {
                $value['Minimum'] = $value['0'];
                $value['Typical'] = $value['1'];
                $value['Maximum'] = $value['2'];
                unset($value['0']);
                unset($value['1']);
                unset($value['2']);
            }

            $dataSet['Name'] = $attributeName;
            $dataSet['AttributeId'] = $key;
            $dataSet['Value']= $value;
            $dataSet['Unit']= $attributes[$key]['Unit'];
            $dataSet['Symbol']= $attributes[$key]['Symbol'];
            $partData[] = $dataSet;
        }
    }

    return $partData;
}


?>
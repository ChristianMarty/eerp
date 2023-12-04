<?php
//*************************************************************************************************
// FileName : _function.php
// FilePath : apiFunctions/manufacturerPart/
// Author   : Christian Marty
// Date		: 30.04.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function seriesDataFromNumber(int $vendorId, string $partNumber) :array | null
{
    global $database;

    $query = <<<STR
        SELECT 
               manufacturerPart_series.Title,
               manufacturerPart_series.Description,
               manufacturerPart_series.NumberTemplate,
               manufacturerPart_series.SeriesNameMatch,
               manufacturerPart_series.Description,
               manufacturerPart_series.Id AS SeriesId, 
               vendor_displayName(vendor.Id) AS VendorName 
        FROM manufacturerPart_series
        LEFT JOIN vendor on vendor.Id = manufacturerPart_series.VendorId
        WHERE manufacturerPart_series.VendorId = '$vendorId'
    STR;

    $result = $database->query($query);

    if(count($result) == 0) return null;

    foreach ($result as $r)
    {
        if( preg_match($r->SeriesNameMatch, trim($partNumber)))
        {
            return (array)$r;
        }
    }
    return null;
}

function partNumberDataFromNumber(int $vendorId, string $partNumber) :array | null
{
    global $database;
    $partNumber = $database->escape(trim($partNumber));
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
        WHERE (manufacturerPart_partNumber.VendorId = '$vendorId' OR manufacturerPart_item.VendorId = '$vendorId' OR manufacturerPart_series.VendorId = '$vendorId') AND manufacturerPart_partNumber.Number = $partNumber
    STR;

    $result = $database->query($query);
    if(count($result)== 0) return null;

    $r = $result[0];
    if(!is_null($r->SeriesId)) $r->SeriesId = intval($r->SeriesId);
    if(!is_null($r->ItemId)) $r->ItemId = intval($r->ItemId);

    return $r;
}

function itemDataFromItemId($dbLink, int $itemId) :array | null
{
    $query = <<<STR
        SELECT * 
        FROM manufacturerPart_item
        WHERE manufacturerPart_item.Id = '$itemId'
    STR;

    $result = mysqli_query($dbLink,$query);

    if(!$result) return null;

    return  mysqli_fetch_assoc($result);
}

function decodeNumberTemplateParameters($numberTemplate, $parameter) :array | null
{
    $numberTemplateLength =  strlen($numberTemplate);

    // Find parameters in template sting and order by occurrence
    $options = array();
    foreach($parameter as $p)
    {
        $tmp = array();
        $pattern = '{'.$p['Name'].'}';
        $tmp['position'] = strpos($numberTemplate,$pattern);
        $tmp['endPosition'] = $tmp['position'] + strlen($pattern)-1;
        $tmp['parameter'] = $p;

        if($tmp['position']) $options[] = $tmp;
    }
    usort($options, "optionsSort");

    if(!count($options)) return null;

    // Fill in non-parameters at the start
    if($options[0]['endPosition'] !== 0)
    {
        $tmp = array();
        $tmp['position'] = 0;
        $tmp['endPosition'] = $options[0]['position']-1;

        $parameter = array();
        $values = array();
        $values['Value'] = substr($numberTemplate,$tmp['position'],$tmp['endPosition']-$tmp['position']+1);
        $values['Description'] = null;

        $parameter['Values'][] = $values;
        $parameter['Name'] = null;
        $parameter["Type"] = "Text";
        $tmp['parameter'] = $parameter;
        $options[] = $tmp;
    }

    usort($options, "optionsSort");

    // Fill in non-parameters in the middle e.g {}-{}
    foreach ($options as $key=>$o)
    {
        if($key != 0){

            if ($options[$key-1]['endPosition']+1 == $o['position']) continue;

            $tmp = array();
            $tmp['position'] = $options[$key-1]['endPosition']+1;
            $tmp['endPosition'] = $o['position']-1;

            $parameter = array();
            $values = array();
            $values['Value'] = substr($numberTemplate, $tmp['position'],  $tmp['endPosition']-$tmp['position']+1);
            $values['Description'] = null;

            $parameter['Values'][] = $values;
            $parameter['Name'] = null;
            $parameter["Type"] = "Text";
            $tmp['parameter'] = $parameter;
            $options[] = $tmp;
        }
    }
    usort($options, "optionsSort");

    // Fill in non-parameters at the end
    $lastOption = end($options);
    if( $lastOption['endPosition'] < $numberTemplateLength-1){

        $tmp = array();
        $tmp['position'] = $lastOption['endPosition']+1;
        $tmp['endPosition'] = $numberTemplateLength;

        $parameter = array();
        $values = array();
        $values['Value'] = substr($numberTemplate, $tmp['position'],$tmp['endPosition']-$tmp['position']+1);
        $values['Description'] = null;

        $parameter['Values'][] = $values;
        $parameter['Name'] = null;
        $parameter["Type"] = "Text";
        $tmp['parameter'] = $parameter;
        $options[] = $tmp;
    }

    return($options);
}

function decodeNumber($number, $numberTemplateParts) : null|array
{
    if($number == null) return null;
    if($numberTemplateParts == null) return null;

    $output = array();

    $numberPart = $number;
    foreach($numberTemplateParts as $part)
    {
        if(isset($part['parameter']["Values"])) {
            foreach ($part['parameter']["Values"] as $v) {
                if (str_starts_with($numberPart, $v['Value'])) {

                    $temp = array();
                    $temp['Value'] = $v['Value'];
                    $temp['Description'] = $v['Description'];
                    $temp['Name'] = $part['parameter']['Name'];
                    $temp['Type'] = $part['parameter']['Type'];

                    $numberPart = substr($numberPart,  strlen($temp['Value']));
                    $output[] = $temp;
                    break;
                }
            }
        }else{
            $length = intval($part['parameter']["Length"]);
            $decoder = $part['parameter']["Decoder"];
            $inputPart = substr($numberPart, 0,$length);

            ob_start();
            $input = $inputPart;
            eval($decoder);
            $description = ob_get_contents();
            ob_end_clean();

            $temp = array();
            $temp['Value'] = $inputPart;
            $temp['Description'] = $description;
            $temp['Name'] = $part['parameter']['Name'];
            $temp['Type'] = $part['parameter']['Type'];

            $numberPart = substr($numberPart,  $length);
            $output[] = $temp;
        }
    }

    return $output;
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

    $options = decodeNumberTemplateParameters($numberTemplate,$parameter);
    if($options == null || !count($options)) return "";

    $decodedNumberParts =  decodeNumber($number,$options);

    $output = "";
    foreach($decodedNumberParts as $part)
    {
        if($part['Type'] == "Text") continue;
        $output .= "; " .$part['Description'];
    }

    if(str_starts_with($output, "; ")) $output = substr($output,2);

    return $output;
}

function getParameter(int|null $manufacturerPartSeriesId) :array|null
{
    if($manufacturerPartSeriesId === null) return null;

    global  $database;

    $query = <<<STR
        SELECT *
        FROM manufacturerPart_series
        WHERE manufacturerPart_series.Id = '$manufacturerPartSeriesId'
    STR;

    $result = $database->query($query);
    if(count($result) == 0) return null;

    $parameter = $result[0]->Parameter;

    if($parameter == null) return null;
    if(trim($parameter) == "") return null;
    return json_decode($parameter,true);
}

function getAttributes() :array
{
    global $database;

    $query = <<<STR
    SELECT manufacturerPart_attribute.Id, manufacturerPart_attribute.ParentId, manufacturerPart_attribute.Name, 
           manufacturerPart_attribute.Type, manufacturerPart_attribute.Scale, 
           unitOfMeasurement.Name AS UnitName, unitOfMeasurement.Unit, unitOfMeasurement.Symbol
    FROM manufacturerPart_attribute
    LEFT JOIN unitOfMeasurement On unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
    STR;
    $result = $database->query($query);

    $attributes = [];
    foreach ($result as $r)
    {
        $id = $r->Id;
        unset($r->Id);
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
            $attributeName = $attributes[$key]->Name;
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
            $dataSet['Unit']= $attributes[$key]->Unit;
            $dataSet['Symbol']= $attributes[$key]->Symbol;
            $partData[] = $dataSet;
        }
    }

    return $partData;
}

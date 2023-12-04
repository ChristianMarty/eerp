<?php
//*************************************************************************************************
// FileName : prodPartsMatch.php
// FilePath : apiFunctions/process/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../../vendor/_preprocessor/_partNumberPreprocessing.php";

$title = "Match Parts";
$description = "Match Manufacturer Part against PartLookup and production parts.";

if($api->isGet())
{
// Add already found Manufacrurer Part Ids to mapping list 
    $query = <<<STR
        INSERT IGNORE
        INTO productionPart_manufacturerPart_mapping(ProductionPartId, ManufacturerPartNumberId, MatchCertainty) 
        SELECT 
            ProductionPartId,
            ManufacturerPartNumberId,
            0 AS MatchCertainty
        FROM partLookup WHERE ManufacturerPartNumberId IS NOT NULL AND ProductionPartId IS NOT NULL;
    STR;
    $database->execute($query);

// Get Part Lookup Data
    $query = <<<STR
    SELECT
        ProductionPartId,
        ManufacturerPartNumber,
        VendorId
    FROM partLookup
    WHERE VendorId IS NOT NULL AND ManufacturerPartNumberId IS NULL;
    STR;
    $queryResult = $database->query($query);
    $partLookup = array();
    
    // Sort by ManufacturerId
    foreach ($queryResult as $part)
    {
        $mfrId = $part->VendorId;
        
        if( !isset($partLookup[$mfrId]) || !is_array($partLookup[$mfrId]))
        {
            $partLookup[$mfrId] = array();
        }
        $partLookup[$mfrId][] = $part;
    }
    
// Get Manufacturer Part Numbers
    $query = <<<STR
        SELECT
            vendor.Id AS VendorId, 
            vendor.PartNumberPreprocessor AS Preprocessor,
            manufacturerPart_partNumber.Number AS ManufacturerPartNumber,
            manufacturerPart_partNumber.Id AS ManufacturerPartNumberId
        FROM manufacturerPart_partNumber
        LEFT JOIN manufacturerPart_item On manufacturerPart_item.Id = manufacturerPart_partNumber.ItemId
        LEFT JOIN manufacturerPart_series On manufacturerPart_series.Id = manufacturerPart_item.SeriesId
        LEFT JOIN vendor ON vendor.Id <=> manufacturerPart_item.VendorId OR vendor.Id <=> manufacturerPart_partNumber.VendorId OR vendor.Id <=> manufacturerPart_series.VendorId
        WHERE vendor.Id IS NOT NULL
    STR;
    $queryResult = $database->query($query);

    $mfrParts = array();
    // Sort by ManufacturerId
    foreach ($queryResult as $part)
    {
        $mfrId = $part->VendorId;
        
        if( !isset($mfrParts[$mfrId]) || !is_array($mfrParts[$mfrId]))
        {
            $mfrParts[$mfrId] = array();
        }
        
        $mfrParts[$mfrId][] = $part;
    }
    
    
// Match parts
    $matches = array();
    $count = 0;
    foreach($partLookup as  $partLookupMfr)
    {
        $mfrId = $partLookupMfr[0]['VendorId'];

        if(!array_key_exists($mfrId, $mfrParts)) continue;

        $preprocessorName = $partLookupMfr[0]['Preprocessor'];
        $preprocessor = new PartNumberPreprocess($preprocessorName);
        
        foreach($mfrParts[$mfrId] as $mfrPart)
        {
            foreach($partLookupMfr as $lookupPart)
            {
                $temp = findMatch($lookupPart, $mfrPart, $preprocessor);
                
                if(!is_null($temp))
                {
                    $count++;
                    $matches[] = $temp;
                }
            }
        }
    }
    
    foreach($matches as  $line)
    {
        addProdPart($line['ProductionPartId'],$line['ManufacturerPartNumberId'],$line['MatchCertainty']);
    }/*
// Add manualy enterd Part Number if no found
    $dbLink = dbConnect();
    $query = <<<STR
        SELECT
            partStock.ManufacturerPartId,
            partStock.OrderReference 
        FROM partStock
        LEFT JOIN productionPart_manufacturerPart_mapping ON partStock.ManufacturerPartId = productionPart_manufacturerPart_mapping.ManufacturerPartId
        LEFT JOIN productionPart ON partStock.ManufacturerPartId = productionPart_manufacturerPart_mapping.ManufacturerPartId
        WHERE (partStock.OrderReference != productionPart.Number OR (productionPart.Number IS NULL AND partStock.OrderReference IS NOT NULL)) AND partStock.OrderReference != ''
        GROUP BY partStock.ManufacturerPartId,partStock.OrderReference
    STR;
    $queryResult = dbRunQuery($dbLink,$query);
    dbClose($dbLink);
    while($part = mysqli_fetch_assoc($queryResult))
    {
        addProdPart($part['OrderReference'],$part['ManufacturerPartId'],"5");
        $matches[] = $part;
    }*/

    $api->returnData($matches);
}

function findMatch($lookup, $part, $preprocessor)
{
    set_time_limit(20);
    
    $mfrPartNr = trim($part['ManufacturerPartNumber']);  
    $lookupPartNr = trim($lookup['ManufacturerPartNumber']);

    if($mfrPartNr == $lookupPartNr) 
    {
        $temp = array();
        $temp['ProductionPartId'] = $lookup['ProductionPartId'];
        $temp['ManufacturerPartNumberId'] = $part['ManufacturerPartNumberId'];
        $temp['MatchCertainty'] = "1";
        return $temp;
    }
    
    $mfrPartNr = str_replace(' ','',strtolower($mfrPartNr));
    $lookupPartNr = str_replace(' ','',strtolower($lookupPartNr));
    
    if($mfrPartNr == $lookupPartNr) 
    {
        $temp = array();
        $temp['ProductionPartId'] = $lookup['ProductionPartId'];
        $temp['ManufacturerPartNumberId'] = $part['ManufacturerPartNumberId'];
        $temp['MatchCertainty'] = "2";
        return $temp;
    }

    if(str_replace('-','',$mfrPartNr) == str_replace('-','',$lookupPartNr )) 
    {
        $temp = array();
        $temp['ProductionPartId'] = $lookup['ProductionPartId'];
        $temp['ManufacturerPartNumberId'] = $part['ManufacturerPartNumberId'];
        $temp['MatchCertainty'] = "3";
        return $temp;
    }

    $tempMfrPartNr = $preprocessor->clean($mfrPartNr);
    $templookupPartNr = $preprocessor->clean($lookupPartNr);
    if($tempMfrPartNr == $templookupPartNr) 
    {
        $temp = array();
        $temp['ProductionPartId'] = $lookup['ProductionPartId'];
        $temp['ManufacturerPartNumberId'] = $part['ManufacturerPartNumberId'];
        $temp['MatchCertainty'] = "4";
        return $temp;
    }
    return null;
}

function like_match($pattern, $subject): bool
{
    $pattern = str_replace('%', '.*', preg_quote($pattern, '/'));
    return (bool) preg_match("/^{$pattern}$/i", $subject);
}

function addProdPart($productionPartId, $manufacturerPartNumberId, $matchCertainty)
{
    global $database;
    $query = <<<STR
        INSERT IGNORE 
        INTO productionPart_manufacturerPart_mapping(ProductionPartId, ManufacturerPartNumberId, MatchCertainty) 
        VALUES("$productionPartId","$manufacturerPartNumberId","$matchCertainty");
    STR;

    $database->execute($query);
}

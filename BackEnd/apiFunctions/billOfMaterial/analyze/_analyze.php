<?php
//*************************************************************************************************
// FileName : _analyze.php
// FilePath : apiFunctions/project/analyze
// Author   : Christian Marty
// Date		: 15.04.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

class BomAnalyzeOutputLine
{

}

abstract class BomAnalyzeBase
{
    protected string $title;
    protected string $description;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function analyze(string $input): stdClass
    {
        return new stdClass();
    }

    public function getStockData(string $productionPartNumber) :array
    {
        global $database;
        $productionPartNumber = $database->escape($productionPartNumber);

        $query = <<<STR
        SELECT 
            CONCAT(numbering.Prefix,'-',productionPart.Number) AS ProductionPartBarcode,
            productionPart.Description,
            productionPart_getQuantity(numbering.Id, productionPart.Number) AS StockQuantity,
            Cache_ReferencePrice_WeightedAverage AS ReferencePriceWeightedAverage,
            Cache_ReferencePrice_Minimum AS ReferencePriceMinimum,
            Cache_ReferencePrice_Maximum AS ReferencePriceMaximum,
            Cache_ReferenceLeadTime_WeightedAverage AS ReferenceLeadTimeWeightedAverage,
            Cache_PurchasePrice_WeightedAverage AS PurchasePriceWeightedAverage
        FROM productionPart
        LEFT JOIN numbering ON numbering.Id = productionPart.NumberingPrefixId
        WHERE CONCAT(numbering.Prefix,'-',productionPart.Number) = $productionPartNumber
        LIMIT 1
    STR;

        $result = $database->query($query);

        $output = array();

        if (count($result)) {
            $output = (array)$result[0];
        }
        else
        {
            $output["ProductionPartBarcode"] = "Unknown - ".$productionPartNumber;
            $output["ManufacturerPartNumber"] = "";
            $output["StockQuantity"] = 0;
        }
        return $output;
    }
}
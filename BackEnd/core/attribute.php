<?php
//*************************************************************************************************
// FileName : attribute.php
// FilePath : core/
// Author   : Christian Marty
// Date		: 11.01.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Attribute;
require_once __DIR__ . "/database.php";

class Item implements \JsonSerializable
{
    public int $attributeId;
    public string $value;
    public string|null $description;

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->id = $this->attributeId;
        $output->value = $this->value;
        if($this->description !== null){
            $output->description = $this->description;
        }
        return $output;
    }
}

class ItemDecoded implements \JsonSerializable
{
    public string $name;
    public string $value;
    public string|null $description;

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->Name = $this->name;
        $output->Value = $this->value;
        $output->Description = $this->description??"";
        return $output;
    }
}

class Attribute
{
    private array $attributeTable;

    function __construct() {
        global $database;

        $query = <<<STR
            SELECT 
                manufacturerPart_attribute.Id,
                manufacturerPart_attribute.ParentId, 
                manufacturerPart_attribute.Name, 
                unitOfMeasurement.Symbol, 
                unitOfMeasurement.Unit, 
                manufacturerPart_attribute.Type, 
                Scale
            FROM `manufacturerPart_attribute` 
            LEFT JOIN `unitOfMeasurement` ON unitOfMeasurement.Id = manufacturerPart_attribute.UnitOfMeasurementId
        STR;
        $attributeTable = $database->query($query);
        if($attributeTable instanceof \Error\Data){
            $this->attributeTable = [];
            return;
        }

        foreach($attributeTable as $r) {
            $this->attributeTable[$r->Id] = $r;
        }
    }

    private function getAttributeName(int $id) : string
    {
        return $this->attributeTable[$id]->Name;
    }

    public function decode(string|null $attributeData) : array | \Error\Data
    {
        if($attributeData === null) return [];
        if(empty($attributeData)) return [];
        if(empty($this->attributeTable)) return \Error\generic('Attribute table not loaded');

        $attributeList = json_decode($attributeData);
        if($attributeList === null) return \Error\generic('Attribute list decoder error');

        $output = [];
        foreach($attributeList as $item){
            $itemDecoded = new ItemDecoded();
            $itemDecoded->name = $this->getAttributeName($item->id);
            $itemDecoded->value = (string)$item->value;
            $itemDecoded->description = $item->description??null;
            $output[] = $itemDecoded;
        }
        return $output;
    }
}

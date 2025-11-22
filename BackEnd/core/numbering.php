<?php
//*************************************************************************************************
// FileName : numbering.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 01.11.2025
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Numbering;

require_once __DIR__ . "/error.php";

enum Category implements \JsonSerializable
{
    case Undefined;
    case Inventory;
    case InventoryAccessory;
    case Vendor;
    case PurchaseOrder;
    case ProductionPart;
    case SupplierPart;
    case WorkOrder;
    case PickingOrder;
    case Document;
    case Location;
    case Stock;
    case StockHistoryIndex;
    case Assembly;
    case AssemblyUnit;
    case AssemblyUnitHistory;
    case ManufacturerPartSeries;
    case ManufacturerPart;
    case ManufacturerPartNumber;
    case Shipment;
    case CostCenter;
    case Project;
    case SpecificationPart;
    case SpecificationPartRevision;
    case TestSystem;
    case BillOfMaterial;

    public function jsonSerialize(): string
    {
        return match ($this) {
            Category::Undefined => "",
            Category::Inventory => "Inventory",
            Category::InventoryAccessory => "Inventory Accessory",
            Category::Vendor => "Vendor",
            Category::PurchaseOrder => "Purchase Order",
            Category::ProductionPart => "Production Part",
            Category::SupplierPart => "Supplier Part",
            Category::WorkOrder => "Work Order",
            Category::PickingOrder => "Picking Order",
            Category::Document => "Document",
            Category::Location => "Location",
            Category::Stock => "Stock",
            Category::StockHistoryIndex => "Stock History",
            Category::Assembly => "Assembly",
            Category::AssemblyUnit => "Assembly Unit",
            Category::AssemblyUnitHistory => "Assembly Unit History",
            Category::ManufacturerPartSeries => "Manufacturer Part Series",
            Category::ManufacturerPart => "Manufacturer Part",
            Category::ManufacturerPartNumber => "Manufacturer Part Number",
            Category::Shipment => "Shipment",
            Category::CostCenter => "Cost Center",
            Category::Project => "Project",
            Category::SpecificationPart => "Specification Part",
            Category::SpecificationPartRevision => "Specification Part Revision",
            Category::TestSystem => "Test System",
            Category::BillOfMaterial => "Bill Of Material"
        };
    }
}

function matchCategory(string $categoryName) : Category|null
{
    $categoryName = strtolower($categoryName);
    $categoryName = str_replace(' ', '', $categoryName);


    foreach (Category::cases() as $case) {
        $caseName = strtolower($case->name);
        $caseName = str_replace(' ', '', $caseName);
        if($categoryName == $caseName)  return $case;
    }
    return null;
}

function prefix(Category $category = Category::Undefined): string|null
{
    return match($category){
        Category::Undefined => "",
        Category::Inventory => "Inv",
        Category::InventoryAccessory => "Inv",
        Category::Vendor => "Ven",
        Category::PurchaseOrder => "PO",
        Category::ProductionPart => null,
        Category::SupplierPart => "",
        Category::WorkOrder => "WO",
        Category::PickingOrder => "Pick",
        Category::Document => "Doc",
        Category::Location => "Loc",
        Category::Stock => "STK",
        Category::StockHistoryIndex => "STK",
        Category::Assembly => "ASM",
        Category::AssemblyUnit => "ASU",
        Category::AssemblyUnitHistory => "ASH",
        Category::ManufacturerPartNumber => "",
        Category::ManufacturerPartSeries => "",
        Category::Shipment => "Shp",
        Category::CostCenter => "CC",
        Category::Project => "Pjct",
        Category::SpecificationPart => "Spec",
        Category::SpecificationPartRevision => "Spec",
        Category::TestSystem => "TSY",
        Category::BillOfMaterial => "BOM",
    };
}

function format(Category $category, string|int|null $number = null, string|int|null $index = null, string|int|null $parameter = null): string|null
{
    if($category === Category::Undefined) return null;
    if($number === null) return null;

    $return ="";
    if($category !== Category::ProductionPart) {
        $return = prefix($category)."-";
    }

    if($category === Category::Stock){
        $return .= strtoupper($number);
    }else if($category === Category::PurchaseOrder){
        $return .= $number;
    }else if($category === Category::ProductionPart){
        $return .= $number;
    }else{
        $return .=str_pad(strval($number),5,"0", STR_PAD_LEFT);
    }

    if($index !== null){
        $return .= "-".$index;
    }

    if($parameter !== null){
        $return .= "#".$parameter;
    }

    return  $return;
}

class ItemCode
{
    public Category $category = Category::Undefined;
    public string|int $number;
    public string|int $parameter;
}

function checkNumber(Category $category, string|int|null $input): string|int|null
{
    if($category === Category::Stock){
        // ABCDEFGHIJKLMNPQRSTUVWXYZ0123456789

        if(strlen($input) != 4) return null;
        else return $input;
    }else{
        if(!is_numeric($input)) return null;
        else return intval($input);
    }
}

function parser(Category $category, string|int|null $input): string|int|null
{
    if($input === null ) return null;
    if(is_numeric($input)) return intval($input);

    $input = trim($input);
    $input = strtolower($input);

    if($category == Category::ProductionPart){
        // TODO: Fix this
        return $input;
    }

    $parameterParts = explode('#',$input);
    $input = $parameterParts[0];

    $numberParts = explode('-',$input);

    if(    $category == Category::StockHistoryIndex
        || $category == Category::InventoryAccessory
        || $category == Category::SpecificationPartRevision
    ){

        if(count($numberParts) !== 3) return null;
    }

    if(count($numberParts) === 1){
        return checkNumber($category, $numberParts[0]);

    }else if(count($numberParts) === 2){
        $prefix = strtolower(prefix($category));
        if($prefix !== $numberParts[0]) return null;
        return checkNumber($category, $numberParts[1]);

    }else if(count($numberParts) === 3){
        if($category == Category::PurchaseOrder){
            return intval($numberParts[1]);
        }
        if($category == Category::Inventory){
            return intval($numberParts[1]);
        }

        if(    $category == Category::StockHistoryIndex
            || $category == Category::InventoryAccessory
            || $category == Category::SpecificationPartRevision
        ){
            return intval($numberParts[2]);
        }
    }

    return null;
}
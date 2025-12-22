<?php
//*************************************************************************************************
// FileName : user.php
// FilePath : core/user/
// Author   : Christian Marty
// Date		: 11.02.2025
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/userSettings.php";

enum Permission
{
// Error
    case Error_Php;
    case Error_ApiTrace;


// Search
    case Search;


// Vendor
    case Vendor_List;
    case Vendor_View;
    case Vendor_Create;
    case Vendor_Edit;

    case Vendor_Contact_View;


// Finance
    case Finance_View;
    case Finance_CostCenter_View;


// PurchaseOrder
    case PurchaseOrder_List;
    case PurchaseOrder_View;
    case PurchaseOrder_Create;
    case PurchaseOrder_Edit;
    case PurchaseOrder_Confirm;


// Inventory
    case Inventory_List;
    case Inventory_View;
    case Inventory_Create;
    case Inventory_Edit;

    case Inventory_History_View;
    case Inventory_History_Create;
    case Inventory_History_Edit;

    case Inventory_Accessory_Edit;

    case Inventory_Purchase_View;
    case Inventory_Purchase_Edit;


// Location
    case Location_List;
    case Location_View;
    case Location_Edit;
    case Location_Transfer;
    case Location_BulkTransfer;


// Stock
    case Stock_List;
    case Stock_Create;
    case Stock_View;
    case Stock_Edit;
    case Stock_Delete;
    case Stock_RequestCounting;
    case Stock_Split;

    case Stock_History_View;
    case Stock_History_Add;
    case Stock_History_Remove;
    case Stock_History_Count;
    case Stock_History_Edit;


// SupplierPart
    case SupplierPart_List;
    case SupplierPart_View;
    case SupplierPart_Create;
    case SupplierPart_Edit;


// ManufacturerPartSeries
    case ManufacturerPartSeries_List;
    case ManufacturerPartSeries_View;
    case ManufacturerPartSeries_Create;
    case ManufacturerPartSeries_Edit;


// ManufacturerPart
    case ManufacturerPart_List;
    case ManufacturerPart_View;
    case ManufacturerPart_Create;
    case ManufacturerPart_Edit;


// ManufacturerPartNumber
    case ManufacturerPartNumber_List;
    case ManufacturerPartNumber_View;
    case ManufacturerPartNumber_Create;
    case ManufacturerPartNumber_Edit;


// ProductionPart
    case ProductionPart_List;
    case ProductionPart_View;
    case ProductionPart_Create;
    case ProductionPart_Edit;


// SpecificationPart
    case SpecificationPart_List;
    case SpecificationPart_View;
    case SpecificationPart_Create;
    case SpecificationPart_Edit;


// WorkOrder
    case WorkOrder_List;
    case WorkOrder_View;
    case WorkOrder_Create;
    case WorkOrder_Edit;


// Project
    case Project_List;
    case Project_View;


// Assembly
    case Assembly_List;
    case Assembly_View;
    case Assembly_Create;
    case Assembly_Edit;

    case Assembly_Unit_View;
    case Assembly_Unit_Edit;
    case Assembly_Unit_Create;

    case Assembly_Unit_History_View;
    case Assembly_Unit_History_Edit;
    case Assembly_Unit_History_Create;


// BillOfMaterial
    case BillOfMaterial_List;
    case BillOfMaterial_View;
    case BillOfMaterial_Create;


// Document
    case Document_List;
    case Document_View;

    case Document_Ingest_List;
    case Document_Ingest_Upload;
    case Document_Ingest_Download;
    case Document_Ingest_Delete;
    case Document_Ingest_Save;

    case Document_Attach_Edit;


// Metrology
    case Metrology_TestSystem_List;
    case Metrology_TestSystem_View;
    case Metrology_TestSystem_Create;


// Process
    case Process_List;
    case Process_Run;


// Report
    case Report_List;
    case Report_Run;


// Renderer
    case Renderer_List;
    case Renderer_View;
    case Renderer_Print_Inventory;
    case Renderer_Print_Location;


// Peripheral
    case Peripheral_List;
    case Peripheral_Scale_Use;
    case Peripheral_Printer_Use;


    public static function toUnFlat(): stdClass
    {
        $output = new \stdClass();

        foreach (self::cases() as $case) {
            $parts = explode("_",$case->name);

            $item = &$output;
            foreach($parts as $key =>$part){
                if(!property_exists($item, $part)){
                    if(($key === array_key_last($parts))) {
                        $item->{$part} = true;
                    }else{
                        $item->{$part} = new \stdClass();
                    }
                }
                $item = &$item->{$part};
            }
        }
        return $output;
    }

    public static function fromName(string $name): null | Permission
    {
        foreach (self::cases() as $case) {
            if( strtolower($name) === strtolower($case->name) ){
                return $case;
            }
        }
        return null;
    }
}

class UserInformation implements \JsonSerializable
{
    public string $initials;
    public string $name;

    public function jsonSerialize(): \stdClass
    {
        $output = new \stdClass();
        $output->Name = $this->name;
        $output->Initials = $this->initials;
        return $output;
    }
}

class User
{
    public int $id = 0;
    public string $name;
    public string $initials;
    public userSettings $settings;
    public null|\stdClass $rights;
    public array $permissions; // of type Permission

    function __construct()
    {
        $this->settings = new userSettings();
    }

    function initializePermissions(): void
    {
        if($this->rights === null){
            $this->permissions = [];
            return;
        }

        $permissionsArray = [];
        self::buildPermissions_recursive($this->rights,$permissionsArray, "");
        $this->permissions = $permissionsArray;
    }

    private function buildPermissions_recursive($rolesObject, &$permissionsArray, $roleStringPart, bool $root = true): string
    {
        $categoryStringPart = $roleStringPart;
        foreach($rolesObject as $key => $role)
        {
            if($root) $roleStringPart = "";
            if(is_object($role) || is_array($role)) {
                $roleStringPart = self::buildPermissions_recursive($role,$permissionsArray,$categoryStringPart.$key."_",false);
            } else {
                if($role){
                    $permissionsString = $roleStringPart.$key;
                    $enumItem = \Permission::fromName($permissionsString);
                    if($enumItem !== null) {
                        $permissionsArray[] = $enumItem;
                    }
                }
            }
        }
        return $categoryStringPart;
    }
}
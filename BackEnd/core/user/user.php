<?php
//*************************************************************************************************
// FileName : user.php
// FilePath : /core/user/
// Author   : Christian Marty
// Date		: 11.02.2025
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/userRoles.php";
require_once __DIR__ . "/userSettings.php";

enum Permission
{
    case Search;

    case Vendor_List;
    case Vendor_View;
    case Vendor_Create;
    case Vendor_Edit;

    case PurchaseOrder_List;
    case PurchaseOrder_View;
    case PurchaseOrder_Create;
    case PurchaseOrder_Edit;
    case PurchaseOrder_Confirm;

    case Inventory_List;
    case Inventory_View;
    case Inventory_Create;
    case Inventory_History_View;
    case Inventory_History_Create;
    case Inventory_History_Edit;
    case Inventory_Accessory_View;
    case Inventory_Accessory_Create;
    case Inventory_Accessory_Edit;
    case Inventory_Purchase_View;
    case Inventory_Purchase_Edit;

    case Location_List;
    case Location_View;
    case Location_Edit;
    case Location_Transfer;

    case Stock_List;
    case Stock_Create;
    case Stock_View;
    case Stock_Edit;
    case Stock_Delete;
    case Stock_RequestCounting;
    case Stock_History_View;
    case Stock_History_Add;
    case Stock_History_Remove;
    case Stock_History_Count;
    case Stock_History_Edit;


    case Report_List;

    case Renderer_List;
    case Renderer_View;

    case Process_List;
    case Process_Run;

    case Peripheral_List;
    case Peripheral_Scale_Use;
    case Peripheral_Printer_Use;

    case SpecificationPart_List;

    case ProductionPart_List;

    case WorkOrder_List;
    case WorkOrder_View;
    case WorkOrder_Create;
    case WorkOrder_Edit;

    case Project_List;
    case Project_View;

    case Assembly_List;
    case Assembly_View;
    case Assembly_Create;

    case Assembly_Unit_View;
    case Assembly_Unit_Edit;
    case Assembly_Unit_Create;

    case BillOfMaterial_List;
    case BillOfMaterial_View;
    case BillOfMaterial_Create;

    case Document_List;
    case Document_View;
    case Document_Ingest_List;
    case Document_Ingest_Upload;
    case Document_Ingest_Download;
    case Document_Ingest_Delete;
    case Document_Ingest_Save;
    case Document_Attach_Edit;

    case Metrology_TestSystem_List;
    case Metrology_TestSystem_View;
    case Metrology_TestSystem_Create;

}

class user
{
    public int $id = 0;
    public string $name;
    public string $initials;
    public userRoles $rights;
    public userSettings $settings;

    function __construct()
    {
        $this->rights = new userRoles();
        $this->settings = new userSettings();
    }
}
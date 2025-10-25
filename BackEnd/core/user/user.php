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

    case PurchaseOrder_List;

    case Inventory_List;

    case Location_List;

    case Stock_List;
    case Stock_View;

    case Report_List;

    case Renderer_View;

    case Process_List;
    case Process_Run;

    case Peripheral_List;

    case SpecificationPart_List;

    case ProductionPart_List;

    case WorkOrder_List;

    case Project_List;

    case Assembly_List;

    case BillOfMaterial_List;

    case Document_List;
    case Document_View;
    case Document_Ingest_List;
    case Document_Ingest_Upload;
    case Document_Ingest_Download;
    case Document_Ingest_Delete;
    case Document_Ingest_Save;
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
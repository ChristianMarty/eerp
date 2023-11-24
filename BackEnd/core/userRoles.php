<?php
//*************************************************************************************************
// FileName : userRoles.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************


class userRoles
{
    static function allRoles():stdClass
    {
        return json_decode(self::$fullAccessRoles);
    }

    static function allRolesArray():array
    {
        return json_decode(self::$fullAccessRoles, true);
    }

    static function devModeRoles():stdClass
    {
        return json_decode(self::$fullAccessRoles);
    }

    static private string $fullAccessRoles = <<< ROLES
    { 
        "assembly":{"view": true, "create": true, "unit" : {"add": true, "history" : {"add": true, "edit": true}}},
        "inventory":{"print": true,"create": true, "history" : {"add": true, "edit": true}, "accessory": {"add": true, "edit": true}, "purchase": {"edit": true}},
        "metrology":{"view": true, "create": true},
        "purchasing":{"create": true, "edit": true, "confirm": true},
        "vendor":{"view": true, "create": true, "edit": true},
        "supplierPart":{"create": true},
        "process":{"view": true, "run": true},
        "document":{"view": true, "upload": true, "create": true, "ingest": true},
        "manufacturerPartSeries":{"create": true,"edit": true},
        "manufacturerPart":{"create": true,"edit": true},
        "manufacturerPartNumber":{"create": true,"edit": true},
        "productionPart":{"create": true,"edit": true},
        "specificationPart":{"view": true, "create": true, "edit": true},
        "stock":{"create": true, "add": true, "remove":true, "count":true, "delete":true}, 
        "location":{"transfer":true, "bulkTransfer":true, "print": true, "edit": true},
        "finance":{"view":true, "costCenter":true},
        "bom":{"print":true},
        "billOfMaterial":{"view": true, "create": true},
        "workOrder":{"create": true, "edit": true},
        "label":{"view": true}
    }
    ROLES;
}
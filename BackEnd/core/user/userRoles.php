<?php
//*************************************************************************************************
// FileName : userRoles.php
// FilePath : /core/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace userRoles {

//*** Important! Read Me! *************************************************************************
// userRoles follow a functional naming convention!
// The class name and variable names must match the JSON data.
// The JSON path equals the class name where levels in the JSON are denoted by '_'
// e.g. assembly_unit_history is "assembly":{"unit" : {"history" : {}}}
//*************************************************************************************************

// error
    class error {
        public bool $php = false;
    }

// assembly
    class assembly_unit_history {
        public bool $add= false;
        public bool $edit = false;
    }
    class assembly_unit {
        public bool $add = false;
        public assembly_unit_history $history;

        function __construct()
        {
            $this->history = new assembly_unit_history();
        }
    }
    class assembly {
        public bool $view = false;
        public bool $create = false;
        public assembly_unit $unit;

        function __construct()
        {
            $this->unit = new assembly_unit();
        }
    }

// inventory
    class inventory_history {
        public bool $add= false;
        public bool $edit = false;
    }
    class inventory_accessory {
        public bool $add= false;
        public bool $edit = false;
    }
    class inventory_purchase {
        public bool $view= false;
        public bool $edit = false;
    }
    class inventory {
        public bool $view = false;
        public bool $create = false;
        public bool $print = false;
        public inventory_history $history;
        public inventory_accessory $accessory;
        public inventory_purchase $purchase;

        function __construct()
        {
            $this->history = new inventory_history();
            $this->accessory = new inventory_accessory();
            $this->purchase = new inventory_purchase();
        }
    }

// metrology
    class metrology_testSystem {
        public bool $view = false;
        public bool $create = false;
    }
    class metrology {
        public bool $view = false;
        public metrology_testSystem $testSystem ;

        function __construct()
        {
            $this->testSystem = new metrology_testSystem();
        }
    }

// purchasing
    class purchasing {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
        public bool $confirm= false;
    }

// vendor
    class vendor {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// supplierPart
    class supplierPart {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// project
    class project {
        public bool $view = false;
    }

// document
    class document {
        public bool $view = false;
        public bool $create = false;
        public bool $upload = false;
        public bool $ingest = false;
    }

// manufacturerPartSeries
    class manufacturerPartSeries {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// manufacturerPart
    class manufacturerPart {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// manufacturerPartNumber
    class manufacturerPartNumber {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// productionPart
    class productionPart {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// specificationPart
    class specificationPart {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// stock
    class stock {
        public bool $view = false;
        public bool $create = false;
        public bool $add = false;
        public bool $remove = false;
        public bool $count = false;
        public bool $delete = false;
        public bool $countingRequest = false;
    }

// location
    class location {
        public bool $view = false;
        public bool $transfer = false;
        public bool $bulkTransfer = false;
        public bool $print = false;
        public bool $edit = false;
    }

// finance
    class finance {
        public bool $view = false;
        public bool $costCenter = false;
    }

// billOfMaterial
    class billOfMaterial {
        public bool $view = false;
        public bool $create = false;
        public bool $print = false;
    }

// workOrder
    class workOrder {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

// process
    class process {
        public bool $view = false;
        public bool $run = false;
    }

// report
    class report {
        public bool $view = false;
        public bool $run = false;
    }

// renderer
    class renderer {
        public bool $view = false;
        public bool $create = false;
        public bool $edit = false;
    }

    class rights {
        public error $error;
        public assembly $assembly;
        public inventory $inventory;
        public metrology $metrology;
        public purchasing $purchasing;
        public vendor $vendor;
        public supplierPart $supplierPart;
        public project $project;
        public document $document;
        public manufacturerPartSeries $manufacturerPartSeries;
        public manufacturerPart $manufacturerPart;
        public manufacturerPartNumber $manufacturerPartNumber;
        public productionPart $productionPart;
        public specificationPart $specificationPart;
        public stock $stock;
        public location $location;
        public finance $finance;
        public billOfMaterial $billOfMaterial;
        public workOrder $workOrder;
        public process $process;
        public report $report;
        public renderer $renderer;

        function __construct()
        {
            // initialize all members to default value
            foreach (get_class_vars(get_class($this)) as $key => $value) {
                $className = __NAMESPACE__ . "\\" . $key;
                $this->{$key} = new $className();
            }
        }
    }
}

namespace {

    class userRoles
    {
        public userRoles\rights $rights;

        function __construct(string|null  $settingsJson = null)
        {
            $this->rights = new userRoles\rights();
            $this->decode($settingsJson);
        }

        private function import($input, object &$parent):void
        {
            $reflect = new ReflectionClass($parent);
            $class = new ($reflect->getName())();
            $className = $reflect->getShortName();

            $path = explode('_', $className);

            $data = $input;
            foreach($path as $item) {
                $data = $data->{$item} ?? null;
            }

            if($data){
                foreach ($data as $key => $value){
                    if(!is_object($value) && property_exists($class, $key)){
                        $class->{$key} = $value;
                    }
                }
            }
            $parent = $class;
        }

        public function decode(string|null $rolesJson):void
        {
            if($rolesJson === null) return;

            $data = json_decode($rolesJson);

            $this->import($data,  $this->rights->error);

            $this->import($data,  $this->rights->assembly);
            $this->import($data,  $this->rights->assembly->unit);
            $this->import($data,  $this->rights->assembly->unit->history);

            $this->import($data,  $this->rights->inventory);
            $this->import($data,  $this->rights->inventory->history);
            $this->import($data,  $this->rights->inventory->accessory);
            $this->import($data,  $this->rights->inventory->purchase);

            $this->import($data,  $this->rights->metrology);
            $this->import($data,  $this->rights->metrology->testSystem);

            $this->import($data,  $this->rights->purchasing);

            $this->import($data,  $this->rights->project);

            $this->import($data,  $this->rights->document);

            $this->import($data,  $this->rights->vendor);

            $this->import($data,  $this->rights->supplierPart);

            $this->import($data,  $this->rights->manufacturerPartSeries);

            $this->import($data,  $this->rights->manufacturerPart);

            $this->import($data,  $this->rights->manufacturerPartNumber);

            $this->import($data,  $this->rights->productionPart);

            $this->import($data,  $this->rights->specificationPart);

            $this->import($data,  $this->rights->stock);

            $this->import($data,  $this->rights->location);

            $this->import($data,  $this->rights->finance);

            $this->import($data,  $this->rights->billOfMaterial);

            $this->import($data,  $this->rights->workOrder);

            $this->import($data,  $this->rights->process);

            $this->import($data,  $this->rights->report);

            $this->import($data,  $this->rights->renderer);
        }

        public function encode() :string|false
        {
            return json_encode($this->rights);
        }
    }
}
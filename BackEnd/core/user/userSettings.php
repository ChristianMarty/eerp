<?php
//*************************************************************************************************
// FileName : userSettings.php
// FilePath : /core/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace userSettings {

//*** Important! Read Me! *************************************************************************
// userSettings follow a functional naming convention!
// The class name and variable names must match the JSON data.
// The JSON path equals the class name where levels in the JSON are denoted by '_'
// e.g. default_stock_renderer is "default":{"stock" : {"renderer" : {}}}
//*************************************************************************************************


// stock
    class default_stock_renderer_receipt {
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_stock_renderer_history {
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_stock_renderer_item {
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_stock_renderer {
        public \userSettings\default_stock_renderer_receipt $Receipt;
        public \userSettings\default_stock_renderer_history $History;
        public \userSettings\default_stock_renderer_item $Item;

        function __construct()
        {
            $this->Receipt = new default_stock_renderer_receipt();
            $this->History = new default_stock_renderer_history();
            $this->Item = new default_stock_renderer_item();
        }
    }

    class default_stock_measurement_countingScale {
        public int $PeripheralId = 0;
    }
    class default_stock_measurement {
        public \userSettings\default_stock_measurement_countingScale $CountingScale;

        function __construct()
        {
            $this->CountingScale = new default_stock_measurement_countingScale();
        }
    }
    class default_stock {
        public \userSettings\default_stock_renderer $Renderer;
        public \userSettings\default_stock_measurement $Measurement;

        function __construct()
        {
            $this->Renderer = new default_stock_renderer();
            $this->Measurement = new default_stock_measurement();
        }
    }

// purchaseOrder
    class default_purchaseOrder
    {
        public int $UnitOfMeasurementId = 0;
        public int $ValueAddedTaxId = 0;
    }

// assembly
    class default_assembly_renderer_item{
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_assembly_renderer_history{
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_assembly_renderer
    {
        public \userSettings\default_assembly_renderer_item $Item;
        public \userSettings\default_assembly_renderer_history $History;

        function __construct()
        {
            $this->Item = new default_assembly_renderer_item();
            $this->History = new default_assembly_renderer_history();
        }
    }
    class default_assembly
    {
        public \userSettings\default_assembly_renderer $Renderer;

        function __construct()
        {
            $this->Renderer = new default_assembly_renderer();
        }
    }

// location
    class default_location_renderer_inventoryList{
        public int $PeripheralId = 0;
        public int $RendererId = 0;
    }
    class default_location_renderer
    {
        public \userSettings\default_location_renderer_inventoryList $InventoryList;

        function __construct()
        {
            $this->InventoryList = new default_location_renderer_inventoryList();
        }
    }
    class default_location
    {
        public \userSettings\default_location_renderer $Renderer;

        function __construct()
        {
            $this->Renderer = new default_location_renderer();
        }
    }

    class defaultSettings
    {
        public default_stock $Stock;
        public default_purchaseOrder $PurchaseOrder;
        public default_location $Location;
        public default_assembly $Assembly;

        function __construct()
        {
            $this->Stock = new default_stock();
            $this->PurchaseOrder = new default_purchaseOrder();
            $this->Location = new default_location();
            $this->Assembly = new default_assembly();
        }
    }

    class settings
    {
        public defaultSettings $Default;

        function __construct()
        {
            $this->Default = new defaultSettings();
        }
    }
}

namespace {

    class userSettings
    {
        public userSettings\settings $settings;

        function __construct(string|null  $settingsJson = null)
        {
            $this->settings = new userSettings\settings();
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
                $item = ucfirst($item);
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

        public function decode(string|null $settingsJson):void
        {
            if($settingsJson === null) return;

            $data = json_decode($settingsJson);

            $this->import($data,  $this->settings->Default->Stock->Renderer->Item);
            $this->import($data,  $this->settings->Default->Stock->Renderer->History);
            $this->import($data,  $this->settings->Default->Stock->Renderer->Receipt);

            $this->import($data,  $this->settings->Default->Stock->Measurement->CountingScale);

            $this->import($data,  $this->settings->Default->Assembly->Renderer->Item);
            $this->import($data,  $this->settings->Default->Assembly->Renderer->History);

            $this->import($data,  $this->settings->Default->PurchaseOrder);

            $this->import($data,  $this->settings->Default->Location->Renderer->InventoryList);
        }

        public function encode() :string|false
        {
            return json_encode($this->settings);
        }
    }
}


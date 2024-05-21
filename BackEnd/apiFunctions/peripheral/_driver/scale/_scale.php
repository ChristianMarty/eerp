<?php
//*************************************************************************************************
// FileName : _scale.php
// FilePath : apiFunctions/peripheral/_driver/scale
// Author   : Christian Marty
// Date		: 13.01.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

enum ScaleError {
    CASE NoError;
    CASE DriverMissing; // Driver is not set in DB
    CASE DriverNotFound; // Driver is not in _driver folder
    CASE PeripheralNotFound;
    CASE PeripheralConnectionError;
    CASE ProtocolFramingError;
    CASE ProtocolDataError;
    CASE DriverUninitialized;
}

abstract class ScaleBase
{
    protected string $ip;
    protected int $port;

    public function __construct(string $ip, int $port)
    {
        $this->ip = $ip;
        $this->port = $port;
    }

    public function read() : float|null|string{
        return null;
    }

    public function getError(): ScaleError{
        return ScaleError::DriverUninitialized;
    }
}

class Scale
{
    private ScaleBase $scale;
    private ScaleError $error = ScaleError::NoError;

    public function __construct(int $scaleId = null)
    {
        global $database;
        $query = <<<QUERY
        SELECT 
            *
        FROM peripheral
        WHERE DeviceType = 'scale' AND Id = '$scaleId'
        LIMIT 1;
        QUERY;

        $return = $database->query($query);

        if(count($return) === 0){
            $this->error =  ScaleError::PeripheralNotFound;
            return;
        }
        $device = $return[0];

        if($device->Driver === null OR trim($device->Driver) === ""){
            $this->error =  ScaleError::DriverMissing;
            return;
        }

        $driverName = trim($device->Driver);

        $filePath = __DIR__.'/'.$driverName.'.php';
        if (!file_exists($filePath)){
            $this->error =  ScaleError::DriverNotFound;
            return;
        }

        require_once($filePath);
        $this->scale = new $driverName($device->Ip, intval($device->Port));
    }

    public function read(): float|null
    {
        $reading =  $this->scale->read();
        if($reading === null){
            $this->error = $this->scale->getError();
            return null;
        }
        return $reading;
    }

    public function hasError(): bool
    {
        return match ($this->error) {
            ScaleError::NoError => false,
            default => true
        };
    }

    public function  gerError(): ScaleError
    {
        return $this->error;
    }

    public function  getErrorString(): string
    {
        return match ($this->error) {
            ScaleError::NoError => "No Error",
            ScaleError::DriverMissing => "Driver name is not set in database",
            ScaleError::DriverNotFound => "Driver is not in _driver folder",
            ScaleError::PeripheralNotFound => "Scale peripheral id not found in the database",
            ScaleError::PeripheralConnectionError => $this->scale->getSocketErrorString(),
            ScaleError::ProtocolFramingError => "Protocol framing error",
            ScaleError::ProtocolDataError => "Protocol data error",
            ScaleError::DriverUninitialized => "Driver uninitialized"
        };
    }
}

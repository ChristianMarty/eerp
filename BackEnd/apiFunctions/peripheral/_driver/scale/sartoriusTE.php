<?php
//*************************************************************************************************
// FileName : sartoriusTE.php
// FilePath : apiFunctions/peripheral/_driver/scale
// Author   : Christian Marty
// Date		: 13.01.2024
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/_scale.php";

class SartoriusTE extends ScaleBase
{
    private ScaleError $error = ScaleError::NoError;
    private int $socketError = 0;

    public function read() : float|null
    {

        $data = $this->readFromScale();

        $data = $this->decodeReading($data);

        if($data === null)
        {
            return null;
        }else{
            return $data;
        }


    }

    public function getError(): ScaleError
    {
        return $this->error;
    }

    public function getSocketErrorString(): string
    {
        return socket_strerror($this->socketError);
    }

    private function decodeReading(string $input): float|null
    {
        $data = unpack('C*',$input);
        if(count($data) !== 22){
            $this->error = ScaleError::ProtocolFramingError;
            return null;
        }

        // Attention: unpack output starts at index 1 !!!
        if($data[21] != 0x0D OR $data[22] != 0x0A) {
            $this->error = ScaleError::ProtocolFramingError;
            return null;
        }

        $type = trim(substr($input,0,6));
        if($type =='N') {
            $signe = $input[6];
            $display = trim(substr($input,8,8));
            $unit = trim(substr($input,17,3));
            $value = floatval($display);

            if($signe === "+" or $signe === " "){

            }else if($signe === "-"){
                $value = $value*-1;
            }else{
                $this->error = ScaleError::ProtocolDataError;
                return null;
            }

            if($unit === "g"){

            }else if($unit === "kg"){
                $value = $value*1000;
            }else{
                $this->error = ScaleError::ProtocolDataError;
                return null;
            }

            return $value;

        }else{
            $this->error = ScaleError::ProtocolDataError;
            return null;
        }
    }

    private function readFromScale(): string|null
    {
        /* Create a TCP/IP socket. */
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($socket,SOL_SOCKET,SO_RCVTIMEO,array("sec"=>1,"usec"=>0));

        if ($socket === false) {
            $this->error = ScaleError::PeripheralConnectionError;
            $this->socketError = socket_last_error();
            return null;
        }

        $result = socket_connect($socket, $this->ip, $this->port);

        if ($result === false) {
            $this->error = ScaleError::PeripheralConnectionError;
            $this->socketError = socket_last_error();
            return null;
        }

        $in = "\x1BP\x0D\x0A"; // Trigger "Print" on scale

        socket_write($socket, $in, strlen($in));

        $rxBuffer = null;
        if (false === ($bytes = socket_recv($socket, $rxBuffer, 22, MSG_WAITALL))) {
            $this->error = ScaleError::PeripheralConnectionError;
            $this->socketError = socket_last_error();
            return null;
        }

        socket_close($socket);
        return $rxBuffer;
    }
}
<?php
//*************************************************************************************************
// FileName : _renderer.php
// FilePath : apiFunctions/renderer/_item
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
namespace renderer;

enum language
{
    case ZPL;
    case ESCPOS;
    case HTML;
}

abstract class renderer
{
    protected \renderer\language|null $method = null;

    static public function render(\stdClass|array $data, int|null $printerId = null) : string|null
    {
        return null;
    }

    static protected function printer(int|null $printerId = null): \stdClass|null
    {
        if($printerId===null) return null;

        global $database;
        $query = "SELECT * FROM peripheral WHERE Id ='$printerId' LIMIT 1;";
        $printer = $database->query($query);

        if(count($printer) === 0) return null;
        return $printer[0];
    }

}

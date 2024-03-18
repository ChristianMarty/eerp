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

    static public function render(\stdClass $input) : string|null
    {
        return null;
    }

}

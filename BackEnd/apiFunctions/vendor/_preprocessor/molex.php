<?php
//*************************************************************************************************
// FileName : molex.php
// FilePath : apiFunctions/vendor/_preprocessor
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/_partNumberPreprocessing.php";

class molex extends PartNumberPreprocessingBase
{
    static public function clean(string $input) : string
    {
        return preg_replace('/[^0-9.]/', '', $input); // Removes all non-numeric characters
    }

    static public function format(string $input) : string
    {
        $input = str_pad($input, 10, '0', STR_PAD_LEFT);
        return  substr($input, 0, 6) . "-" . substr($input, 6);
    }
}

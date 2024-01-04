<?php
//*************************************************************************************************
// FileName : distrelec.php
// FilePath : apiFunctions/vendor/_preprocessor
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
require_once __DIR__ . "/_partNumberPreprocessing.php";

class distrelec extends PartNumberPreprocessingBase
{
    static public function clean(string $input) : string
    {
        $str = preg_replace('/[^0-9.]/', '', $input); // Removes all non-numeric characters
        return ltrim($str, "0"); // remove leading zeros
    }

    static public function format(string $input) : string
    {
        $input = str_pad($input, 8, '0', STR_PAD_LEFT);
        return  substr($input, 0, 3) . "-" . substr($input, 3, 2) . "-" . substr($input, 5,3);
    }
}

<?php
//*************************************************************************************************
// FileName : _part.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 01.07.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

function manufacturerPart_numberWithoutParameters(string|null $input): string|null
{
    if($input == null) return null;

    return preg_replace("/(\{[^{]+\})/","{}",$input);
}

<?php
//*************************************************************************************************
// FileName : userAuthentication.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 27.01.2025
// Website  : www.christian-marty.ch
//*************************************************************************************************

namespace logging;

enum type
{
    case Warning;
    case Error;
}

class message
{
    static function invalideParameters(string $message)
    {

    }

    static function addWarning(string $message)
    {
        error_log("Warning: ".$message);
    }
    static function addError(string $message)
    {
        error_log("Error: ".$message);
    }
}
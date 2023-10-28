<?php
//*************************************************************************************************
// FileName : weekNumber.php
// FilePath : apiFunctions/various
// Author   : Christian Marty
// Date		: 23.10.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

if($api->isGet())
{
    $output = new stdClass();
    $output->WeekNumber = date("W");

    $api->returnData($output);
}

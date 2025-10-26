<?php
//*************************************************************************************************
// FileName : locationCache.php
// FilePath : apiFunctions/process/cache/
// Author   : Christian Marty
// Date		: 12.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************<?php

require_once __DIR__ . "/../../../config.php";
require_once __DIR__ . "/../../location/_location.php";

$title = "Update Location Cache";
$description = "Update Location Cache ";
$parameter = null;


$locations = location_getLocations();

echo "<pre>";

foreach($locations as $location)
{
    var_dump(location_buildLocation($locations,$location['Id']));
}

echo "</br>";

foreach($locations as $location)
{
    var_dump(location_buildLocationPath($locations,$location['Id'],100));
}

//var_dump(location_buildTree($locations,0));

echo "</pre>";


//var_dump(apcu_cache_info());


?>
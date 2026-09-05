<?php
//*************************************************************************************************
// FileName : _renderer.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 05.09.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace Renderer;
require_once __DIR__ ."/../core/numbering.php";

function parseLocationList(string|null $locationList) : array|null
{
    if($locationList === null) return null;

    $locationNumbers = explode(",", $locationList);
    if(!count($locationNumbers)){
        return null;
    }

    foreach ($locationNumbers as &$item) {
        $item = \Numbering\parser(\Numbering\Category::Location, $item);
    }

    $locationNumbers = array_filter($locationNumbers);
    if(!count($locationNumbers)){
        return null;
    }

    return $locationNumbers;
}
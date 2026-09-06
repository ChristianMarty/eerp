<?php
//*************************************************************************************************
// FileName : _class.php
// FilePath : apiFunctions/part/
// Author   : Christian Marty
// Date		: 05.09.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

namespace PartClass;

function getPath(int|null $classId) : string | \Error\Data
{
    if($classId === null) return "";

    global $database;

    $query = <<<STR
        SELECT 
            Id,
            ParentId,
            Name
        FROM manufacturerPart_class
    STR;
    $result = $database->query($query);
    if(\Error\checkError($result)){
        return $result;
    }

    $map = [];
    foreach ($result as $r)
    {
        $map[$r->Id] = $r;
    }

    $output = [];
    $nextId = $classId;
    $i = 0;
    while($nextId != null){
        $output[] = $map[$nextId]->Name;
        $nextId = $map[$nextId]->ParentId;
        $i++;
        if($i>100){
            return \Error\generic("Class path too deep");
        }
    }

    return implode(" → ",array_reverse($output));
}

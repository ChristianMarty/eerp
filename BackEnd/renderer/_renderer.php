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
use Numbering\Category;

require_once __DIR__ . "/../core/numbering.php";

class Parameter
{
    public int $offset = 0;
    public array $items;

    function isEmpty(): bool
    {
        return count($this->items) == 0;
    }

    function sqlInString(): string
    {
        return implode(", ", $this->items);
    }
}

function parseLocationParameter(object|null $parameter): Parameter
{
    $output = new Parameter();
    $output->items = parseParameterList($parameter->LocationNumber ?? null, \Numbering\Category::Location);
    $output->offset = intval($parameter->Offset ?? 0);

    return $output;
}

function parseInventoryParameter(object|null $parameter): Parameter
{
    $output = new Parameter();
    $output->items = parseParameterList($parameter->InventoryNumber ?? null, \Numbering\Category::Inventory);
    $output->offset = intval($parameter->Offset ?? 0);

    return $output;
}

function parseParameterList(string|null $locationList, \Numbering\Category $category): array
{
    if ($locationList === null) return [];

    $locationNumbers = explode(",", $locationList);
    if (!count($locationNumbers)) {
        return [];
    }

    foreach ($locationNumbers as &$item) {
        $item = \Numbering\parser($category, $item);
    }

    $locationNumbers = array_filter($locationNumbers);
    if (!count($locationNumbers)) {
        return [];
    }

    return $locationNumbers;
}



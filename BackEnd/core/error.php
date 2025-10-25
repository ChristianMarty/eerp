<?php
//*************************************************************************************************
// FileName : error.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 09.09.2025
// Website  : www.christian-marty.ch
//*************************************************************************************************

namespace Error;
require_once __DIR__ . "/user/userAuthentication.php";

enum Type
{
    case Undefined;
    case Generic;
    case ItemNotFound;
    case Database;
    case PostDataMissing;
    case Parameter;
    case ParameterMissing;
}

function trace(): string
{
    global $user;
    if($user->roles()["error"]->apiErrorTrace === false) return "";

    $bt = debug_backtrace();
    array_shift($bt);
    $caller = array_shift($bt);
    return $caller['file']." - ".$caller['line']." : ";
}

function database(string $error): Data
{
    return new Data(Type::Database, trace().$error);
}

function generic(string $error): Data
{
    return new Data(Type::Generic, trace().$error);
}

function itemNotFound(string $itemCode): Data
{
    return new Data(Type::Generic, trace().$itemCode." not found");
}

function parameter(string $error): Data
{
    return new Data(Type::Parameter, trace().$error);
}

function parameterMissing(string $parameterName): Data
{
    return new Data(Type::ParameterMissing, trace().$parameterName." is not specified");
}

function postDataMissing(): Data
{
    return new Data(Type::PostDataMissing, trace()."Parameter Error: POST field must not be empty");
}

class Data
{
    public string|null $error = null;
    public Type $type = Type::Undefined;

    public function __construct(Type $type, string $message)
    {
        $this->error = $message;
        $this->type = $type;
    }
}
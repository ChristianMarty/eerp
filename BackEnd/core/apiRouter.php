<?php
//*************************************************************************************************
// FileName : apiRouter.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/user/userAuthentication.php";

enum apiMethod
{
    case GET;
    case POST;
    case PATCH;
    case DELETE;
    case OPTIONS;
}

enum entrypoint{
    case API;
    case DATA;
    case RENDERER;
    case REPORT;
    case PROCESS;
}

class apiRouter
{
    private apiMethod $method;
    private array|null $options = null;
    private string|null $runPath = null;

    private bool $hasGet = false;
    private bool $hasPost = false;
    private bool $hasPatch = false;
    private bool $hasDelete = false;


    function __construct(userAuthentication $user, entrypoint $entrypoint, string $path, string $methodString)
    {
        global $user;

        $this->method = match ($methodString) {
            'GET' => apiMethod::GET,
            'POST' => apiMethod::POST,
            'PATCH' => apiMethod::PATCH,
            'DELETE' => apiMethod::DELETE,
            'OPTIONS' => apiMethod::OPTIONS,
        };

        $entryPath = match ($entrypoint) {
            entrypoint::API => 'api.php/',
            entrypoint::DATA=> 'data.php/',
            entrypoint::RENDERER => 'renderer.php/',
            entrypoint::REPORT => 'report.php/',
            entrypoint::PROCESS => 'process.php/',
        };

        global $serverDataPath;
        $filePath = match ($entrypoint) {
            entrypoint::API => __DIR__.'/../apiFunctions/',
            entrypoint::DATA=> $serverDataPath.'/',
            entrypoint::RENDERER => __DIR__.'/../renderer/',
            entrypoint::REPORT => __DIR__.'/../report/',
            entrypoint::PROCESS => __DIR__.'/../process/',
        };

        $requestParts = explode($entryPath, $path);
        if(count($requestParts) < 2){
            $this->returnNotFoundError();
        }

        $request = explode('?', $requestParts[1])[0];
        $filePath .= $request;
        $filePath = rtrim($filePath, "/");
        $filePath .= ".php";

        if (!file_exists($filePath)) {
            $this->returnNotFoundError();
        }

        //Files starting with "_" are hidden
        $tmp = explode('/', $filePath);
        if (str_starts_with(end($tmp), "_")) {
            $this->returnNotFoundError();
        }
        /*
        if($this->isOptions()) {
            header("Allow: ".$this->optionsString());
            exit;
        }*/

        if ($request == "user/login")
        {
            $this->runPath = $filePath;
        } 
		else if ($user->loggedIn())
		{
            $idempotencyToken = null;
            $headers = array_change_key_case(getallheaders(),CASE_LOWER);
            if (isset($headers['idempotency-key'])) {
                $idempotencyToken = $headers['idempotency-key'];
            }
            if ($this->method === apiMethod::POST && $filePath != __DIR__.'/../apiFunctions/document/ingest/upload.php' && $filePath != __DIR__.'/../apiFunctions/purchasing/item/upload.php')
			{
                if ($idempotencyToken !== $_SESSION['idempotency']) {
                    $this->returnError("Idempotency key expired.");
                }
                $_SESSION['idempotency'] = self::generateIdempotenceToken();
            }

            $this->runPath = $filePath;
        } 
		else 
		{
            $this->returnUnauthorizedError("User Session Invalid. Please Log In.");
        }
    }

    function getRunPath():string|null
    {
        return $this->runPath;
    }

    function optionsString():string
    {
        $output = "";
        if($this->hasGet) $output .= "GET,";
        if($this->hasPost) $output .= "POST,";
        if($this->hasPatch) $output .= "PATCH,";
        if($this->hasDelete) $output .= "DELETE,";
        $output .= "OPTIONS";
        return $output;
    }

    function getPostData():stdClass|null
    {
        $data = json_decode(file_get_contents('php://input'));
        if($data === null) return null;

        foreach($data as &$item)
        {
            if(!is_string($item)) continue;
            $str = strtolower($item);
            if($str === "true" || $str === "false")
            {
                $item = filter_var($str, FILTER_VALIDATE_BOOLEAN);
            }
        }
        return $data;
    }

    function getGetData():stdClass|null
    {
        $data = $_GET;
        if($data === null) return null;

        foreach($data as &$item)
        {
            $str = strtolower($item);
            if($str === "true" || $str === "false")
            {
                $item = filter_var($str, FILTER_VALIDATE_BOOLEAN);
            }else if($str === "null"){
                $item = null;
            }
        }
        return (object)$data;
    }

    function isGet(Permission|null $permissions = null):bool
    {
        $this->hasGet = true;
        if($this->method === apiMethod::GET){
            $this->checkPermission($permissions);
            return true;
        }
        else return false;
    }

    function isPost(Permission|null $permissions = null):bool
    {
        $this->hasPost = true;
        if($this->method === apiMethod::POST){
            $this->checkPermission($permissions);
            return true;
        }
        else return false;
    }

    function isPatch(Permission|null $permissions = null):bool
    {
        $this->hasPatch = true;
        if($this->method === apiMethod::PATCH){
            $this->checkPermission($permissions);
            return true;
        }
        else return false;
    }

    function isDelete(Permission|null $permissions = null):bool
    {
        $this->hasDelete = true;
        if($this->method === apiMethod::DELETE){
            $this->checkPermission($permissions);
            return true;
        }
        else return false;
    }

    function isOptions():bool
    {
        if($this->method === apiMethod::OPTIONS) return true;
        else return false;
    }

    #[NoReturn] function returnData(array|stdClass|null|string|\Error\Data $data, string|null $errorMessage = null): void
    {
        if($data instanceof \Error\Data){
            $this->returnError($data->error);
        }

        header("Content-Type:application/json; charset=UTF-8");

        global $user;

        if(!isset($_SESSION['idempotency'])) $_SESSION['idempotency'] = self::generateIdempotenceToken();
        $loginState = $user->loggedIn();

        $response['data'] = $data;
        $response['error'] = $errorMessage;
        $response['authenticated'] = $loginState;
        $response['idempotency'] = $_SESSION['idempotency'];

        $json_response = json_encode($response);
        if(!$json_response) {
            $errorResponse['error'] = "JSON encoding error: ".json_last_error_msg();

            echo json_encode($errorResponse);
            exit;
        }

        echo $json_response;
        exit;
    }

    #[NoReturn] function returnCSV(string $data, string $name): void
    {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$name.'"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        //header('Content-Length: ' . filesize($csvFile));
        echo $data;
        exit;
    }

    #[NoReturn] function returnEmpty(): void
    {
        $this->returnData(null);
    }

    #[NoReturn] function returnParameterMissingError(string $parameterName): void
    {
        $bt = debug_backtrace();
        $caller = array_shift($bt);

        $meta = $caller['file']." - ".$caller['line'];

        $this->returnData(null, $meta." - ".$parameterName." is not specified");
    }

    #[NoReturn] function returnParameterPostDataMissing(): void
    {
        $this->returnData(null, "Parameter Error: POST field must not be empty");
    }

    #[NoReturn] function returnParameterError(string $parameterName): void
    {
        $this->returnData(null, "Parameter Error: ".$parameterName);
    }

    #[NoReturn] function returnError(string|null $errorMessage = null): void
    {
        if($errorMessage === null) $errorMessage = "Unknown error in: ".$this->runPath;
        $this->returnData(null, $errorMessage);
    }

    #[NoReturn] function returnNotFoundError(string $message = ""):void
    {
        http_response_code(404);
        $this->returnError("Error 404 - Not Found:  ".$message);
    }

    #[NoReturn] function returnUnauthorizedError(string $message = ""):void
    {
        http_response_code(401);
        $this->returnError("Error 401 - Unauthorized: ".$message);
    }

    #[NoReturn] function returnMethodNotAllowedError(string $message = ""):void
    {
        http_response_code(405);
        $this->returnError("Error 405 - Method Not Allowed: ".$message);
    }

    static private function generateIdempotenceToken(): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < 20; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function checkPermission(\Permission|null $permissions = null): void
    {
        if($permissions === null) return;

        global $user;
        if(!$user->checkPermission($permissions)){
            $this->returnData(\Error\permission($permissions));
        }
    }
}
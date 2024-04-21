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
require_once __DIR__ . "/userAuthentication.php";

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
    private userAuthentication $user;

    private bool $hasGet = false;
    private bool $hasPost = false;
    private bool $hasPatch = false;
    private bool $hasDelete = false;


    function __construct(userAuthentication $user, entrypoint $entrypoint, string $path, string $methodString)
    {
        $this->user = $user;

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

        $requestParts = explode($entryPath, $path);

        global $serverDataPath;
        $filePath = match ($entrypoint) {
            entrypoint::API => __DIR__.'/../apiFunctions/',
            entrypoint::DATA=> $serverDataPath.'/',
            entrypoint::RENDERER => __DIR__.'/../renderer/',
            entrypoint::REPORT => __DIR__.'/../report/',
            entrypoint::PROCESS => __DIR__.'/../apiFunctions/process/',
        };

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

        if ($request == "user/login" || $request == "user/logout")
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
            global $devMode;
            if ($this->method === apiMethod::POST && !$devMode && $filePath != __DIR__.'/../apiFunctions/document/ingest/upload.php' && $filePath != __DIR__.'/../apiFunctions/purchasing/item/upload.php')
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

    function isGet(string|null $permissions = null):bool
    {
        $this->hasGet = true;
        if($this->method === apiMethod::GET) return true;
        else return false;
    }

    function isPost(string|null $permissions = null):bool
    {
        $this->hasPost = true;
        if($this->method === apiMethod::POST) return true;
        else return false;
    }

    function isPatch(string|null $permissions = null):bool
    {
        $this->hasPatch = true;
        if($this->method === apiMethod::PATCH) return true;
        else return false;
    }

    function isDelete(string|null $permissions = null):bool
    {
        $this->hasDelete = true;
        if($this->method === apiMethod::DELETE) return true;
        else return false;
    }

    function isOptions():bool
    {
        if($this->method === apiMethod::OPTIONS) return true;
        else return false;
    }

    #[NoReturn] function returnData(array|stdClass|null|string $data, string|null $errorMessage = null): void
    {
        header("Content-Type:application/json; charset=UTF-8");

        global $devMode;
        global $user;

        if($devMode)
        {
            $loginState = true;
            $_SESSION['idempotency'] = "abcdefghijklmnopqrstuvwxyz";
        }
        else
        {
            if(!isset($_SESSION['idempotency'])) $_SESSION['idempotency'] = self::generateIdempotenceToken();
            $loginState = $user->loggedIn();
        }

        $response['data'] = $data;
        $response['error'] = $errorMessage;
        $response['loggedin'] = $loginState; // deprecated -> replaced by 'authenticated'
        $response['authenticated'] = $loginState;
        $response['idempotency'] = $_SESSION['idempotency'];

        $json_response = json_encode($response);
        if(!$json_response)
        {
            $errorResponse['error'] = "JSON encoding error";
            $errorResponse['loggedin'] = $_SESSION['authenticated'];

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
        $this->returnData(null, $parameterName." is not specified");
    }

    #[NoReturn] function returnParameterError(string $parameterName): void
    {
        $this->returnData(null, $parameterName." is invalid");
    }

    #[NoReturn] function returnError(string|null $errorMessage = null): void
    {
        if($errorMessage === null) $errorMessage = "Unknown error in: ".$this->runPath;
        $this->returnData(null, $errorMessage);
    }

    #[NoReturn] function returnNotFoundError(string $message = ""):void
    {
        http_response_code(404);
        $this->returnError("Error 404 - Not Found ".$message);
    }

    #[NoReturn] function returnUnauthorizedError(string $message = ""):void
    {
        http_response_code(401);
        $this->returnError("Error 401 - Unauthorized".$message);
    }

    #[NoReturn] function returnMethodNotAllowedError(string $message = ""):void
    {
        http_response_code(405);
        $this->returnError("Error 405 - Method Not Allowed".$message);
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
}
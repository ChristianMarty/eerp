<?php
//*************************************************************************************************
// FileName : userAuthentication.php
// FilePath : /core
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/../config.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/userRoles.php";
require_once __DIR__ . "/userSettings.php";

class userAuthentication
{
    private string $ldapServer;
    private string $ldapBase;

    private stdClass $allRoles;

    private database|null $database = null;

    function __construct( database|null $database = null) {
        $this->database = $database;
        global $adServer;
        global $ldapBase;
        if(session_status() === PHP_SESSION_NONE) session_start();

        $this->ldapServer = $adServer;
        $this->ldapBase = $ldapBase;

        $this->allRoles = userRoles::allRoles();
    }

    function loginWithToken(string $username, #[\SensitiveParameter] string $token): bool
    {
        if($this->database === null)$this->database = new database();

        $username = $this->database->pdo()->quote($username);
        $token = $this->database->pdo()->quote($token);

        $query =  <<< QUERY
            SELECT * 
            FROM `user` 
            WHERE `UserId` = $username and `Token` =$token
            LIMIT 1;
        QUERY;

        $data = $this->database->pdo()->query($query);
        $result = $data->fetch();

        if(!$result) return false;

        self::getUserInfoFromDb($result->UserId);

        return $_SESSION['authenticated'];
    }

    function login(string $username, #[\SensitiveParameter] string $password ): bool
    {
        $ldap = ldap_connect($this->ldapServer);
        if(!$ldap) return false;

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $ldapDn =  "uid=".$username.",".$this->ldapBase ;

        $bind = @ldap_bind($ldap, $ldapDn, $password);

        if (!$bind){
            $_SESSION['authenticated'] = false;
            return false;
        }

        self::getUserInfoFromDb($username);
        return $_SESSION['authenticated'];
    }

    function logout(): void
    {
        $_SESSION = array(); // delete all session variables.

        // This is from www.php.net/manual/
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"],
                $params["domain"], $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    function loggedIn():bool
    {
        global $devMode;
        if($devMode) return true;

        if(!isset($_SESSION['authenticated'])) return false;
        return $_SESSION['authenticated'];
    }

    function showPhpErrors(): bool
    {
        global $devMode;
        if($devMode) return true;

        if(!isset($_SESSION['userRoles'])) return false;
        return $_SESSION['userRoles']->error->php ?? false;
    }

    function displayName():string
    {
        global $devMode;
        if($devMode) return "DevMode";

        if(isset($_SESSION["userName"])) return $_SESSION["userName"];
        else return "WTF";
    }

    function info(): array
    {
        global $devMode;
        $returnData=[];

        $returnData['introduction'] = "legacy -> remove"; // Todo: remove
        $returnData['avatar'] =""; // Todo: remove

        if(!$devMode)
        {
            $roles_array = [];
            $roles = $_SESSION['userRoles'];
            self::buildRolesForFrontend_recursive($roles, $roles_array, "");
            $returnData['name'] = $_SESSION["userName"];
            $returnData['roles'] = $roles_array;
            $returnData['settings'] = $_SESSION["userSettings"];
            $returnData['rolesJson'] = $roles;

        }
        else
        {
            $roles_array = [];
            $roles = userRoles::devModeRoles();
            self::buildRolesForFrontend_recursive($roles, $roles_array, "");
            $returnData['name'] = "DevMode";
            $returnData['roles'] = $roles_array;
            $returnData['settings'] = userSettings::devModeSettings();
            $returnData['rolesJson'] = $roles;

        }
        return $returnData;
    }

    function roles(): array
    {
        global $devMode;
        if($devMode) return userRoles::allRolesArray();
        else return $_SESSION['userRoles'];
    }

    private function getUserInfoFromDb(string $username) :void
    {
        if($this->database === null)$this->database = new database();

        $username = $this->database->pdo()->quote($username);

        $query = "SELECT * FROM user WHERE UserId = $username LIMIT 1";
        $data = $this->database->pdo()->query($query);
        $result = $data->fetch();

        if(!$result)
        {
            $_SESSION["authenticated"] = false;
            unset($_SESSION["userId"]);
            unset($_SESSION["userName"]);
            unset($_SESSION["userRoles"]);
            unset($_SESSION["userSettings"]);
            return;
        }
		
        $userid = $result->Id;
        $settings = json_decode($result->Settings,true);
        $userRolesTree = json_decode($result->Roles,true);

        $_SESSION["authenticated"] = true;
        $_SESSION["userId"] = $userid;
        $_SESSION["userName"] = $username;
        $_SESSION['userRoles'] = $userRolesTree;
        $_SESSION["userSettings"] = $settings;
    }

    private function buildRolesForFrontend_recursive($rolesObject, &$roleStringArray, $roleStringPart): string
    {
     
        $categoryStringPart = $roleStringPart;

        foreach($rolesObject as $key => $role)
        {
            if(is_object($role) || is_array($role))
            {
                $roleStringPart = self::buildRolesForFrontend_recursive($role,$roleStringArray,$categoryStringPart.$key.".");
            }
            else
            {
                if($role) $roleStringArray[] = $roleStringPart . $key;
            }
        }
        return $categoryStringPart;
    }


}

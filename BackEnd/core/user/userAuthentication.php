<?php
//*************************************************************************************************
// FileName : userAuthentication.php
// FilePath : /core/user/
// Author   : Christian Marty
// Date		: 23.10.2023
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/../../config.php";
require_once __DIR__ . "/../database.php";
require_once __DIR__ . "/user.php";


class userAuthentication
{
    private string $ldapServer;
    private string $ldapBase;

    public userSettings|null $settings = null;

    function __construct( database|null $database = null) {
        global $adServer;
        global $ldapBase;

        $this->ldapServer = $adServer;
        $this->ldapBase = $ldapBase;
        $this->settings = null;
    }

    function loginWithToken(string $username, #[\SensitiveParameter] string $token): bool
    {
        global $database;

        $username = $database->escape($username);
        $token = $database->escape($token);

        $query =  <<< QUERY
            SELECT * 
            FROM `user` 
            WHERE `UserId` = $username and `Token` =$token
            LIMIT 1;
        QUERY;

        $results = $database->query($query);

        if(!$results) return false;

        self::getUserInfoFromDb($results[0]->UserId);

        return $_SESSION['authenticated'];
    }

    function login(string $username, #[\SensitiveParameter] string $password ): string|bool
    {
        $ldap = ldap_connect($this->ldapServer);
        if(!$ldap){
            return "LDAP connection error";
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $ldapDn =  "uid=".$username.",".$this->ldapBase ;

        $bind = @ldap_bind($ldap, $ldapDn, $password);

        if (!$bind){
            $this->logout();
            return "Username or password wrong";
        }

        if(!self::getUserInfoFromDb($username)){
            $this->logout();
            return "Username not in user list";
        }

        return $this->loggedIn();
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
        return isset($_SESSION['user']);
    }

    function showPhpErrors(): bool
    {
        global $devMode;
        if($devMode) return true;

        return false;
    }

    function displayName():string
    {
        if(!isset($_SESSION['user'])){
            return "";
        }

        return $_SESSION['user']->name;
    }

    function userId():int
    {
        if(!isset($_SESSION['user'])){
            return 0;
        }

        return $_SESSION['user']->id;
    }

    function vatIdDefault(): int
    {
        if(!isset($_SESSION['user'])){
            return 0;
        }

        return $_SESSION['user']->settings->settings->Default->PurchaseOrder->ValueAddedTaxId;
    }

    function userData(): user|null
    {
        if(!isset($_SESSION['user'])) return null;
        else return $_SESSION['user'];
    }

    function info(): array
    {
        $user = $this->userData();

        if($user === null){
            return [];
        }

        $returnData=[];

        $returnData['settingsNew'] = $user->settings->encode();

        $roles_array = [];
        self::buildRolesForFrontend_recursive( $user->rights->rights, $roles_array, "");
        $returnData['name'] = $user->name;
        $returnData['roles'] = $roles_array;
        $returnData['settings'] = $user->settings->settings;
        $returnData['rolesJson'] = $user->rights->rights;

        return $returnData;
    }

    function roles(): array
    {
        $user = $this->userData();
        return (array)$user?->rights->rights;
    }

    private function getUserInfoFromDb(string $username) :bool
    {
        global $database;

        $username = $database->escape($username);

        $query =  <<< QUERY
            SELECT 
                `Id`,
                `UserId`,
                `Initials`,
                `Roles`,
                `Settings`
            FROM `user` 
            WHERE `UserId` = $username
            LIMIT 1;
        QUERY;

        $results = $database->query($query);
        if(!$results)
        {
            unset($_SESSION["user"]);
            return false;
        }

        $result = $results[0];

        $user = new user();
        $user->id = intval($result->Id);
        $user->name = $result->UserId;
        $user->initials = $result->Initials;
        $user->settings->decode($result->Settings);
        $user->rights->decode($result->Roles);

        $_SESSION["user"] = $user;

        return true;
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

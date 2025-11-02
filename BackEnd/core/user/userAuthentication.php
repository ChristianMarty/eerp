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
require_once __DIR__ . "/../error.php";

class UserAuthentication
{
    private string $ldapServer;
    private string $ldapBase;

    public userSettings|null $settings = null;

    function __construct(Database|null $database = null) {
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
            SELECT 
                `Id`,
                `UserId`,
                `Initials`,
                `Roles`,
                `Settings`
            FROM `user` 
            WHERE `UserId` = $username and `Token` = $token
            LIMIT 1;
        QUERY;
        $results = $database->query($query);

        if(!$results){
            $this->logout();
            return false;
        }
        self::setUserSession($results[0]);
        return $this->loggedIn();
    }

    function login(string $username, #[\SensitiveParameter] string $password ): stdClass | \Error\Data
    {
        $ldap = ldap_connect($this->ldapServer);
        if(!$ldap){
            return \Error\generic("LDAP connection error");
        }

        ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

        $ldapDn =  "uid=".$username.",".$this->ldapBase ;

        $bind = @ldap_bind($ldap, $ldapDn, $password);

        if (!$bind){
            $this->logout();
            return \Error\generic( "Username or password wrong");
        }

        if(!self::getUserInfoFromDb($username)){
            $this->logout();
            return \Error\generic( "Username not in user list");
        }

        if(!$this->loggedIn()){
            return \Error\generic( "Login error");
        }

        $loginReplyData = new stdClass();
        $loginReplyData->DisplayName = $this->displayName();
        $loginReplyData->UserRoles = $this->roles();
        return $loginReplyData;
    }

    function logout(): void
    {
        if(session_status() === PHP_SESSION_NONE) return;

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

    function checkPermission(\Permission $permission): bool
    {
        if(!isset($_SESSION['user'])){
            return false;
        }
        return in_array($permission, $_SESSION['user']->permissions);
    }

    function showPhpErrors(): bool
    {
        if($this->loggedIn()){
            return $_SESSION['user']->rights?->Error?->Php??false;
        }
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

    function userData(): User|null
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

        $returnData['name'] = $user->name;
        $returnData['roles'] = $this->getRolesFromPermissions();
        $returnData['settings'] = $user->settings->settings;

        return $returnData;
    }

    function roles(): array
    {
        $user = $this->userData();
        return (array)$user?->rights;
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
        if(!$results) {
            unset($_SESSION["user"]);
            return false;
        }
        self::setUserSession($results[0]);
        return true;
    }

    private function setUserSession(stdClass $userQueryResult): void
    {
        $user = new User();
        $user->id = intval($userQueryResult->Id);
        $user->name = $userQueryResult->UserId;
        $user->initials = $userQueryResult->Initials;
        $user->settings->decode($userQueryResult->Settings);
        $user->rights = json_decode($userQueryResult->Roles);
        $user->initializePermissions();

        $_SESSION["user"] = $user;
    }

    private function getRolesFromPermissions(): array
    {
        $output = [];
        if(!isset($_SESSION['user'])){
            return $output;
        }

        foreach($_SESSION['user']->permissions as $role) {
            $output[] = $role->name;
        }

        return $output;
    }
}

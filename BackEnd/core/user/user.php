<?php
//*************************************************************************************************
// FileName : user.php
// FilePath : /core/user/
// Author   : Christian Marty
// Date		: 11.02.2025
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

require_once __DIR__ . "/userRoles.php";
require_once __DIR__ . "/userSettings.php";

class user
{
    public int $id = 0;
    public string $name;
    public string $initials;
    public userRoles $rights;
    public userSettings $settings;

    function __construct()
    {
        $this->rights = new userRoles();
        $this->settings = new userSettings();
    }
}
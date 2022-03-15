<?php
//*************************************************************************************************
// FileName : login.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../databaseConnector.php";
require_once __DIR__ . "/../../config.php";


// TODO: This is fundamentally broken -> fix it

global $adServer;
global $ldapBase;

if($_SERVER['REQUEST_METHOD'] == 'POST')
{
		
	$data = json_decode(file_get_contents('php://input'),true);
	
	$username = $data['username'];
	$password = $data['password'];
	
    $ldap = ldap_connect($adServer);
	if ( !$ldap) sendResponse(null,"LDAP server connection faild");
	
    $ldaprdn =  "uid=".$username.",".$ldapBase;
	
    ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

    $bind = @ldap_bind($ldap, $ldaprdn, $password);
	
    if ($bind)
	{
		/*$rs = ldap_search ($ldap,$ldapBase,"(uid=".$username.")");
		$userAttributes = ldap_get_attributes($ldap,ldap_first_entry($ldap,$rs));
		$userRoles = array();
		
		foreach($userAttributes['memberOf'] as $roles)
		{
			if((strpos($roles, 'cn=BlueNova') !== false))
			{
				$temp = str_replace("cn=","",explode(",",$roles)[0]);
				array_push($userRoles, $temp);
			}
		}
		
		$userRolesTree = array();
		
		$_SESSION['roles'] = $userRoles;
		
		foreach($userRoles as $roles)
		{
			$temp = explode(".",$roles);
			if(!is_array($userRolesTree[$temp[0]])) $userRolesTree[$temp[0]] = array();
			array_push($userRolesTree[$temp[0]], $temp[1]);
		}*/
		
		$userRolesTree = array();
		
		$dbLink = dbConnect();
		if($dbLink == null) return null;

		$query = "SELECT * FROM user WHERE UserID = '".$username."'";

		$result = mysqli_query($dbLink,$query);
		
		$rows = array();

		while($r = mysqli_fetch_assoc($result)) 
		{
			$settings = json_decode($r['Settings']);
			$userRolesTree = json_decode($r['Roles']);
		}

		dbClose($dbLink);
		
		
		$_SESSION["username"] = $username;
		$_SESSION['loggedin'] = true;
		$_SESSION['UserRoles'] = $userRolesTree;
		$_SESSION["Settings"] = $settings;
		
		$returnData = array();
		$returnData['DisplayName'] = "User";//$userAttributes['displayName'][0];
		$returnData['UserRoles'] = $userRolesTree;
		
		$roles_array = array();
		$roles = $_SESSION['UserRoles'];

		foreach($roles as $key => $category)
		{
			$categoryName = $key;
			foreach($category as $key => $role)
			{
				$roleStr = $categoryName.".".$key;
				if($role == true) array_push($roles_array, $roleStr);
			}
		}
		
		$_SESSION['UserRolesString'] = $roles_array;

		sendResponse($returnData);
	}
	else
	{
		$_SESSION['loggedin'] = false;
		sendResponse(null, "Username or Password Wrong");
	}	
}

?>
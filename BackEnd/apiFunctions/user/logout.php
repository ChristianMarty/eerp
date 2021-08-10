<?php
//*************************************************************************************************
// FileName : logout.php
// FilePath : apiFunctions/user/
// Author   : Christian Marty
// Date		: 01.08.2020
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

if(isset($_SESSION))
{
	$_SESSION["username"] = "";
	$_SESSION['loggedin'] = false;
		
	$_SESSION = array(); // Löschen aller Session-Variablen.

	// This is from www.php.net/manual/
	if (ini_get("session.use_cookies")) {
		$params = session_get_cookie_params();
		setcookie(session_name(), '', time() - 42000, $params["path"],
			$params["domain"], $params["secure"], $params["httponly"]
		);
	}
	session_destroy();
}

sendResponse("success");
?>
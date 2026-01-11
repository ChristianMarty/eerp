<?php
//*************************************************************************************************
// FileName : yealinkPhoneBook.php
// FilePath : /dataAccess
// Author   : Christian Marty
// Date		: 02.12.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

if(!isset($_GET["user"]) || !isset($_GET["token"]))
{
    http_response_code(400);
    echo "400 Bad Request - user or token missing";
    exit;
}

require_once __DIR__ . "/../core/user/userAuthentication.php";
$database = new Database();
$user = new UserAuthentication();

$user->loginWithToken($_GET["user"],$_GET["token"]);

if(!$user->loggedIn())
{
    http_response_code(401);
    echo "401 Unauthorized - user or token wrong";
    exit;
}

if(!$user->checkPermission(Permission::Vendor_Contact_View))
{
    http_response_code(401);
    echo "401 Unauthorized - no permission";
    exit;
}

function escape(string|null $input):string
{
    if($input === null) return "";

    $input = str_replace('&', '&amp;', $input);
    $input = str_replace('"', '&quot;', $input);
    $input = str_replace("'", '&apos;', $input);
    $input = str_replace('<', '&lt;', $input);
    return str_replace('>', '&gt;', $input);
}

$query = <<< QUERY
    SELECT 
        FullName, 
        ShortName, 
        vendor_displayName(vendor.Id) AS DisplayName,
        FirstName, 
        LastName, 
        CustomerNumber, 
        Phone, 
        Gender, 
        Language, 
        EMail  
    FROM vendor
    LEFT JOIN vendor_contact ON vendor_contact.VendorId = vendor.Id
QUERY;

$result = $database->query($query);

header("Content-type: text/xml");

echo "<YealinkIPPhoneDirectory>";

foreach ($result AS $r)
{
	if(!$r->Phone) continue;
	
	$number = $r->Phone;
	$number = str_replace(' ', '', $number);
	$number = str_replace('-', '', $number);

	if(str_starts_with($number, "+")) $number = "00".substr($number, 1);
	if(str_starts_with($number, "0041")) $number = "0".substr($number, 4);
	
	if($r->DisplayName) $name = $r->DisplayName;
    else $name = $r->Name;
	
	if($r->FirstName || $r->LastName) $name .=";";
	
    if($r->FirstName) $name .= " ".$r->FirstName;
    if($r->LastName) $name .= " ".$r->LastName;

    echo "<DirectoryEntry>";
    echo "<Name>".escape($name)."</Name>";
    echo "<Telephone>".$number."</Telephone>";
    if($r->CustomerNumber) echo '<Extra label="Customer Number">'.escape($r->CustomerNumber)."</Extra>";
	if($r->FullName) echo '<Extra label="Company">'.escape($r->FullName)."</Extra>";
    if($r->Gender) echo '<Extra label="Gender">'.escape($r->Gender)."</Extra>";
    if($r->Language) echo '<Extra label="Language">'.escape($r->Language)."</Extra>";
    if($r->EMail)  echo '<Extra label="E-Mail">'.escape($r->EMail)."</Extra>";
    echo "</DirectoryEntry>";
}

echo "</YealinkIPPhoneDirectory>";
<?php
//*************************************************************************************************
// FileName : purchaseOrder.php
// FilePath : renderer/
// Author   : Christian Marty
// Date		: 17.01.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__ . "/../apiFunctions/databaseConnector.php";
require_once __DIR__ . "/../config.php";

require_once __DIR__ . "/../core/userAuthentication.php";

if(!isset($_GET["user"]) || !isset($_GET["token"]))
{
    http_response_code(400);
    echo "400 Bad Request";
    exit;
}

$user = new userAuthentication();

$user->loginWithToken($_GET["user"],$_GET["token"]);

if(!$user->loggedIn())
{
    http_response_code(401);
    echo "401 Unauthorized";
    exit;
}


function escape($input):string
{
    $input = str_replace('&', '&amp;', $input);
    $input = str_replace('"', '&quot;', $input);
    $input = str_replace("'", '&apos;', $input);
    $input = str_replace('<', '&lt;', $input);
    return str_replace('>', '&gt;', $input);
}

$dbLink = dbConnect();

$query = <<< QUERY
    SELECT 
        FullName, 
        ShortName, 
        vendor_displayName(Id) AS DisplayName,
        FirstName, 
        LastName, 
        CustomerNumber, 
        Phone, 
        Gender, 
        Language, 
        `E-Mail` AS EMail  
    FROM vendor
    LEFT JOIN vendor_contact ON vendor_contact.VendorId = vendor.Id
QUERY;

$result = dbRunQuery($dbLink,$query);

header("Content-type: text/xml");

echo "<YealinkIPPhoneDirectory>";

while($r = mysqli_fetch_assoc($result))
{
	if(!$r['Phone']) continue;
	
	$number = $r['Phone'];
	$number = str_replace(' ', '', $number);
	$number = str_replace('-', '', $number);

	if(str_starts_with($number, "+")) $number = "00".substr($number, 1);
	if(str_starts_with($number, "0041")) $number = "0".substr($number, 4);
	
	if($r['DisplayName']) $name = $r['DisplayName'];	
    else $name = $r['Name'];
	
	if($r['FirstName'] || $r['LastName']) $name .=";";
	
    if($r['FirstName']) $name .= " ".$r['FirstName'];
    if($r['LastName']) $name .= " ".$r['LastName'];

    echo "<DirectoryEntry>";
    echo "<Name>".escape($name)."</Name>";
    echo "<Telephone>".$number."</Telephone>";
    if($r['CustomerNumber']) echo '<Extra label="Customer Number">'.escape($r['CustomerNumber'])."</Extra>";
	if($r['Name']) echo '<Extra label="Company">'.escape($r['Name'])."</Extra>";
    if($r['Gender']) echo '<Extra label="Gender">'.escape($r['Gender'])."</Extra>";
    if($r['Language']) echo '<Extra label="Language">'.escape($r['Language'])."</Extra>";
    if($r['EMail'])  echo '<Extra label="E-Mail">'.escape($r['EMail'])."</Extra>";
    echo "</DirectoryEntry>";
}

echo "</YealinkIPPhoneDirectory>";

dbClose($dbLink);

?>
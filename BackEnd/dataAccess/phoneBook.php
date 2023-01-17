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

if(!isset($_GET["token"])) exit;


global $phoneToken;
if($_GET["token"] != $phoneToken)
{
    http_response_code(404);
    echo "404 Not Found";
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

$query = <<< STR
    SELECT Name, FirstName, LastName, CustomerNumber, Phone, Gender, Language, `E-Mail` AS EMail  FROM vendor
    LEFT JOIN vendor_contact ON vendor_contact.VendorId = vendor.Id
STR;

$result = dbRunQuery($dbLink,$query);

header("Content-type: text/xml");

echo '<?xml version="1.0"?>';
echo "<YealinkIPPhoneDirectory>";

while($r = mysqli_fetch_assoc($result))
{
    $name = $r['Name'];
    if($r['FirstName']) $name .= " ".$r['FirstName'];
    if($r['LastName']) $name .= " ".$r['LastName'];

    echo "<DirectoryEntry>";
    echo "<Name>".escape($name)."</Name>";
    if($r['Phone']) echo "<Telephone>".str_replace(' ', '', escape($r['Phone']))."</Telephone>";
    if($r['CustomerNumber']) echo '<Extra label="Customer Number">'.escape($r['CustomerNumber'])."</Extra>";
    if($r['Gender']) echo '<Extra label="Gender">'.escape($r['Gender'])."</Extra>";
    if($r['Language']) echo '<Extra label="Language">'.escape($r['Language'])."</Extra>";
    if($r['EMail'])  echo '<Extra label="E-Mail">'.escape($r['EMail'])."</Extra>";
    echo "</DirectoryEntry>";
}


echo "</YealinkIPPhoneDirectory>";


dbClose($dbLink);

?>
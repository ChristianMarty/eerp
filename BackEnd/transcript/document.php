<?php
//*************************************************************************************************
// FileName : document.php
// FilePath : transcript/
// Author   : Christian Marty
// Date		: 10.05.2026
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);
global $database;
global $api;

require_once __DIR__ . "/../core/numbering.php";

$parameters = $api->getGetData();

if(!isset($parameters->ItemCode)){
    http_response_code(422 );
    echo "Error 422  - Unprocessable Content -> ItemCode Missing";
    exit;
}

$documentNumber = \Numbering\parser(\Numbering\Category::Document, $parameters->ItemCode);
$documentRevision = \Numbering\parser(\Numbering\Category::DocumentRevision, $parameters->ItemCode);

if($documentNumber === null || $documentRevision === null){
    http_response_code(422 );
    echo "Error 422  - Unprocessable Content -> Parameter Error";
    exit;
}

$query  = <<<QUERY
    SELECT
        document_revision.Cache_Content AS Data
    FROM  document_revision
    LEFT JOIN document ON document_revision.DocumentNumberId = document.Id
    WHERE document.DocumentNumber = $documentNumber AND document_revision.RevisionNumber = $documentRevision
QUERY;
$result = $database->query($query);

if(count($result)===0){
    http_response_code(404 );
    echo "Error 404  - Not Found -> ".$parameters->ItemCode;
    exit;
}

$pages = json_decode($result[0]->Data);

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $parameters->ItemCode; ?> transcript</title>
    <link  media="print" />
</head>

<style>
    body {
        background-color: LightGray;
    }
    div.page{
        background-color: white;
        margin: 20px;
        padding: 20px;
    }
    pre{
        white-space: pre-wrap;
        max-width: 100%;
        overflow-x: auto;
    }
</style>


<?php
    foreach ($pages as $data) {
        if(strlen($data->Data)===0) continue;

        echo '<div class="page">'.PHP_EOL;
        echo '<h2>Page '.$data->Page.'</h2>'.PHP_EOL;
        echo '<pre>';
        //echo nl2br($data->Data);
        //echo str_replace( "\n", '<br />', $data->Data ).PHP_EOL;
        echo $data->Data;
        echo '</pre>';
        echo '</div>'.PHP_EOL;
    }
?>

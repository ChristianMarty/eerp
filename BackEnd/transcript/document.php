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
    echo "Error 404  - Item not found -> ".$parameters->ItemCode;
    exit;
}

if($result[0]->Data===null){
    http_response_code(404 );
    echo "Error 404  - No data for item found -> ".$parameters->ItemCode;
    exit;
}

$pages = json_decode($result[0]->Data);

?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title><?php echo $parameters->ItemCode; ?> transcript</title>
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
<body>
<?php

    function fixHtmlTables(string $input): string
    {
        $output = $input;

        $output = preg_replace('/<$/', '', $output);
        $output = preg_replace('/<td$/', '', $output);
        $output = preg_replace('/<tr$/', '', $output);

        $tableOpen = substr_count($output,'<table>');
        $tableClose = substr_count($output,'</table>');

        $rowOpen = substr_count($output,'<tr>');
        $rowClose = substr_count($output,'</tr>');

        if(str_ends_with($output, '</tr') || str_ends_with($output, '</td')){
            $output .= '>';
        }

        if($rowOpen !== $rowClose){
            $output .= '</tr>';
        }

        if($tableOpen !== $tableClose){
            $output .= '</table>';
        }

        return $output;
    }

    foreach ($pages as $data) {
        $content = trim($data->Data);
        if(strlen($content)===0) continue;

        $content = str_replace(' < ', ' &lt; ', $content);
        $content = str_replace(' <= ', ' &lt;= ', $content);
        $content = str_replace(' > ', ' &gt; ', $content);
        $content = str_replace(' >= ', ' &gt;= ', $content);

        echo '<div class="page">'.PHP_EOL;
        echo '<h2>Page '.$data->Page.'</h2>'.PHP_EOL;
        echo '<hr>'.PHP_EOL;
        echo '<pre>'.PHP_EOL;
        echo $content;
        echo '</pre>'.PHP_EOL;
        echo '</div>'.PHP_EOL;
    }
?>
</body>

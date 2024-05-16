<?php
//*************************************************************************************************
// FileName : _files.php
// FilePath : apiFunctions/util/
// Author   : Christian Marty
// Date		: 10.08.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************

require_once __DIR__."/_extractVariable.php";

function files_listFiles(string $path, string|null $entrypoint = null): array
{
    $localPath = __DIR__."/../".$path;  // TODO: This is a hack

    $output = array();
    $files = scandir($localPath);
    $files = array_diff($files, array('.', '..'));

    foreach($files as $file)
    {
        if(is_file($localPath.$file))
        {
            if(pathinfo($path.$file,PATHINFO_EXTENSION ) == "php" && !str_starts_with ($file,'_'))
            {
                $output[] = files_getInfo($localPath, $path, $file, $entrypoint);
            }
        }
        else if(is_dir($localPath.$file))
        {
            $files2 = scandir($localPath.$file);
            $files = array_diff($files, array('.', '..'));

            foreach( $files2 as $file2)
            {
                if(pathinfo($path.$file."/".$file2,PATHINFO_EXTENSION ) == "php" && !str_starts_with ($file,'_'))
                {
                    $output[] = files_getInfo($localPath.$file,$path.$file, $file2, $entrypoint);
                }
            }
        }
    }
    return $output;
}

function files_getInfo(string $localPath, string $path, string $file, string|null $entrypoint = null): array
{
    global $apiRootPath;

    $filePath = $localPath."/".$file;
	if($entrypoint === null) $externalPath = $path."/".pathinfo($filePath,PATHINFO_FILENAME);
    else $externalPath = $entrypoint."/".pathinfo($filePath,PATHINFO_FILENAME);

    $output = array();
    $output["FileName"] = $file;
    $output["Title"] = extractVariable($filePath,"title");
    $output["Description"] = extractVariable($filePath,"description");
    $output["Path"] = $externalPath;

    return $output;
}
?>
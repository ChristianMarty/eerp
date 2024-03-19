<?php
//*************************************************************************************************
// FileName : _partNumberPreprocessing.php
// FilePath : apiFunctions/vendor/_preprocessor
// Author   : Christian Marty
// Date		: 13.11.2023
// License  : MIT
// Website  : www.christian-marty.ch
//*************************************************************************************************
declare(strict_types=1);

abstract class PartNumberPreprocessingBase
{
    static public function clean(string $input) : string{
        return trim($input);
    }

    static public function format(string $input) : string{
        return $input;
    }
}

class PartNumberPreprocessing extends PartNumberPreprocessingBase
{
    // use default from PartNumberPreprocessingBase
}

class PartNumberPreprocess
{
    private PartNumberPreprocessingBase $preprocessor;

    public function __construct(string|int|null $partNumberPreprocessing = null)
    {
        $partNumberPreprocessingName = null;
        if(is_integer($partNumberPreprocessing))
        {
            global $database;
            $query = "SELECT PartNumberPreprocessor FROM vendor WHERE Id = '$partNumberPreprocessing';";
            $return = $database->query($query);
            if(!empty($return) && $return[0] !== null){
                $partNumberPreprocessingName = $return[0]->PartNumberPreprocessor;
            }
        }
        else if(is_string($partNumberPreprocessing)){
            $partNumberPreprocessingName = trim($partNumberPreprocessing);
        }

        if($partNumberPreprocessingName === null) {
            $this->preprocessor = new PartNumberPreprocessing();
        }else{
            $filePath = __DIR__.'/'.$partNumberPreprocessingName.'.php';
            if (!file_exists($filePath)) {
                throw new EerpException("The requested part number preprocessor does not exist");
            }
            require_once($filePath);
            $this->preprocessor = new $partNumberPreprocessingName();
        }
    }

    public function clean(string $string): string
    {
        return $this->preprocessor->clean($string);
    }

    public function format(string $string): string
    {
        return $this->preprocessor->format($string);
    }
}
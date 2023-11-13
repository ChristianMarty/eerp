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

    public function __construct(string|null $partNumberPreprocessing = null)
    {
        if($partNumberPreprocessing == null) {
            $this->preprocessor = new PartNumberPreprocessing();
        }else{
            $filePath = __DIR__.'/'.$partNumberPreprocessing.'.php';
            if (!file_exists($filePath)) {
                throw new EerpException("The requested part number preprocessor does not exist");
            }
            require_once($filePath);
            $this->preprocessor = new $partNumberPreprocessing();
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
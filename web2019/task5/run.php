<?php

new Solution();

class Solution
{
    private $filename = '';
    private $inputFilename = '';

    private $out = array();

    /**
     * Solution constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'input.txt', $filename = 'output.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->writeDataArray();
        }catch(Exception $e){
            echo $e->getMessage();
        }
    }

    //считывание входных данных
    private function setDataArray()
    {
        
        $this->recursiveDir(__DIR__ . DIRECTORY_SEPARATOR . 'in');
    }

    function recursiveDir($dir)
    {
        static $deep = 0;
        $odir = opendir($dir);
        
    
        while (($file = readdir($odir)) !== FALSE)
        {
            if ($file == '.' || $file == '..')
            {
                continue;
            }
            else
            {
                echo str_repeat('---', $deep).$dir.DIRECTORY_SEPARATOR.$file."\n";
            }
            
            if (is_dir($dir.DIRECTORY_SEPARATOR.$file))
            {
                $deep ++;
                $this->recursiveDir($dir.DIRECTORY_SEPARATOR.$file);
                $deep --;
            } else {
                include($dir.DIRECTORY_SEPARATOR.$file);
                print_r($arMenu);
            }
        }
        closedir($odir);
    }

    //записываем результат
    private function writeDataArray()
    {
        foreach ($this->out as &$line) {
            echo $line."\n";
        }
        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            foreach ($this->out as &$line) {
                @fwrite($handle, $line."\n");
            }
            fclose($handle);
        }
    }
}

?>

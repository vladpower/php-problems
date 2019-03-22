<?php

new Template();

class Template
{
    private $filename = '';
    private $inputFilename = '';

    private $firstDayJd = 0;
    private $out = array();

    /**
     * Template constructor.
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
        $this->dataArray = array();

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename, "r");

        if ($handle)
        {
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $str = trim($buffer);
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if($this->firstDayJd == 0) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

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

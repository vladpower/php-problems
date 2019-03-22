<?php

new MaskReplacer();

class MaskReplacer
{
    private $filename = '';
    private $inputFilename = '';

    private $attribute = array();
    private $text = '';
    private $out = '';

    /**
     * MaskReplacer constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'input.txt', $filename = 'output.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->replaceMask();
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
            $buffer = fgets($handle, 4096);
            $this->num = intval($buffer);
            for($i = 1; $i <= $this->num; $i++)
            {
                $buffer = fgets($handle, 4096);
                $space_pos = strpos($buffer, ' ');
                if($space_pos !== FALSE)
                {
                    $key = substr($buffer, 0, $space_pos);
                    $value = substr($buffer, $space_pos + 1, strlen($buffer)-$space_pos-2);
                    $this->attribute[$key] = $value;
                }
            }
            $buffer = fgets($handle, 4096);
            if($buffer !== "\n")
                $this->text.=$buffer;
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $this->text.=$buffer;
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if($this->num == 0) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

    }

    private function replaceCallback($matches) 
    {
        $res = "";
        if(isset($this->attribute[$matches[2]])) 
        {
            // replacement with the value specified in the placeholder
            $res = $this->attribute[$matches[2]];
        } 
        else if(count($matches) >= 8)
        {
            // default placeholder replacement
            $res = $matches[6];
        }
        return $res;
    }

    private function replaceMask()
    {
        $pattern = '/\[\[=region(\s+)(\w+)(\s*)(,(\s*)([^ ^\[^\]]+(\s+[^ ^\[^\]]+)*)(\s*))?\]\]/i';
        $this->out = preg_replace_callback($pattern, array($this, 'replaceCallback'), $this->text);
    }

    //записываем результат
    private function writeDataArray()
    {

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            echo $this->out;
            @fwrite($handle, $this->out);
            fclose($handle);
        }
    }
}

?>

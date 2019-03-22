<?php

new Decoder();

class Decoder
{
    private $filename = '';
    private $inputFilename = '';

    private $str = 0;
    private $word = 0;

    private $func = array('add', 'fir', 'sec', 'ofl');

    /**
     * Decoder constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'in.txt', $filename = 'out.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->decodeStr();
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
                $this->str = trim($buffer);
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if(empty($this->str)) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

    }

    private function decodeStr()
    {
        $stack = array();

        $tokens = explode('-', $this->str);

        $token = array_shift($tokens);

        while (!empty($token))
        {
            if (in_array($token, $this->func))
            {
                //проверка на количество операндов
                if (count($stack) < 2)
                    throw new Exception("Недостаточно данных в стеке для операции '$token'");

                //достаем из стека два операнда
                $b = array_pop($stack);
                $a = array_pop($stack);

                //производим над ними действие
                switch ($token)
                {
                    case 'add': $res = $a.$b; break; //конкатенация
                    case 'fir': $res = $a; break; //первое слово
                    case 'sec': $res = $b; break; //второе слово
                    case 'ofl': $res = $a[0].$b[0]; break; //конкатенация первых символов операндов
                }
                //кладем результат в стек
                array_push($stack, $res);
            } elseif (is_string($token) && strlen($token) == 3) //проверяем на количество символов в операнде
            {
                array_push($stack, $token);
            } else
            {
                throw new Exception("Недопустимый символ в выражении: $token");
            }

            $token = array_shift($tokens);
        }
        if (count($stack) > 1)
            throw new Exception("Количество операторов не соответствует количеству операндов");
        $this->word = array_pop($stack);
    }

    //записываем результат
    private function writeDataArray()
    {
        echo $this->word;

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            @fwrite($handle, $this->word);
            fclose($handle);
        }
    }


}

?>

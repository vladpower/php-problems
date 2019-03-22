<?php

new SearchEngine();

function utf_substr($string, $offset, $length)
{
  $arr = preg_split("//u", $string);
  $slice = array_slice($arr, $offset + 1, $length);
  return implode("", $slice);
}


function utf_strcmp($str1, $str2, $encoding = null) {
    return strcmp($str1, $str2);
    // if (null === $encoding) { $encoding = mb_internal_encoding(); }
    // return strcmp(mb_strtoupper($str1, $encoding), mb_strtoupper($str2, $encoding));
}

function sortByRelevance($prod1, $prod2){
    if($prod1->getRelevance() == $prod2->getRelevance()) {
        return ($prod1->getName() < $prod2->getName())  ? -1 : 1;
    }
    return ($prod1->getRelevance() > $prod2->getRelevance()) ? -1 : 1; 
}

class Product
{
    private $number;
    private $index;
    private $name;
    private $code;
    private $manufacturer;
    private $relevance;

    public function __construct($str, $num)
    {
        $buffer = explode(";", $str);
        $this->index = intval($buffer[0]);
        $this->name = $buffer[1];
        $this->code = $buffer[2];
        $this->manufacturer = $buffer[3];
        $this->number = $num;
    }

    public function calculateRelevance($searchQuery) 
    {
        $splitPattern = '/([^\w^\d]+)|((?<=[a-zA-Zа-яА-Я^\(^\)])(?=\d))|((?<=\d)(?=[a-zA-Zа-яА-Я]))/u';
        $queryWords = preg_split($splitPattern, $searchQuery);
        $productWords = preg_split($splitPattern, $this->name);
        //$productWords = array_unique($productWords);
        //print_r($productWords);
        $rn = 0.0;
        $rc = 0.0;
        $rm = 0.0;
        foreach($queryWords as $qword)
        {
            $qlen = strlen(utf8_decode($qword));
            $pos = strpos($this->name, $qword);
            if($pos !== FALSE)
            {
                $rn += $qlen * 0.1;
                //echo $qword." ".$qlen."\n";
            }
            else if($qlen > 2)
            {
                $pos = strpos($this->code, $qword);
                if($pos !== FALSE)
                {
                    $rc += $qlen * 0.1;
                    //echo $qword." ".$qlen." * 0.8\n";
                }
                $pos = strpos($this->manufacturer, $qword);
                if($pos !== FALSE)
                {
                    $rm += $qlen * 0.1;
                    //echo $qword." ".$qlen." * 0.8\n";
                }
            }
            foreach($productWords as $pword)
            {
                $plen = strlen(utf8_decode($pword));
                if(utf_strcmp($qword, utf_substr($pword, 0, $qlen)) === 0) {
                    $rn += 0.05;
                    //echo $qword." ".$pword."\n";
                    break;
                }
            }
            foreach($productWords as $pword)
            {
                $plen = strlen(utf8_decode($pword));
                if(utf_strcmp($qword, utf_substr($pword, $plen-$qlen, $qlen)) === 0) {
                    $rn += 0.05;
                    //echo $qword." ".$pword."\n";
                    break;
                }
            }
            
        }
        for( $i = 0; $i < count($queryWords) - 1; $i++)
        {
            $regWord1 = preg_quote($queryWords[$i], '/');
            $regWord2 = preg_quote($queryWords[$i+1], '/');
            $pair ='/'.$regWord1.'.*'.$regWord2.'/ui';
            preg_match_all($pair, $this->name, $matches);
            if(count($matches[0]) > 0)
            {
                //echo $pair."\n";
                $rn +=  0.1;
            }
        }
        $this->relevance = $rn + 0.8*$rc + 0.8*$rm;
        //echo $this->name." ".$this->relevance."\n";
    }

    public function getIndex()
    {
        return $this->index;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getRelevance()
    {
        return $this->relevance;
    }
}

class SearchEngine
{
    private $filename = '';
    private $inputFilename = '';

    private $searchQuery = '';
    private $products = array();
    private $topProducts = array();

    /**
     * SearchEngine constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'input.txt', $filename = 'output.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->calculateRelevance() ;
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
            $buffer = substr($buffer, 0, strlen($buffer)-1);
            $this->searchQuery = $buffer;
            fgets($handle, 4096);
            $i = 0;
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $buffer = substr($buffer, 0, strlen($buffer)-1);
                if(!empty($buffer))
                    $this->products[] = new Product($buffer, $i);
                $i++;
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if($this->searchQuery == '') echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

    }

    private function calculateRelevance() 
    {
        foreach($this->products as $product)
        {
            $product->calculateRelevance($this->searchQuery);
        }
        $products = $this->products;
        usort($products, 'sortByRelevance'); 
        $this->topProducts = array_splice($products, 0, 10);
    }

    //записываем результат
    private function writeDataArray()
    {

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            foreach($this->topProducts as $product)
            {
                $out = $product->getIndex()." ".$product->getRelevance()."\n";
                echo $out;
                @fwrite($handle, $out);
            }
            
            fclose($handle);
        }
    }
}

?>

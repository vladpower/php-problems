<?php

new LogAnalyzer();

class User
{
    private $ip = '';
    private $queries = array();
    private $warnings = array();
    private $ban = FALSE;

    public function __construct($ip)
    {
        $this->ip = $ip;
    }

    public function addQuery($time, $retCode, $timeIntervalN, $maxQueryCount, $timeIntervalK, $maxWarningNum)
    {
        if($this->ban === FALSE)
        {
            $this->queries[] = $time;
            if($retCode === '2') {
                $this->checkQueries($time, $time - $timeIntervalN, $maxQueryCount);
                $this->checkWarnings($time, $time - $timeIntervalK, $maxWarningNum);
            }
            return $this->isBan();
        }
        return FALSE;
    }

    private function checkQueries($curTime, $lastTime, $maxQueryCount)
    {
        while($this->queries[0] < $lastTime)
        {
            array_shift($this->queries);
        }
        if(count($this->queries) > $maxQueryCount)
        {
            $this->warnings[] = $curTime;
            //echo "warning ".$this->ip." ".$curTime."\n";
        }
    }

    private function checkWarnings($curTime, $lastTime, $maxWarningNum)
    {
        while(isset($this->warnings[0]) && $this->warnings[0] < $lastTime)
        {
            array_shift($this->warnings);
        }
        if(count($this->warnings) > $maxWarningNum)
        {
            $this->ban = TRUE;
        }
    }

    public function isBan()
    {
        return $this->ban;
    }
}

class LogAnalyzer
{
    private $filename = '';
    private $inputFilename = '';

    private $timeIntervalN;
    private $maxQueryCount;
    private $timeIntervalK;
    private $maxWarningNum;
    private $queryLog = array();
    private $bannedIp = array();
    private $users = array();

    /**
     * LogAnalyzer constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'input.txt', $filename = 'output.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->banIp() ;
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
            $arr = explode(' ', $buffer);
            $this->timeIntervalN = $arr[0];
            $this->maxQueryCount = $arr[1];
            $this->timeIntervalK = $arr[2];
            $this->maxWarningNum = $arr[3];
            fgets($handle, 4096);
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $buffer = substr($buffer, 0, strlen($buffer)-1);
                if(strlen($buffer) > 0)
                    $this->queryLog[] = $buffer;
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if($this->timeIntervalN == 0) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

    }

    private function banIp()
    {
        //$pattern = '/([\d\.]+)\s?-\s?([\w]*)\s?-\s?\[((\d+)\/(\w+)\/(\d+):(\d+):(\d+):(\d+)\s?(\+\d+))\s?\-\s?(([\d]*\.[\d]*)|\-)\]\s?(\d+)\s?"([^"]+)"\s(\d+)\s"([^"]+)"\s"([^"]+)"\s"-"/'; 
        $pattern = '/([\d\.]+)\s?-\s?([\w]*)\s?-\s?\[((\d+)\/(\w+)\/(\d+):(\d+):(\d+):(\d+)\s?(\+\d+))\s?\-\s?(([\d]*\.[\d]*)|\-)\]\s?(\d+)/';
        //echo count($this->queryLog)."\n";
        foreach($this->queryLog as $line)
        {
            preg_match_all($pattern , $line, $matches);
            if(isset($matches[0][0])) {
                $ip = $matches[1][0];
                $dateStr = $matches[3][0];
                $time = strtotime($dateStr);
                $retCode = $matches[13][0];
                if(!isset($this->users[$ip]))
                    $this->users[$ip] = new User($ip);
                $isAddBan = $this->users[$ip]->addQuery($time, $retCode[0], $this->timeIntervalN, $this->maxQueryCount, $this->timeIntervalK, $this->maxWarningNum);
                if($isAddBan)
                {
                    $this->bannedIp[]=$ip;
                }
                //echo $ip." ".$time." ".$retCode."\n";
                
            }
            
        }
        
    }

    //записываем результат
    private function writeDataArray()
    {

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            foreach($this->bannedIp as $ip)
            {
                $out = $ip."\n";
                echo $out;
                @fwrite($handle, $out);
            }
            fclose($handle);
        }
    }
}

?>

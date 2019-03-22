<?php

new WorkingCalendar();

abstract class WorkMode
{
    private $curState = 0;
    protected $nextWorkDayIn;

    abstract public function isDayOff($dayJd);

    public function nextWorkDay($curDayJd)
    {
        $nextDay = $curDayJd + $this->nextWorkDayIn[$this->curState];
        $this->curState = ($this->curState + 1) % count($this->nextWorkDayIn);
        while($this->isDayOff($nextDay)) {
            $nextDay++;
        }
        return $nextDay;
    }
}

class WorkMode2n2 extends WorkMode
{
    protected $nextWorkDayIn;
    public function __construct()
    {
        $this->nextWorkDayIn = [1, 3];
    }

    public function isDayOff($dayJd)
    {
        if($dayJd % 7 == 6) // Is sunday?
            return True;
        return False;
    }
}

class WorkingCalendar
{
    private $filename = '';
    private $inputFilename = '';

    private $firstDayJd = 0;
    private $out = array();

    /**
     * WorkingCalendar constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'input.txt', $filename = 'output.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        $this->setDataArray();
        try {
            $this->getWorkDays(30);
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
                $this->firstDayJd = $this->strToDateJd($str);
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if($this->firstDayJd == 0) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;

    }

    private function getWorkDays($count)
    {
        $workMode = new WorkMode2n2();
        $curDayJd = $this->firstDayJd;
        $endDayJd = $curDayJd + $count;
        while($workMode->isDayOff($curDayJd))
            $curDayJd++;
        while($curDayJd < $endDayJd)
        {
            $this->out[] = $this->dateJdToStr($curDayJd);
            $curDayJd = $workMode->nextWorkDay($curDayJd);
        }
    }

    private function dateJdToStr($dayJd)
    {
        $str = jdtogregorian($dayJd);
        $dataNums = explode("/", $str);
        list($mounth, $day, $year) = $dataNums;
        return sprintf("%02d.%02d.%04d", $day, $mounth, $year);
    }

    private function strToDateJd($str)
    {
        $dataNums = explode('.', $str);
        if (count($dataNums) != 3)
            throw new Exception("Неверный формат даты");
        list($day, $mounth, $year) = $dataNums;
        $jd = gregoriantojd($mounth, $day, $year);     
        return $jd;
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

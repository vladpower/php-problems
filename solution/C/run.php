<?

new Matches();

class Matches
{
    private $filename = '';
    private $inputFilename = '';

    private $num = 0;

    private $baseImage;
    private $width = 600;
    private $height = 600;

    private $matchLength = 50;
    private $matchColor;

    private $count = 0;

    /**
     * Matches constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'in.txt', $filename = 'out.txt')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;

        // пустая картинка
        $this->baseImage = @imagecreatetruecolor($this->width, $this->height);

        // цвета
        $bgcolor = ImageColorAllocate($this->baseImage, 240, 240, 240);
        $this->matchColor = ImageColorAllocate($this->baseImage, 10, 10, 10);

        //фон
        imagefill($this->baseImage, 0, 0, $bgcolor);

        $this->setDataArray();
        $this->drawMatches();
        $this->writeNum();

        //вывод в браузер
        header("Content-Type: image/png");
        imagepng ($this->baseImage);

        imagedestroy($this->baseImage);


    }

    //считываем входные данные
    private function setDataArray()
    {
        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename, "r");

        if ($handle)
        {
            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $this->num = intval($buffer);
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if(empty($this->num)) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;
    }

    private function drawMatchesLine($startX, $startY, $length, $isVertical)
    {
        $delta = 0.1 * $this->matchLength;

        $x = $startX;
        $y = $startY;

        if($isVertical)
        {
            $y += $this->matchLength / 2;
            $w = 2;
            $h = $this->matchLength - 2 * $delta;
        }
        else
        {
            $x += $this->matchLength / 2;
            $w = $this->matchLength - 2 * $delta;
            $h = 2;
        }

        $this->count += $length;

        for($i = 0; $i < $length; $i++)
        {
            imagefilledellipse($this->baseImage, $x, $y, $w, $h, $this->matchColor);

            if($isVertical) $y += $this->matchLength;
            else $x += $this->matchLength;
        }
    }


    private function drawMatches()
    {
        $sq = floor(sqrt($this->num));

        $margin = min($this->width, $this->height) * 0.05;
        $ostatok = $this->num - $sq*$sq;

        if($ostatok > 0)
            $this->matchLength = min($this->width, $this->height) * 0.9 / ($sq + 1);
        else
            $this->matchLength = min($this->width, $this->height) * 0.9 / $sq;

        // вертикальные
        for($i = 0; $i <= $sq; $i++)
        {
            if($i <= $ostatok && $ostatok > 0)
                $length = $sq + 1;
            else
                $length = $sq;

            $this->drawMatchesLine($margin + $i * $this->matchLength, $margin, $length, true);
        }

        $ostatok2 = $ostatok - $sq;

        if($ostatok2 > 0)
            $this->drawMatchesLine($margin + ($sq + 1) * $this->matchLength, $margin, $ostatok2, true);

        // горизонтальные
        for($i = 0; $i <= $sq; $i++)
        {
            if($i <= $ostatok2 && $ostatok2 > 0)
                $length = $sq + 1;
            else
                $length = $sq;

            $this->drawMatchesLine($margin, $margin + $i * $this->matchLength, $length, false);
        }

        if($ostatok2 > 0)
            $this->drawMatchesLine($margin, $margin + ($sq + 1) * $this->matchLength, $sq, false);
        else
            $this->drawMatchesLine($margin, $margin + ($sq + 1) * $this->matchLength, $ostatok, false);

    }

    //записываем результат
    private function writeNum()
    {
        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->filename, "w");
        if ($handle)
        {
            @fwrite($handle, $this->count."\n");
            fclose($handle);
        }
    }



}

?>
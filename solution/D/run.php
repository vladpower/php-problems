<?
new DrawFractale();

class DrawFractale
{
    private $filename = '';
    private $inputFilename = '';

    private $baseImage;
    private $width = 800;
    private $height = 600;

    private $length = 5;
    private $start;
    private $color;
    private $degree = 0;

    private $save = array();

    private $dataArray = array();

    // цвета
    private $arColors = array();


    /**
     * DrawFractale constructor.
     * @param string $inputFilename
     * @param string $filename
     */
    public function __construct($inputFilename = 'in.txt', $filename = 'out.png')
    {
        $this->filename = $filename;
        $this->inputFilename = $inputFilename;
        $this->setDataArray();

        // пустая картинка
        $this->baseImage = @imagecreatetruecolor($this->width, $this->height);

        // цвета
        $bgcolor = ImageColorAllocate($this->baseImage, 255, 255, 255);
        $this->color = ImageColorAllocate($this->baseImage, rand(0, 240), rand(0, 240), rand(0, 240));
        $this->setColors(20);

        //фон
        imagefill($this->baseImage, 0, 0, $bgcolor);

        $this->drawFractaleRec();

        //вывод в браузер
        header("Content-Type: image/png");
        imagepng ($this->baseImage);

        imagedestroy($this->baseImage);
    }

    //считываем входные данные
    private function setDataArray()
    {
        $this->dataArray = array();

        $handle = @fopen(__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename, "r");

        if ($handle)
        {
            $buffer = fgets($handle, 4096);
            $arr = explode(' ', trim($buffer));
            $this->width = intval($arr[0]);
            $this->height = intval($arr[1]);
            $this->length = intval($arr[2]);
            $this->start['x'] = intval($arr[3]);
            $this->start['y'] = intval($arr[4]);
            $this->degree = deg2rad(intval($arr[5]));


            $buffer = fgets($handle, 4096);
            $this->dataArray['axioma'] = trim($buffer);

            $buffer = fgets($handle, 4096);
            $this->dataArray['degree'] = deg2rad(intval(trim($buffer)));

            $buffer = fgets($handle, 4096);
            $this->dataArray['iteration'] = intval(trim($buffer));

            while (($buffer = fgets($handle, 4096)) !== false)
            {
                $arr = explode(' ', trim($buffer));
                $this->dataArray['rules'][$arr[0]] = $arr[1];
            }
            if (!feof($handle))
            {
                echo "Error: unexpected fgets() fail\n";
            }
            fclose($handle);
        }

        if(empty($this->dataArray)) echo 'Error: no data in file '.__DIR__ . DIRECTORY_SEPARATOR . $this->inputFilename;
    }



    private function save()
    {
        $s = array(
            'degree' => $this->degree,
            'start' => $this->start
        );

        array_push($this->save, $s);
    }

    private function restore()
    {
        $s = array_pop($this->save);
        $this->degree = $s['degree'];
        $this->start = $s['start'];
    }

    private function doAction($symbol)
    {
        switch($symbol)
        {
            case '+':
                $this->degree += $this->dataArray['degree'];
                break;
            case '-':
                $this->degree -= $this->dataArray['degree'];
                break;
            case '[':
                $this->save();
                break;
            case ']':
                $this->restore();
                break;
            case 'F':
                $new['x'] = $this->start['x'] + $this->length * cos($this->degree);
                $new['y'] = $this->start['y'] + $this->length * sin($this->degree);
                imageline($this->baseImage, $this->start['x'], $this->start['y'], $new['x'], $new['y'], $this->color);
                $this->start = $new;
                break;
            default:
                break;
        }
    }

    private function goFgcd($symbol, $iter)
    {
        if($iter < $this->dataArray['iteration'])
        {
            if(!empty($this->dataArray['rules'][$symbol]))
            {
                $str = $this->dataArray['rules'][$symbol];
                $l = strlen($str);
                for($i = 0; $i < $l; $i++)
                {
                    $this->goFgcd($str[$i], $iter + 1); // запускаем рекурсию
                }
            }
            else
            {
                $this->doAction($symbol);
            }
        }
        else
        {
            $this->doAction($symbol);
        }
    }

    private function drawFractaleRec()
    {
        $str = $this->dataArray['axioma'];

        $l = strlen($str);
        for($i = 0; $i < $l; $i++)
        {
            $this->goFgcd($str[$i], 1);
        }
    }

    private function setColors($count)
    {
        $borders = array(
            'r' => array(
                'from' => 77,
                'to' => 171
            ),
            'g' => array(
                'from' => 1,
                'to' => 0
            ),
            'b' => array(
                'from' => 95,
                'to' => 214
            ),
        );


        for($i = 0; $i < $count; $i++)
        {
            $r = $borders['r']['from'] + $i * (($borders['r']['to'] - $borders['r']['from']) / $count); //rand(0, 255);
            $g = $borders['g']['from'] + $i * (($borders['g']['to'] - $borders['g']['from']) / $count); //rand(0, 255);
            $b = $borders['b']['from'] + $i * (($borders['b']['to'] - $borders['b']['from']) / $count); //rand(0, 255);

            $this->arColors[$i] = ImageColorAllocate($this->baseImage, $r, $g, $b);
        }

    }

}

?>
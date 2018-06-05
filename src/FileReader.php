<?php

class FileReader
{
    private $f;

    public function __construct($filename)
    {
        $this->f = fopen($filename, 'rb');
    }

    public function __destruct()
    {
        if ($this->f) {
            fclose($this->f);
        }
    }

    public function lines()
    {
        $nextLine = null;
        while (!feof($this->f)) {
            $s = fgets($this->f);
            if ($s === false) {
                continue;
            }

            yield rtrim($s, "\r\n");
        }
    }
}


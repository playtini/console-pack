<?php

namespace Playtini\ConsolePack\Io;

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

    public function load()
    {
        $result = [];

        foreach ($this->lines() as $line) {
            $result[] = $line;
        }

        return $result;
    }

    public function lines()
    {
        $nextLine = null;
        while (!feof($this->f)) {
            $s = fgets($this->f);
            if ($s !== false) {
                yield rtrim($s, "\r\n");
            }
        }
    }
}


<?php

namespace Playtini\ConsolePack;

class FileWriter
{
    private $f;

    private $filename;

    private $inited = false;

    private $mode;

    public function __construct($filename, $mode = 'wb')
    {
        $this->filename = $filename;
        $this->mode = $mode;

        if (is_file($filename)) {
            $this->init();
        }
    }

    public function __destruct()
    {
        if ($this->f) {
            fclose($this->f);
        }
    }

    public function append($s)
    {
        $this->init();

        if (is_array($s)) {
            $s = implode("\t", $s);
        }
        if (substr($s, -1) !== "\n") {
            $s .= "\n";
        }

        fwrite($this->f, $s);
    }

    public function flush()
    {
        fflush($this->f);
    }

    protected function init(): void
    {
        if ($this->inited) {
            return;
        }

        // create directory for file if not created
        if (!is_file($this->filename)) {
            $dir = dirname($this->filename);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }

        $this->f = fopen($this->filename, $this->mode);
        $this->inited = true;
    }
}

<?php

namespace Playtini\ConsolePack\Filter;

class Thread
{
    /** @var int */
    private $threadCount = 1;

    /** @var int */
    private $threadNo = 1;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        global $argv;

        if (!isset($argv[2])) {
            return;
        }

        $this->threadCount = (int)$argv[1];
        $this->threadNo = (int)$argv[2];
        if ($this->threadNo > $this->threadCount) {
            throw new \Exception(sprintf('ThreadNo %s > ThreadCount %s', $this->threadNo, $this->threadCount));
        }
        if ($this->threadCount < 1) {
            throw new \Exception(sprintf('ThreadCount %s < 1', $this->threadCount));
        }
        if ($this->threadNo < 1) {
            throw new \Exception(sprintf('ThreadNo %s < 1', $this->threadNo));
        }
    }

    public function needProcess(string $key): bool
    {
        if ($this->threadCount <= 1) {
            return true;
        }

        return (abs(crc32($key)) % $this->threadCount === $this->threadNo - 1);
    }

    public function getThreadCount(): int
    {
        return $this->threadCount;
    }

    public function getThreadNo(): int
    {
        return $this->threadNo;
    }

    public function addFilenameSuffix(string $filename): string
    {
        if ($this->threadCount <= 1) {
            return $filename;
        }

        $strThreadNo = (string)$this->threadNo;
        $strThreadCount = (string)$this->threadCount;
        while (strlen($strThreadNo) < strlen($strThreadCount)) {
            $strThreadNo = '0' . $strThreadNo;
        }

        return preg_replace('#\.tsv$#', sprintf('_%s-%s.tsv', $strThreadNo, $strThreadCount), $filename);
    }
}
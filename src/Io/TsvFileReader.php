<?php

namespace Playtini\ConsolePack\Io;

class TsvFileReader
{
    /** @var string */
    private $filename;

    /** @var resource */
    private $file;

    /** @var array|null */
    private $keys = null;

    /** @var int */
    private $countKeys;

    /** @var int */
    private $rowNo;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function load(): array
    {
        $result = [];
        foreach ($this->items() as $item) {
            $result[] = $item;
        }

        return $result;
    }

    public function items()
    {
        $this->open();
        while (!feof($this->file)) {
            $cols = $this->loadRow();
            if ($cols !== null) {
                yield array_combine($this->keys, $cols);
            }
        }
        $this->close();
    }

    public function setKeys(array $keys = null): TsvFileReader
    {
        $this->keys = $keys;

        return $this;
    }

    private function loadRow(): ?array
    {
        $s = trim(fgets($this->file));
        if ($s === '') {
            return null;
        }
        $cols = explode("\t", $s);
        $countCols = count($cols);

        if ($this->keys === null) {
            $this->keys = $cols;
            if (isset($this->keys[0])) {
                $this->keys[0] = str_replace("\xEF\xBB\xBF", '', $this->keys[0]); // remove UTF BOM
            }
            $this->countKeys = $countCols;
            return null;
        }

        $this->rowNo++;
        if ($countCols > $this->countKeys) {
            throw new \InvalidArgumentException(sprintf('Row #%s has more columns than in header', $this->rowNo));
        }
        while ($countCols < $this->countKeys) {
            $cols[] = '';
            $countCols++;
        }

        return $cols;
    }

    private function open(): void
    {
        if (!is_file($this->filename)) {
            throw new \InvalidArgumentException(sprintf('Cannot find tsv file: "%s"', $this->filename));
        }

        $this->countKeys = ($this->keys !== null) ? count($this->keys) : 0;

        $this->file = fopen($this->filename, 'rb');
        $this->rowNo = 0;
    }

    private function close(): void
    {
        fclose($this->file);
        $this->keys = null;
    }
}

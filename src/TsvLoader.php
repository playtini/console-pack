<?php

namespace Playtini\ConsolePack;

class TsvLoader
{
    /** @var array */
    private $items;

    public function __construct(string $filename)
    {
        $this->items = $this->load($filename);
    }

    public function getItems(): array
    {
        return $this->items;
    }

    private function load(string $filename): array
    {
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(sprintf('Cannot find tsv file: "%s"', $filename));
        }

        $result = [];

        $keys = null;
        $countKeys = 0;

        $f = fopen($filename, 'rb');
        $rowNo = 0;
        while (!feof($f)) {
            $s = trim(fgets($f));
            if ($s === '') {
                continue;
            }
            $cols = explode("\t", $s);
            $countCols = count($cols);

            if ($keys === null) {
                $keys = $cols;
                if (isset($keys[0])) {
                    $keys[0] = str_replace("\xEF\xBB\xBF", '', $keys[0]); // remove UTF BOM
                }
                $countKeys = $countCols;
                continue;
            }

            $rowNo++;
            if ($countCols > $countKeys) {
                throw new \InvalidArgumentException(sprintf('Row #%s has more columns than in header', $rowNo));
            }
            while ($countCols < $countKeys) {
                $cols[] = '';
                $countCols++;
            }

            $result[] = array_combine($keys, $cols);
        }
        fclose($f);

        return $result;
    }
}

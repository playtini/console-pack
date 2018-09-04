<?php

namespace Playtini\ConsolePack\Repository;

use Playtini\ConsolePack\Io\FileLocator;
use Playtini\ConsolePack\Io\FileReader;

class TsvRepository
{
    /** @var bool */
    protected $isLoaded = false;

    /** @var array */
    protected $items = [];

    /** @var string */
    protected $filename = null;

    /**
     * @param string|null $filename
     * @throws \Exception
     */
    public function __construct(string $filename = null)
    {
        if ($filename !== null) {
            $this->filename = $filename;
        }
        $this->filename = FileLocator::findFileSomewhere($this->filename);
    }

    public function load()
    {
        if ($this->isLoaded) {
            return $this;
        }

        $reader = new FileReader($this->filename);
        foreach ($reader->lines() as $row) {
            [$key, $value] = $this->parseRow($row);
            $this->items[$this->normalizeKey($key)] = $value;
        }
        $this->isLoaded = true;
        unset($reader);

        return $this;
    }

    public function count(): int
    {
        $this->load();

        return count($this->items);
    }

    public function keys(): array
    {
        $this->load();

        return array_keys($this->items);
    }

    public function all(): array
    {
        $this->load();

        return $this->items;
    }

    protected function parseRow($row): array
    {
        return explode("\t", $row, 2);
    }

    protected function normalizeKey(string $key)
    {
        return mb_strtolower($key);
    }
}
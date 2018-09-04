<?php

namespace Playtini\ConsolePack\Logger;

class InfiniteProgressBar
{
    /** @var int */
    private $minorStep;

    /** @var int */
    private $majorStep;

    /** @var int */
    private $counter = 0;

    /**
     * @var callable|null
     */
    private $callback;

    public function __construct(int $majorStep = 1000, int $minorSteps = null, $callback = null)
    {
        $minorSteps = $minorSteps ?? 20;

        $this->majorStep = $majorStep;
        $this->minorStep = max(1, (int)round($majorStep / $minorSteps));
        $this->callback = $callback;

        $this->newline();
    }

    public function setCallback(?callable $callback): self
    {
        $this->callback = $callback;

        return $this;
    }

    public function inc(): void
    {
        $this->counter++;

        if ($this->counter % $this->minorStep === 0) {
            echo '.';
        }
        if ($this->counter % $this->majorStep === 0) {
            $this->newline();
        }
    }

    public function end(): void
    {
        $this->stat();
    }

    private function newline(): void
    {
        if ($this->counter) {
            $this->stat();
        }

        printf('[%s] ', date('Y-m-d H:i:s'));
    }

    private function stat(): void
    {
        echo ' ' . number_format($this->counter);
        if ($this->callback !== null) {
            echo ' ' . ($this->callback)();
        }
        echo "\n";
    }
}
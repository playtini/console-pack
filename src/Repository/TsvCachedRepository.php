<?php

namespace Playtini\ConsolePack\Repository;

class TsvCachedRepository extends TsvRepository
{
    public function load(): TsvCachedRepository
    {
        /** @var TsvCachedRepository $result */
        $result = parent::load();

        return $result;
    }

    public function get(string $key, $defaultValue = null): ?string
    {
        if (!$this->isLoaded) {
            $this->load();
        }

        static $cache = [];
        static $lastCacheCleared = 0;

        if (isset($cache[$key])) {
            return $cache[$key];
        }
        if (time() - $lastCacheCleared >= 60) {
            $lastCacheCleared = time();

            if (count($cache) >= 10000) {
                $cache = [];
            }
        }

        $result = $this->items[$this->normalizeKey($key)] ?? $defaultValue;

        $cache[$key] = $result;

        return $result;
    }
}
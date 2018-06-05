<?php

namespace Playtini\ConsolePack;

use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Browser
{
    /** @var null */
    private $cacheDirectory = null;

    public function __construct(string $cacheDirectory = null)
    {
        $this->cacheDirectory = $cacheDirectory;
    }

    public function get(string $url): ?string
    {
        $cache = new FilesystemAdapter('browser', 86400 * 7, __DIR__ . '/../../var/cache');

        $result = null;
        try {
            $cacheItem = $cache->getItem(md5($url));
        } catch (InvalidArgumentException $e) {
            return null;
        }
        $result = $cacheItem->get();
        if ($result === null) {
            $result = $this->doGet($url);
            $cacheItem->set($result);
            $cache->save($cacheItem);
        }
        if ($result === false || $result === null) {
            $result = null;
        }

        return $result;
    }

    /**
     * @param string $url
     * @return bool|string
     */
    private function doGet(string $url)
    {
        try {
            $result = file_get_contents($url);
        } catch (\Exception $e) {
            return null;
        }

        return $result;
    }
}

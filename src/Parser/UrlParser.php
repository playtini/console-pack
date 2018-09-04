<?php

namespace Playtini\ConsolePack\Parser;

class UrlParser
{
    /**
     * @param string $url http://example.com/page
     * @return string http://example.com
     */
    public static function extractHomepage(string $url): string
    {
        static $cache = [];
        static $lastCacheCleared = 0;

        if (isset($cache[$url])) {
            return $cache[$url];
        }
        if (time() - $lastCacheCleared >= 60) {
            $lastCacheCleared = time();

            if (count($cache) >= 10000) {
                $cache = [];
            }
        }

        $result = rtrim($url, '/');
        $result = preg_replace('#^((?:[a-z0-9]+://)?[^/]+).*#i', '$1', $result);

        $cache[$url] = $result;

        return $result;
    }

    /**
     * @param string $url http://www.example.com/page
     * @return string www.example.com
     */
    public static function extractDomain(string $url): string
    {
        static $cache = [];
        static $lastCacheCleared = 0;

        if (isset($cache[$url])) {
            return $cache[$url];
        }
        if (time() - $lastCacheCleared >= 60) {
            $lastCacheCleared = time();

            if (count($cache) >= 10000) {
                $cache = [];
            }
        }

        $url = self::extractHomepage($url);
        $domain = preg_replace('#^.*//#', '', $url);
        $domain = mb_strtolower($domain);

        $cache[$url] = $domain;

        return $domain;
    }

    /**
     * @param string $url http://www.example.com/page
     * @return string example.com
     */
    public static function extractDomainNoWww(string $url): string
    {
        $domain = self::extractDomain($url);
        $domain = preg_replace('#^www\.#', '', $domain);

        return $domain;
    }

    /**
     * @param string $url http://sub.sub.example.com/page
     * @return string example.com
     */
    public static function extractRegistrableDomain(string $url)
    {
        static $cache = [];
        static $lastCacheCleared = 0;

        $domainName = self::extractDomain($url);
        if (isset($cache[$domainName])) {
            return $cache[$domainName];
        }
        if (time() - $lastCacheCleared >= 60) {
            $lastCacheCleared = time();

            if (count($cache) >= 10000) {
                $cache = [];
            }
        }

        $manager = new \Pdp\Manager(new \Pdp\Cache(), new \Pdp\CurlHttpClient());
        $rules = $manager->getRules();

        $domain = $rules->resolve($domainName);

        $result = $domain->getRegistrableDomain();
        $cache[$domainName] = $result;

        return $result;
    }
}
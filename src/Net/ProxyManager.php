<?php

namespace Playtini\ConsolePack\Net;

class ProxyManager
{
    /** @var string[] */
    private $hosts;

    /** @var int */
    private $port;

    /** @var string|null */
    private $username;

    /** @var string|null */
    private $password;

    /** @var array host=>bannedTillTime */
    private $badHosts = [];

    /**
     * ProxyManager constructor.
     * @param array|string $hosts
     * @param int $port
     * @param string|null $username
     * @param string|null $password
     */
    public function __construct($hosts, int $port, string $username = null, string $password = null)
    {
        if (!is_array($hosts)) {
            $hosts = preg_replace('#[,\s]+#', ',', trim($hosts));
            $hosts = explode(',', $hosts);
        }
        $this->hosts = $hosts;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
    }

    public function banProxy($proxy, $seconds)
    {
        $host = $this->extractHost($proxy);
        $this->badHosts[$host] = time() + $seconds;
    }

    public function getProxy(): string
    {
        $host = $this->getProxyHost();

        $result = sprintf('http://%s:%s@%s:%s', $this->username, $this->password, $host, $this->port);
        $result = str_replace('//:@', '', $result);
        $result = str_replace(':@', '@', $result);

        return $result;
    }

    private function getProxyHost(): string
    {
        $infinity = 10;
        $host = $this->hosts[array_rand($this->hosts)];
        while ($infinity-->0) {
            if (!$this->isBadHost($host)) {
                break;
            }
            $host = $this->hosts[array_rand($this->hosts)];
        }

        return $host;
    }

    private function isBadHost($host)
    {
        if (!isset($this->badHosts[$host])) {
            return false;
        }

        if ($this->badHosts[$host] >= time()) {
            unset($this->badHosts[$host]);
            return false;
        }

        return true;
    }

    public function extractHost($proxy)
    {
        if (preg_match('#@(.+):#', $proxy, $m)) {
            return $m[1];
        }

        return $proxy;
    }
}

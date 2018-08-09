<?php

namespace Playtini\ConsolePack;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Cache\InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class Browser
{
    use LoggerAwareTrait;

    /** @var null */
    private $cacheDirectory = null;

    /** @var GuzzleClient */
    private $guzzleClient;

    /** @var CookieJar */
    private $cookieJar;

    /** @var string */
    private $proxy = null;

    /** @var int */
    private $timeout = 90;

    /** @var string */
    private $lastError = null;

    /** @var int */
    private $lastStatusCode;

    /** @var array|null */
    private $lastResponseHeaders = null;

    /** @var ProxyManager */
    private $proxyManager;

    /** @var array */
    private $defaultHeaders = [
        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        'Accept-Encoding' => 'gzip, deflate',
        'Accept-Language' => 'ru,en-US;q=0.7,en;q=0.3',
        'Cache-Control' => 'max-age=0',
        'Connection' => 'keep-alive',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:52.0) Gecko/20100101 Firefox/52.0',
    ];

    public function __construct(
        string $cacheDirectory = null,
        ProxyManager $proxyManager = null,
        LoggerInterface $logger = null
    ) {
        $this->logger = $logger ?? new NullLogger();
        $this->cacheDirectory = $cacheDirectory;
        $this->proxyManager = $proxyManager;
        $this->guzzleClient = new GuzzleClient();

        $this->changeProxy();
        $this->enableCookies();
    }

    public function get(string $url, array $options = []): ?string
    {
        return $this->send('get', $url, $options);
    }

    public function post(string $url, array $options = []): ?string
    {
        return $this->send('post', $url, $options);
    }

    private function send(string $method, string $url, array $options = []): ?string
    {
        if ($this->cacheDirectory) {
            $result = $this->sendCachable($method, $url, $options);
        } else {
            $result = $this->sendClient($method, $url, $options);
        }

        if ($result === null) {
            $this->lastResponseHeaders = null;
            $this->lastStatusCode = null;

            return $result;
        }

        $this->lastResponseHeaders = $this->compactHeaders($result['headers']);
        $this->lastStatusCode = $result['status_code'];

        return $result['html'];
    }

    public function banAndChangeProxy(): self
    {
        if (!$this->proxyManager) {
            return $this;
        }

        $this->proxyManager->banProxy($this->proxyManager->extractHost($this->proxy), 300);
        $this->changeProxy();

        return $this;
    }

    public function changeProxy(): self
    {
        if (!$this->proxyManager) {
            return $this;
        }

        $this->proxy = $this->proxyManager->getProxy();
        $this->logger->info('browser_proxy', ['proxy'=>$this->proxy]);

        return $this;
    }

    public function getProxyHost(): ?string
    {
        if (!$this->proxyManager) {
            return null;
        }

        return $this->proxyManager->extractHost($this->proxy);
    }

    public function enableCookies(): self
    {
        $this->cookieJar = new CookieJar();

        return $this;
    }

    public function disableCookies(): self
    {
        $this->cookieJar = null;

        return $this;
    }

    public function getDefaultHeaders(): array
    {
        return $this->defaultHeaders;
    }

    public function setDefaultHeaders(array $defaultHeaders): self
    {
        $this->defaultHeaders = $defaultHeaders;

        return $this;
    }

    public function getLastStatusCode(): int
    {
        return $this->lastStatusCode;
    }

    public function setLastStatusCode(int $lastStatusCode): Browser
    {
        $this->lastStatusCode = $lastStatusCode;

        return $this;
    }

    public function getLastResponseHeaders(): ?array
    {
        return $this->lastResponseHeaders;
    }

    public function setLastResponseHeaders(?array $lastResponseHeaders): Browser
    {
        $this->lastResponseHeaders = $lastResponseHeaders;

        return $this;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    private function sendCachable(string $method, string $url, array $options = []): ?array
    {
        $cache = new FilesystemAdapter('browser', 86400 * 7, $this->cacheDirectory);

        $result = null;
        try {
            $cacheItem = $cache->getItem(md5($url));
        } catch (InvalidArgumentException $e) {
            return null;
        }
        $result = $cacheItem->get();
        if ($result === null) {
            $result = $this->sendClient($method, $url, $options);
            $cacheItem->set($result);
            $cache->save($cacheItem);
        }

        return $result;
    }

    private function sendClient(string $method, string $url, array $options = []): ?array
    {
        $method = strtolower($method);

        try {
            if ($method === 'post') {
                $this->logger->info('browser_post', ['url' => $url, 'options' => $this->getOptions($options)]);
                $response = $this->guzzleClient->post($url, $this->getOptions($options));
            } else {
                $this->logger->info('browser_get', ['url' => $url, 'options' => $this->getOptions($options)]);
                $response = $this->guzzleClient->get($url, $this->getOptions($options));
            }
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
            return null;
        }
        $streamResponse = $response->getBody();
        if (!$streamResponse) {
            return null;
        }

        return [
            'status_code' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'html' => $streamResponse->__toString(),
        ];
    }

    private function getOptions(array $options = []): array
    {
        $default = [
            'headers' => $this->defaultHeaders,
            'form_params' => [],
            'timeout' => $this->timeout,
        ];
        if ($this->cookieJar) {
            $default['cookies'] = $this->cookieJar;
        }
        if ($this->proxy) {
            $default['proxy'] = $this->proxy;
        }

        return array_merge($default, $options);
    }

    private function compactHeaders(array $headers)
    {
        $result = [];

        foreach ($headers as $key => $values) {
            $result[$key] = implode("\n", $values);
        }

        return $result;
    }
}

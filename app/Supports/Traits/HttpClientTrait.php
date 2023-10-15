<?php

namespace App\Supports\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;

trait HttpClientTrait
{

    private $logChannel = 'default';
    private $closeBeforeRequestLog = false;
    private $closeAfterRespondLog = false;
    private $baseUri = '';
    private $url;
    private $headers = [];
    private $options = [];
    private $params = [];
    private $publicParams = [];
    /** @var Client */
    private $client = null;
    private $afterRespondHandle;
    private $beforeRequestHandle;

    public function post(string $url, array $data, bool $isJson = true, bool $async = false)
    {
        $this->url = ltrim($url, '/');
        $params = $data + $this->publicParams;
        $this->params = $isJson ? ['json' => $params] : ['form_params' => $params];

        if (is_callable($this->beforeRequestHandle)) {
            ($this->beforeRequestHandle) ($this->baseUri . $this->url, $this->getLogParams());
        }

        $this->setClient();
        $this->logBeforeRequest();

        return $async ? $this->postAsync() : $this->postSync();
    }

    public function get(string $url, array $query, bool $async = false)
    {
        $this->url = ltrim($url, '/');
        $params = $query + $this->publicParams;
        $this->params = ['query' => $params];

        if (is_callable($this->beforeRequestHandle)) {
            ($this->beforeRequestHandle) ($this->baseUri . $this->url, $this->getLogParams());
        }

        $this->setClient();
        $this->logBeforeRequest();

        return $async ? $this->getAsync() : $this->getSync();
    }

    public function delete(string $url, array $data, bool $async = false)
    {
        $this->url = ltrim($url, '/');
        $params = $data + $this->publicParams;
        $this->params = ['json' => $params];

        if (is_callable($this->beforeRequestHandle)) {
            ($this->beforeRequestHandle) ($this->baseUri . $this->url, $this->params);
        }

        $this->setClient();
        $this->logBeforeRequest();

        return $async ? $this->deleteAsync() : $this->deleteSync();
    }

    public function setBaseUri(string $baseUri)
    {
        $this->client = null;
        if (!Str::endsWith($baseUri, '/')) {
            $baseUri .= '/';
        }
        $this->baseUri = $baseUri;
    }

    public function setHeaders(array $headers)
    {
        $this->client = null;
        $this->headers = array_merge($this->headers, $headers);
    }

    public function setOptions(array $options)
    {
        $this->client = null;
        $this->options = array_merge($this->options, $options);
    }

    public function setPublicParams(array $publicParams)
    {
        $this->publicParams = $publicParams;
    }

    public function getPublicParams(): array
    {
        return $this->publicParams;
    }

    public function setLogChannel($logChannel)
    {
        $this->logChannel = $logChannel;
    }

    public function setBeforeRequestHandle(callable $callback)
    {
        $this->beforeRequestHandle = $callback;
    }

    public function setAfterRespondHandle(callable $callback)
    {
        $this->afterRespondHandle = $callback;
    }

    public function closeBeforeRequestLog()
    {
        $this->closeBeforeRequestLog = true;
    }

    public function closeAfterRespondLog()
    {
        $this->closeAfterRespondLog = true;
    }

    private function setClient(): void
    {
        if (!$this->client instanceof Client) {
            $this->client = new Client(
                [
                    'base_uri' => $this->baseUri,
                    'headers' => $this->headers,
                ] + $this->options
            );
        }
    }

    private function postSync()
    {
        try {
            $content = $this->client->post($this->url, $this->params)->getBody()->getContents();
            return $this->sync($content);
        } catch (RequestException $e) {
            $this->logError($e);
        }
    }

    private function postAsync(): bool
    {
        $promise = $this->client->postAsync($this->url, $this->params);
        $this->async($promise);
        return true;
    }

    private function getSync()
    {
        try {
            $content = $this->client->get($this->url, $this->params)->getBody()->getContents();
            return $this->sync($content);
        } catch (RequestException $e) {
            $this->logError($e);
        }
    }

    private function getAsync(): bool
    {
        $promise = $this->client->getAsync($this->url, $this->params);
        $this->async($promise);
        return true;
    }

    private function deleteSync()
    {
        try {
            $content = $this->client->delete($this->url, $this->params)->getBody()->getContents();
            return $this->sync($content);
        } catch (RequestException $e) {
            $this->logError($e);
        }
    }

    private function deleteAsync(): bool
    {
        $promise = $this->client->getAsync($this->url, $this->params);
        $this->async($promise);
        return true;
    }

    private function sync($content)
    {
        $content = json_decode($content, true);

        if (is_callable($this->afterRespondHandle)) {
            ($this->afterRespondHandle) ($this->baseUri . $this->url, $this->getLogParams(), $content);
        }

        $this->logAfterRequest($content);

        return $content;
    }

    private function async($promise)
    {
        $promise->then(
            function (ResponseInterface $res) {
                $content = $res->getBody()->getContents();
                $content = json_decode($content, true);
                if (is_callable($this->afterRespondHandle)) {
                    ($this->afterRespondHandle) ($this->baseUri . $this->url, $this->getLogParams(), $content);
                }

                $this->logAfterRequest($content);

                return $content;
            },

            function (RequestException $e) {
                $this->logError($e, true);
            }
        )->wait();
    }

    private function logBeforeRequest()
    {
        if (!$this->closeBeforeRequestLog) {
            $msg = [
                'url' => $this->baseUri . $this->url,
                'headers' => $this->headers,
                'params' => $this->getLogParams(),
            ];
            $this->getLogDriver()->info("{$this->logChannel}_request_info", $msg);
        }
    }

    private function logAfterRequest($content)
    {
        if (!$this->closeAfterRespondLog) {
            $msg = [
                'url' => $this->baseUri . $this->url,
                'headers' => $this->headers,
                'params' => $this->getLogParams(),
                'content' => $content,
            ];
            $this->getLogDriver()->info("{$this->logChannel}_respond_info", $msg);
        }
    }

    private function logError(RequestException $e, $async = false)
    {
        $msg = ['data' => ['url' => $this->baseUri . $this->url, 'query' => $this->params], 'msg' => $e->getMessage()];
        $this->getLogDriver()->error("{$this->logChannel}_request_error", $msg);
    }

    private function getLogParams()
    {
        return $this->params['json'] ?? $this->params['form_params'] ?? $this->params['query'] ?? [];
    }

    private function getLogDriver()
    {
        return Log::channel($this->logChannel);
    }
}

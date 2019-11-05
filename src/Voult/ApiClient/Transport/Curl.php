<?php

namespace Voult\ApiClient\Transport;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Voult\ApiClient\Exception\TransportException;
use Voult\ApiClient\Psr7\Response;
use Voult\ApiClient\Psr7\Stream;
use Voult\ApiClient\Exception\InvalidArgumentException;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
class Curl extends AbstractTransport
{
    /**
     * cURL resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * Curl constructor
     *
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        $this->init();

        if (!empty($headers)) {
            $this->setHeaders($headers);
        }
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws TransportException
     * @throws InvalidArgumentException
     */
    public function request(RequestInterface $request): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->__toString();
        $headers = $request->getHeaders();
        $requestBody = $request->getBody()->getContents();

        $method = strtoupper($method);

        if (!is_resource($this->resource)) {
            $this->reset();
        }

        $this->setResponseStream();
        $this->setMethod($method);
        $this->setHeaders($headers);
        $this->appendHeaders();
        $this->setRequestBody($requestBody);
        $this->setOption(CURLOPT_URL, $uri);
        $this->appendOptions();

        $this->execute();

        $curlErrno = curl_errno($this->resource);

        if (CURLE_OK !== $curlErrno) {
            $curlError = curl_error($this->resource);

            if (empty($curlError)) {
                $curlError = curl_strerror($curlErrno);
            }

            throw new TransportException($curlError . ' (cURL errno: ' . $curlErrno . ')', $curlErrno);
        }

        $this->setResponseHeaders();
        $this->close();

        return $this->response;
    }

    /**
     * @param int $option
     * @param mixed $value
     */
    public function setOption(int $option, $value): void
    {
        $this->options[ $option ] = $value;
    }

    protected function appendOptions(): void
    {
        $this->checkDefaultOptions();
        
        if (!empty($this->options)) {
            foreach ($this->options as $option => $value) {
                curl_setopt($this->resource, $option, $value);
            }
        }
    }

    /**
     * @return void
     * @throws InvalidArgumentException
     */
    protected function setResponseStream(): void
    {
        $fp = fopen('php://temp', 'wb+');

        $stream = new Stream;
        $stream->setStream($fp, 'wb+');

        $this->setOption(CURLOPT_FILE, $fp);
        $this->response = $this->response->withBody($stream);
    }

    /**
     * @param $curlHandler
     * @param $data
     * @return int
     */
    protected function streamWrite($curlHandler, $data): int
    {
        return $this->response->getBody()->write($data);
    }

    /**
     * Init cURL connection
     */
    protected function init(): void
    {
        $this->resource = curl_init();
        $this->response = new Response;
    }

    /**
     * @return void
     */
    protected function execute(): void
    {
        curl_exec($this->resource);
    }

    /**
     * Close cURL connection and free resource
     */
    protected function close(): void
    {
        curl_close($this->resource);
    }

    /**
     * @return resource
     */
    protected function reset()
    {
        if (is_resource($this->resource)) {
            $this->close();
            $this->options = [];
        }

        $this->resource = curl_init();

        return $this->resource;
    }

    /**
     * @param array $headers
     */
    protected function setHeaders(array $headers): void
    {
        if (empty($headers)) {
            return;
        }

        foreach ($headers as $headerName => $headerValue) {
            $value = (is_array($headerValue) ? implode(', ', $headerValue) : (string)$headerValue);
            $this->headers[$headerName] = sprintf('%s: %s', $headerName, $value);
        }
    }

    /**
     * @return void
     */
    protected function appendHeaders(): void
    {
        if (!is_resource($this->resource) || empty($this->headers)) {
            return;
        }

        $this->setOption(CURLOPT_HTTPHEADER, $this->headers);
    }

    /**
     * @return void
     */
    protected function setResponseHeaders(): void
    {
        if (!is_resource($this->resource)) {
            return;
        }

        $headersInfo = $this->getResponseInfo();
        $headerSize = $this->getResponseInfo(CURLINFO_HEADER_SIZE);

        if (!isset($headerSize[ 0 ])) {
            return;
        }

        $parsedHeaders = $this->parseResponseHeaders($headersInfo);

        if (!empty($parsedHeaders)) {
            foreach ($parsedHeaders as $headerName => $headerValue) {
                if ('Status' === $headerName) {
                    $this->response = $this->response->withStatus($headerValue);
                }

                $this->response = $this->response->withHeader($headerName, $headerValue);
            }
        }
    }

    /**
     * @param array $headersInfo
     * @return array
     */
    protected function parseResponseHeaders(array $headersInfo = []): array
    {
        $parsedList = [];
        $parsedList[ 'Content-Length' ] = $this->response->getBody()->getSize();

        if (array_key_exists('http_code', $headersInfo)) {
            $parsedList[ 'Status' ] = $headersInfo[ 'http_code' ];
        }

        if (array_key_exists('content_type', $headersInfo)) {
            $parsedList[ 'Content-Type' ] = $headersInfo[ 'content_type' ];
        }

        foreach ($headersInfo as $key => $value) {
            if (empty($value)) {
                continue;
            }

            $header = explode(':', $value, 2);

            if (empty($header[ 1 ])) {
                continue;
            }

            $parsedList[ $header[ 0 ] ] = trim($header[ 1 ]);
        }

        return $parsedList;
    }

    /**
     * @param null $option
     * @return array
     */
    protected function getResponseInfo($option = null): array
    {
        if (!is_resource($this->resource)) {
            return [];
        }

        if (null !== $option) {
            return (array)curl_getinfo($this->resource, $option);
        }

        return (array)curl_getinfo($this->resource);
    }

    protected function checkDefaultOptions(): void
    {
        if (!array_key_exists(CURLOPT_RETURNTRANSFER, $this->options)) {
            $this->setOption(CURLOPT_RETURNTRANSFER, true);
        }

        if (!array_key_exists(CURLOPT_BINARYTRANSFER, $this->options)) {
            $this->setOption(CURLOPT_BINARYTRANSFER, true);
        }

        if (!array_key_exists(CURLOPT_VERBOSE, $this->options)) {
            $this->setOption(CURLOPT_VERBOSE, false);
        }

        if (!array_key_exists(CURLOPT_WRITEFUNCTION, $this->options)) {
            $this->setOption(CURLOPT_WRITEFUNCTION, function ($ch, $data) {
                return $this->streamWrite($ch, $data);
            });
        }

        if (!array_key_exists(CURLOPT_HEADER, $this->options)) {
            $this->setOption(CURLOPT_HEADER, false);
        }

        if (!array_key_exists(CURLOPT_CONNECTTIMEOUT, $this->options)) {
            $this->setOption(CURLOPT_CONNECTTIMEOUT, 30);
        }

        if (!array_key_exists(CURLOPT_TIMEOUT, $this->options)) {
            $this->setOption(CURLOPT_TIMEOUT, 30);
        }

        if (!array_key_exists(CURLOPT_HTTP_VERSION, $this->options)) {
            $this->setOption(CURLOPT_HTTP_VERSION, 1.1);
        }

        if (!array_key_exists(CURLOPT_FOLLOWLOCATION, $this->options)) {
            $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        }

        if (!array_key_exists(CURLOPT_MAXREDIRS, $this->options)) {
            $this->setOption(CURLOPT_MAXREDIRS, 5);
        }

        ////For debug
        //if (!array_key_exists(CURLINFO_HEADER_OUT, $this->options)) {
        //    $this->setOption(CURLINFO_HEADER_OUT, true);
        //}
    }

    /**
     * @param mixed|null $requestBody
     */
    protected function setRequestBody($requestBody = null): void
    {
        if (!empty($requestBody)) {
            curl_setopt($this->resource, CURLOPT_POSTFIELDS, $requestBody);
        }
    }

    /**
     * @param string $method
     * @throws TransportException
     */
    protected function setMethod(string $method): void
    {
        parent::setMethod($method);

        switch ($method) {
            case RequestMethodInterface::METHOD_HEAD:
                curl_setopt($this->resource, CURLOPT_NOBODY, true);
                break;
            case RequestMethodInterface::METHOD_GET:
                curl_setopt($this->resource, CURLOPT_HTTPGET, true);
                break;
            case RequestMethodInterface::METHOD_POST:
                curl_setopt($this->resource, CURLOPT_POST, true);
                curl_setopt($this->resource, CURLOPT_CUSTOMREQUEST, RequestMethodInterface::METHOD_POST);
                break;
            default:
                curl_setopt($this->resource, CURLOPT_CUSTOMREQUEST, $method);
        }
    }
}

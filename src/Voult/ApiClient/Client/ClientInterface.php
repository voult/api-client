<?php

namespace Voult\ApiClient\Client;

use Voult\ApiClient\Transport\TransportInterface;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
interface ClientInterface
{
    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface;

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport): void;

    /**
     * @param string $contentType
     * @return void
     */
    public function setTransportContentType(string $contentType): void;

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @param string $host
     */
    public function setHost(string $host): void;

    /**
     * @return array
     */
    public function getRequestHeaders(): array;

    /**
     * @param string $headerName
     * @param string $headerValue
     */
    public function addRequestHeader(string $headerName, string $headerValue): void;

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return array
     */
    public function call(string $method, string $uri, array $params = []): array;
}

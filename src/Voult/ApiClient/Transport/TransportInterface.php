<?php

namespace Voult\ApiClient\Transport;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
interface TransportInterface
{
    /**
     * Do the request and return response as a string
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function request(RequestInterface $request): ResponseInterface;

    /**
     * Returns raw response from last request, or empty string if no request was done
     *
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface;
}

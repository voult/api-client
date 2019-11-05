<?php

namespace Voult\ApiClient\Psr7;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * @author [2019-10-22] Edmund A. Pacha <voults@gmail.com>
 */
class Request extends AbstractMessage implements RequestInterface
{
    /**
     * @var string
     */
    protected $method = 'GET';

    /**
     * @var null|string
     */
    protected $requestTarget;

    /**
     * @var UriInterface
     */
    private $uri;

    /**
     * @return string
     */
    public function getRequestTarget(): string
    {
        if (null !== $this->requestTarget) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath();
        if ($this->uri->getQuery()) {
            $target .= '?' . $this->uri->getQuery();
        }

        if (empty($target)) {
            $target = '/';
        }

        return $target;
    }

    /**
     * @param mixed $requestTarget
     * @return RequestInterface
     */
    public function withRequestTarget($requestTarget): RequestInterface
    {
        $request = clone $this;
        $request->requestTarget = $requestTarget;

        return $request;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return RequestInterface
     */
    public function withMethod($method): RequestInterface
    {
        $request = clone $this;
        $request->method = $method;

        return $request;
    }

    /**
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return RequestInterface
     */
    public function withUri(UriInterface $uri, $preserveHost = false): RequestInterface
    {
        $request = clone $this;
        $request->uri = $uri;

        if ($preserveHost && $this->hasHeader('Host')) {
            return $request;
        }

        if (!$uri->getHost()) {
            return $request;
        }

        $host = $uri->getHost();

        if ($uri->getPort()) {
            $host .= ':' . $uri->getPort();
        }

        $request->headers['Host'] = $host;

        return $request;
    }
}


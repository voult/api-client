<?php

namespace Voult\ApiClient\Psr7;

use Psr\Http\Message\UriInterface;
use Voult\ApiClient\Exception\InvalidArgumentException;

/**
 * @author [2019-10-22] Edmund A. Pacha <voults@gmail.com>
 */
class Uri implements UriInterface
{
    /**
     * @var string
     */
    protected $scheme = '';

    /**
     * @var string
     */
    protected $userInfo = '';

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var string
     */
    protected $port = '';

    /**
     * @var string
     */
    protected $path = '';

    /**
     * @var string
     */
    protected $query = '';

    /**
     * @var string
     */
    protected $fragment = '';

    /**
     * Uri constructor.
     *
     * @param string $uri
     * @throws InvalidArgumentException
     */
    public function __construct($uri = '')
    {
        if ('' !== $uri) {
            $this->setUri($uri);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getUri();
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        $uri = '';

        if ('' !== $this->scheme) {
            $uri .= $this->scheme . ':';
        }

        $authority = $this->getAuthority();

        if ('' !== $authority) {
            $uri .= '//' . $authority;
        }

        if ('' !== $this->path) {
            $uri .= $this->path;
        }

        if ('' !== $this->query) {
            $uri .= '?' . $this->query;
        }

        if ('' !== $this->fragment) {
            $uri .= '#' . $this->fragment;
        }

        return $uri;
    }

    /**
     * @param string $uri
     * @return void
     * @throws InvalidArgumentException
     */
    public function setUri(string $uri): void
    {
        $parts = parse_url($uri);

        if (false === $parts) {
            throw new InvalidArgumentException('"%s" is not valid URI string');
        }

        $this->scheme = $parts[ 'scheme' ] ?? '';
        $this->userInfo = $parts[ 'user' ] ?? '';
        $this->host = $parts[ 'host' ] ?? '';
        $this->port = $parts[ 'port' ] ?? '';
        $this->path = $parts[ 'path' ] ?? '';
        $this->query = $parts[ 'query' ] ?? '';
        $this->fragment = $parts[ 'fragment' ] ?? '';

        if (isset($parts[ 'pass' ])) {
            $this->userInfo .= ':' . $parts[ 'pass' ];
        }
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority(): string
    {
        if ('' === $this->host) {
            return '';
        }

        $authority = $this->host;

        if ('' !== $this->userInfo) {
            $authority = $this->userInfo . '@' . $authority;
        }

        if ('' !== $this->port) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo(): string
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery(): string
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment(): string
    {
        return $this->fragment;
    }

    /**
     * @param string $scheme
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withScheme($scheme): UriInterface
    {
        $scheme = preg_replace('|[^a-z]?|', '', strtolower($scheme));

        if ('' === $scheme || !in_array($scheme, [ 'http', 'https' ])) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid scheme', $scheme));
        }

        $uri = clone $this;
        $uri->scheme = $scheme;

        return $uri;
    }

    /**
     * @param string $user
     * @param null $password
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withUserInfo($user, $password = null): UriInterface
    {
        if (!is_string($user)) {
            throw new InvalidArgumentException(sprintf('User "%s" is not a string', $user));
        }

        $userInfo = $user;

        if ('' === $password) {
            if (!is_string($password)) {
                throw new InvalidArgumentException(sprintf('Password "%s" is not a string', $password));
            }

            $userInfo .= ':' . $userInfo;
        }

        $uri = clone $this;
        $uri->userInfo = $userInfo;

        return $uri;
    }

    /**
     * @param string $host
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withHost($host): UriInterface
    {
        if (!is_string($host)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a string', $host));
        }

        $uri = clone $this;
        $uri->host = (string)$host;

        return $uri;
    }

    /**
     * @param int|null $port
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withPort($port): UriInterface
    {
        if (!is_string($port)) {
            throw new InvalidArgumentException(sprintf('"%s" is not an integer', $port));
        }

        $port = (int)$port;

        if ($port < 1 || $port > 65535) {
            throw new InvalidArgumentException(sprintf('"%d" is not valid TCP/UDP port', $port));
        }

        $uri = clone $this;
        $uri->port = $port;

        return $uri;
    }

    /**
     * @param string $path
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withPath($path): UriInterface
    {
        if (!is_string($path)) {
            throw new InvalidArgumentException(sprintf('Path "%s" is not a string', $path));
        }

        $pathArray1 = explode('?', (string)$path, 2);

        if (isset($pathArray1[ 1 ])) {
            throw new InvalidArgumentException(sprintf('Path "%s" must not contain valid query string', $path));
        }

        $pathArray2 = explode('#', (string)$path, 2);

        if (isset($pathArray2[ 1 ])) {
            throw new InvalidArgumentException(sprintf('Path "%s" must not contain hash (#) fragment', $path));
        }

        $uri = clone $this;
        $uri->path = $path;

        return $uri;
    }

    /**
     * @param string $query
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withQuery($query): UriInterface
    {
        if (!is_string($query)) {
            throw new InvalidArgumentException(sprintf('Query "%s" is not a string', $query));
        }

        $queryArray1 = explode('?', (string)$query, 2);

        if (!isset($queryArray1[ 1 ])) {
            throw new InvalidArgumentException(sprintf('Path "%s" does not contain necessary query string', $query));
        }

        $queryArray2 = explode('#', (string)$query, 2);

        if (isset($queryArray2[ 1 ])) {
            throw new InvalidArgumentException(sprintf('Path "%s" must not contain hash (#) fragment', $query));
        }

        $uri = clone $this;
        $uri->query = $queryArray1[ 1 ];

        return $uri;
    }

    /**
     * @param string $fragment
     * @return UriInterface
     * @throws InvalidArgumentException
     */
    public function withFragment($fragment): UriInterface
    {
        if (!is_string($fragment)) {
            throw new InvalidArgumentException(sprintf('Fragment "%s" is not a string', $fragment));
        }

        $fragmentArray = explode('#', (string)$fragment, 2);

        if (!isset($fragmentArray[ 1 ])) {
            throw new InvalidArgumentException(
                sprintf('Path "%s" does not contain necessary hash (#) fragment', $fragment)
            );
        }

        $uri = clone $this;
        $uri->fragment = $fragmentArray[ 1 ];

        return $uri;
    }
}

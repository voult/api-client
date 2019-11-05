<?php

namespace Voult\ApiClient\Psr7;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * @author [2019-10-22] Edmund A. Pacha <voults@gmail.com>
 */
abstract class AbstractMessage implements MessageInterface, StatusCodeInterface
{
    /**
     * @var string
     */
    protected $protocolVersion = '1.1';

    /**
     * @var array
     */
    protected $headers = [];

    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * @return string
     */
    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    /**
     * @param string $version
     * @return static
     */
    public function withProtocolVersion($version): MessageInterface
    {
        $message = clone $this;
        $message->protocolVersion = (string)$version;

        return $message;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name): bool
    {
        return array_key_exists($name, $this->headers);
    }

    /**
     * @param string $name
     * @return string[]
     */
    public function getHeader($name): array
    {
        return $this->headers[$name] ?? [];
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name): string
    {
        $header = $this->headers[$name];

        if (empty($header)) {
            return '';
        }

        if (!is_array($header)) {
            return $header;
        }

        return implode(',', $header);
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withHeader($name, $value): MessageInterface
    {
        $message = clone $this;

        if (is_array($value) || is_string($value)) {
            $message->headers[(string)$name] = $value;
        }

        return $message;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withAddedHeader($name, $value): MessageInterface
    {
        $message = clone $this;

        if (!$this->hasHeader($name)) {
            return $this->withHeader($name, $value);
        }

        $message->headers[$name] = array_merge($message->headers[$name], $value);

        return $message;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name): MessageInterface
    {
        $message = clone $this;

        if ($message->hasHeader($name)) {
            unset($message->headers[$name]);
        }

        return $message;
    }

    /**
     * @return StreamInterface
     */
    public function getBody(): StreamInterface
    {
        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body): MessageInterface
    {
        $message = clone $this;
        $message->stream = $body;

        return $message;
    }
}

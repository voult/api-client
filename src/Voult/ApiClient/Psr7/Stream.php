<?php

namespace Voult\ApiClient\Psr7;

use Psr\Http\Message\StreamInterface;
use RuntimeException;
use Voult\ApiClient\Exception\InvalidArgumentException;
use Voult\ApiClient\Exception\StreamException;

/**
 * @author [2019-10-22] Edmund A. Pacha <voults@gmail.com>
 */
class Stream implements StreamInterface
{
    /**
     * @var resource
     */
    protected $resource;

    /**
     * Stream constructor.
     *
     * @param string $stream
     * @param string $mode
     * @throws InvalidArgumentException
     */
    public function __construct($stream = '', $mode = '')
    {
        if ('' !== $stream && '' !== $mode) {
            $this->setStream($stream, $mode);
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        if (!$this->isReadable()) {
            return '';
        }

        try {
            return $this->getContents();
        } catch (RuntimeException $e) {
            return '';
        }
    }

    /**
     * return void
     */
    public function close(): void
    {
        if (!$this->resource) {
            return;
        }

        $resource = $this->detach();

        fclose($resource);
    }

    /**
     * @return resource|null
     */
    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        if (null === $this->resource) {
            return null;
        }

        $stats = fstat($this->resource);

        if ($stats !== false) {
            return $stats[ 'size' ];
        }

        return null;
    }

    /**
     * @return int
     * @throws StreamException
     */
    public function tell(): int
    {
        if (null === $this->resource) {
            throw new StreamException('Missing resource');
        }

        $result = ftell($this->resource);

        if (!is_int($result)) {
            throw new StreamException('Cannot get resource pointer');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function eof(): bool
    {
        if (null === $this->resource) {
            return true;
        }

        return feof($this->resource);
    }

    /**
     * @return bool
     */
    public function isSeekable(): bool
    {
        if (null === $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        if (!isset($meta[ 'seekable' ])) {
            return false;
        }

        return $meta[ 'seekable' ];
    }

    /**
     * @param int $offset
     * @param int $whence
     * @throws StreamException
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        if (null === $this->resource) {
            throw new StreamException('Missing resource');
        }

        if (!$this->isSeekable()) {
            throw new StreamException('Stream is not seekable');
        }

        $result = fseek($this->resource, $offset, $whence);

        if (0 !== $result) {
            throw new StreamException('Error seeking within stream');
        }
    }

    /**
     * @return void
     * @throws StreamException
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        if (null === $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        if (!isset($meta[ 'mode' ])) {
            return false;
        }

        $mode = $meta[ 'mode' ];

        return (
            strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+')
        );
    }

    /**
     * @param string $string
     * @return int
     * @throws StreamException
     */
    public function write($string): int
    {
        if (null === $this->resource) {
            throw new StreamException('Missing resource');
        }

        if (!$this->isWritable()) {
            throw new StreamException('Stream is not writable');
        }

        $result = fwrite($this->resource, $string);

        if (false === $result) {
            throw new StreamException('Error during writing to stream');
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        if (null === $this->resource) {
            return false;
        }

        $meta = stream_get_meta_data($this->resource);

        if (!isset($meta[ 'mode' ])) {
            return false;
        }

        $mode = $meta[ 'mode' ];

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * @param int $length
     * @return string
     * @throws StreamException
     */
    public function read($length): string
    {
        if (null === $this->resource) {
            throw new StreamException('Missing resource');
        }

        if (!$this->isReadable()) {
            throw new StreamException('Stream is not readable');
        }

        $result = fread($this->resource, $length);

        if (false === $result) {
            throw new StreamException('Cannot read from stream');
        }

        return $result;
    }

    /**
     * @return string
     * @throws StreamException
     */
    public function getContents(): string
    {
        if (!$this->isReadable()) {
            throw new StreamException('Stream is not readable');
        }

        if ($this->isSeekable()) {
            $this->rewind();
        }

        $result = stream_get_contents($this->resource);

        if (false === $result) {
            throw new StreamException('Cannot read from stream');
        }

        return $result;
    }

    /**
     * @param null $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $metadata = stream_get_meta_data($this->resource);

        if (null === $key) {
            return $metadata;
        }

        if (!array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[ $key ];
    }

    /**
     * @param string|resource $stream
     * @param string $mode
     * @throws InvalidArgumentException
     */
    public function setStream($stream, string $mode = 'r'): void
    {
        $resource = $stream;

        if (is_string($stream)) {
            $resource = @fopen($stream, $mode);

            if (!is_resource($resource)) {
                throw new InvalidArgumentException('Invalid stream provided');
            }
        }

        if (!is_resource($resource) || 'stream' !== get_resource_type($resource)) {
            throw new InvalidArgumentException('Invalid stream provided');
        }

        $this->resource = $resource;
    }
}

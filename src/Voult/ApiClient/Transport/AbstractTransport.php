<?php

namespace Voult\ApiClient\Transport;

use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\ResponseInterface;
use Voult\ApiClient\Exception\TransportException;
use ReflectionClassConstant;
use ReflectionException;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
abstract class AbstractTransport implements TransportInterface
{
    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * {@inheritDoc}
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * Check if method is supported
     *
     * @param string $method
     * @throws TransportException
     */
    protected function setMethod(string $method): void
    {
        $const = 'METHOD_' . $method;

        try {
            new ReflectionClassConstant(RequestMethodInterface::class, $const);
        } catch (ReflectionException $e) {
            throw new TransportException(sprintf('Method %s is unsupported', $method));
        }
    }

    /**
     * Close connection and free the resource
     */
    abstract protected function close(): void;

    /**
     * Close connection and free the resource
     */
    abstract protected function reset();
}


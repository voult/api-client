<?php

namespace Voult\ApiClient\Psr7;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;
use Voult\ApiClient\Exception\InvalidArgumentException;
use ReflectionClass;
use ReflectionException;

/**
 * @author [2019-10-22] Edmund A. Pacha <voults@gmail.com>
 */
class Response extends AbstractMessage implements ResponseInterface
{
    /**
     * @var string
     */
    protected $reasonPhrase;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return (int)$this->statusCode;
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function withStatus($code, $reasonPhrase = ''): ResponseInterface
    {
        $this->assertStatusCode($code);
        $response = clone $this;
        $response->statusCode = $code;

        if (!empty($reasonPhrase)) {
            $response->reasonPhrase = (string)$reasonPhrase;
        }

        return $response;
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }

    /**
     * @param int $statusCode
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    protected function assertStatusCode(int $statusCode): void
    {
        $class = new ReflectionClass(StatusCodeInterface::class);
        $constants = $class->getConstants();

        if (!in_array($statusCode, $constants)) {
            throw new InvalidArgumentException(sprintf('"%s" is not a valid HTTP status code', $statusCode));
        }
    }
}

<?php

namespace Voult\ApiClient\Client;

use Fig\Http\Message\StatusCodeInterface;
use Voult\ApiClient\Exception\ClientException;
use Voult\ApiClient\Exception\InvalidArgumentException;
use Voult\ApiClient\Exception\TransportException;
use Voult\ApiClient\Psr7\Request;
use Voult\ApiClient\Psr7\Stream;
use Voult\ApiClient\Psr7\Uri;
use Voult\ApiClient\Transport\Curl;
use Voult\ApiClient\Transport\TransportInterface;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
abstract class AbstractClient implements ClientInterface
{
    /**
     * @var TransportInterface
     */
    protected $transport;

    /**
     * @var string
     */
    protected $host = '';

    /**
     * @var array
     */
    protected $requestHeaders = [];

    /**
     * Client constructor.
     *
     * @param TransportInterface|null $transport
     * @param string $host
     * @param string $user
     * @param string $password
     */
    public function __construct(
        TransportInterface $transport = null,
        string $host = '',
        string $user = '',
        string $password = ''
    ) {
        if (null === $transport) {
            $transport = new Curl;
        }

        $this->transport = $transport;

        $this->setTransportContentType('application/json');

        if ('' !== $host) {
            $this->setHost($host);
        }
        if ('' !== $user) {
            $this->setTransportAuthorization($user, $password);
        }
    }

    /**
     * @param string $user
     * @param string $password
     * @return void
     */
    public function setTransportAuthorization(string $user, string $password): void
    {
        $this->addRequestHeader(
            'Authorization',
            'Basic ' . base64_encode($user . ':' . $password)
        );
    }

    /**
     * @param string $contentType
     * @return void
     */
    public function setTransportContentType(string $contentType): void
    {
        $this->addRequestHeader('Content-Type', $contentType);
    }

    /**
     * @return TransportInterface
     */
    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    /**
     * @param TransportInterface $transport
     */
    public function setTransport(TransportInterface $transport): void
    {
        $this->transport = $transport;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    /**
     * @return array
     */
    public function getRequestHeaders(): array
    {
        return $this->requestHeaders;
    }

    /**
     * @param string $headerName
     * @param string $headerValue
     */
    public function addRequestHeader(string $headerName, string $headerValue): void
    {
        $this->requestHeaders[ $headerName ] = $headerValue;
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $params
     * @return array
     * @throws InvalidArgumentException
     * @throws TransportException
     * @throws ClientException
     */
    public function call(string $method, string $uri, array $params = []): array
    {
        $uriObject = new Uri;
        $uriObject->setUri($this->getHost() . $uri);

        $request = new Request;
        $request = $request->withMethod($method);
        $request = $request->withUri($uriObject);

        if (!empty($this->requestHeaders)) {
            foreach ($this->requestHeaders as $headerName => $headerValue) {
                $request = $request->withHeader($headerName, $headerValue);
            }
        }

        $body = new Stream('php://temp', 'wb+');

        if (!empty($params)) {
            $body->write(json_encode($params));
        }

        $request = $request->withBody($body);
        $response = $this->transport->request($request);

        if (StatusCodeInterface::STATUS_UNAUTHORIZED === $response->getStatusCode()) {
            throw new ClientException('Unauthorized API access');
        }

        $results = (array)json_decode($response->getBody()->getContents(), true);

        return $results;
    }
}

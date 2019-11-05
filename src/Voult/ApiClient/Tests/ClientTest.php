<?php

namespace Voult\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Voult\ApiClient\Client;
use Voult\ApiClient\Transport\Curl;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
class ClientTest extends TestCase
{
    public function testClientInit(): void
    {
        $client = new Client;

        $this->assertInstanceOf(Client::class, $client);
        $this->assertInstanceOf(Curl::class, $client->getTransport());
    }
}

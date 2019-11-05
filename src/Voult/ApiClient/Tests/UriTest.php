<?php

namespace Voult\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Voult\ApiClient\Exception\InvalidArgumentException;
use Voult\ApiClient\Psr7\Uri;

/**
 * @author [2019-10-23] Edmund A. Pacha <voults@gmail.com>
 */
class UriTest extends TestCase
{
    public function testUri()
    {
        $address =
            'https://user:password@localhost:9210/api/?query=true#get-all';

        $uri = new Uri;
        $uri->setUri($address);

        $this->assertEquals('https', $uri->getScheme());
        $this->assertEquals('localhost', $uri->getHost());
        $this->assertEquals('9210', $uri->getPort());
        $this->assertEquals('user:password', $uri->getUserInfo());
        $this->assertEquals('/api/', $uri->getPath());
        $this->assertEquals('query=true', $uri->getQuery());
        $this->assertEquals('get-all', $uri->getFragment());
        $this->assertEquals('user:password@localhost:9210', $uri->getAuthority());
        $this->assertEquals($address, $uri->getUri());
    }

    public function testUriWrongAddress()
    {
        //$this->expectException(InvalidArgumentException::class);

        $uri = new Uri;

        try {
            $uri->withHost(null);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(sprintf('"%s" is not a string', null), $e->getMessage());
        }

        try {
            $uri->withPort(-1);
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(sprintf('"%s" is not an integer', -1), $e->getMessage());
        }

        try {
            $uri->withScheme('ftp://');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(sprintf('"%s" is not a valid scheme', 'ftp'), $e->getMessage());
        }

        try {
            $uri->withPath('?query=true');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                sprintf('Path "%s" must not contain valid query string', '?query=true'),
                $e->getMessage()
            );
        }

        try {
            $uri->withQuery('/api_docs/');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                sprintf('Path "%s" does not contain necessary query string', '/api_docs/'),
                $e->getMessage()
            );
        }

        try {
            $uri->withFragment('/api_docs/');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals(
                sprintf('Path "%s" does not contain necessary hash (#) fragment', '/api_docs/'),
                $e->getMessage()
            );
        }
    }
}

<?php

namespace Voult\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Voult\ApiClient\Exception\TransportException;
use Voult\ApiClient\Psr7\Request;
use Voult\ApiClient\Psr7\Response;
use Voult\ApiClient\Psr7\Stream;
use Voult\ApiClient\Psr7\Uri;
use Voult\ApiClient\Transport\Curl;

/**
 * @author [2019-10-21] Edmund A. Pacha <voults@gmail.com>
 */
class TransportTest extends TestCase
{
    public function testTransport(): void
    {
        $request = new Request;
        $request = $request->withMethod('GET');
        $request = $request->withUri(new Uri('http://google.com'));
        $request = $request->withBody(new Stream('php://temp', 'wb+'));

        $transport = new Curl;
        $transport->setOption(CURLOPT_FOLLOWLOCATION, false);
        $transport->request($request);

        $response = $transport->getResponse();
        $responseContent = $response->getBody()->getContents();

        //print_r($responseContent);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertContains('<TITLE>301 Moved</TITLE>', $responseContent);
    }

    public function testTransportWithWrongParams(): void
    {
        $request1 = new Request;
        $request1 = $request1->withMethod('TEST');
        $request1 = $request1->withUri(new Uri('http://google.com'));
        $request1 = $request1->withBody(new Stream('php://temp', 'wb+'));

        $request2 = new Request;
        $request2 = $request2->withMethod('GET');
        $request2 = $request2->withUri(new Uri(''));
        $request2 = $request2->withBody(new Stream('php://temp', 'wb+'));

        $request3 = new Request;
        $request3 = $request3->withMethod('GET');
        $request3 = $request3->withUri(new Uri('http://localhost:9210'));
        $request3 = $request3->withBody(new Stream('php://temp', 'wb+'));

        //$this->expectException(TransportException::class);
        //$this->expectExceptionMessage('Method TEST is unsupported');

        try {
            $transport = new Curl;
            $transport->request($request1);
        } catch (TransportException $e) {
            $this->assertEquals('Method TEST is unsupported', $e->getMessage());
        }

        //$this->expectExceptionMessageRegExp('/Too few arguments/');

        try {
            $transport = new Curl;
            $transport->request($request2);
        } catch (TransportException $e) {
            $this->assertContains('URL using bad/illegal format or missing URL', $e->getMessage());
            $this->assertEquals(3, $e->getCode());
        }

        try {
            $transport = new Curl;
            $transport->request($request3);
        } catch (TransportException $e) {
            $this->assertContains('Failed to connect to localhost', $e->getMessage());
        }
    }

    public function testTransportLargeRespone(): void
    {
        /**
         * Please, use this test at your own risk. You may get very long response time.
         *
         * Client was tested with it successfully.
         *
         * It's left here for Debug purpose.
         */

        $this->assertTrue(true);

        return;

        $request = new Request;
        $request = $request->withMethod('GET');
        //$request = $request->withUri(new Uri('http://ipv4.download.thinkbroadband.com/1GB.zip'));
        //$request = $request->withUri(new Uri('http://ipv4.download.thinkbroadband.com/512MB.zip'));
        //$request = $request->withUri(new Uri('http://ipv4.download.thinkbroadband.com/200MB.zip'));
        $request = $request->withUri(new Uri('http://ipv4.download.thinkbroadband.com/100MB.zip'));
        $request = $request->withBody(new Stream('php://temp', 'wb+'));

        $transport = new Curl;
        $transport->setOption(CURLOPT_FOLLOWLOCATION, false);
        $transport->setOption(CURLOPT_USERAGENT, 'MyFancyUserAgent');
        $transport->setOption(CURLOPT_CONNECTTIMEOUT, 300);
        $transport->setOption(CURLOPT_TIMEOUT, 300);
        $transport->request($request);

        $response = $transport->getResponse();
        $responseSize = $response->getBody()->getSize();

        //print_r($responseSize);
        //print_r($response->getHeaders());

        $this->assertInstanceOf(Response::class, $response);
    }
}

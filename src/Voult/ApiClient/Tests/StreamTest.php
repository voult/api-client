<?php

namespace Voult\ApiClient\Tests;

use PHPUnit\Framework\TestCase;
use Voult\ApiClient\Exception\InvalidArgumentException;
use Voult\ApiClient\Exception\StreamException;
use Voult\ApiClient\Psr7\Stream;

/**
 * @author [2019-10-23] Edmund A. Pacha <voults@gmail.com>
 */
class StreamTest extends TestCase
{
    public function testStream()
    {
        $string = 'Lorem ipsum';
        $stringLength = strlen($string);

        $stream = new Stream;

        $this->assertIsBool($stream->isWritable());
        $this->assertFalse($stream->isWritable());
        $this->assertFalse($stream->isSeekable());
        $this->assertFalse($stream->isReadable());
        $this->assertTrue($stream->eof());
        $this->assertNull($stream->getSize());
        $this->assertNull($stream->getMetadata());

        //$this->expectException(StreamException::class);

        try {
            $stream->tell();
        } catch (StreamException $e) {
            $this->assertEquals('Missing resource', $e->getMessage());
        }

        try {
            $stream->seek(0);
        } catch (StreamException $e) {
            $this->assertEquals('Missing resource', $e->getMessage());
        }

        try {
            $stream->read(0);
        } catch (StreamException $e) {
            $this->assertEquals('Missing resource', $e->getMessage());
        }

        try {
            $stream->write('');
        } catch (StreamException $e) {
            $this->assertEquals('Missing resource', $e->getMessage());
        }

        try {
            $stream->getContents();
        } catch (StreamException $e) {
            $this->assertEquals('Stream is not readable', $e->getMessage());
        }

        //$this->expectException(InvalidArgumentException::class);

        try {
            $stream->setStream('test');
        } catch (InvalidArgumentException $e) {
            $this->assertEquals('Invalid stream provided', $e->getMessage());
        }

        $stream->setStream('php://temp', 'wb+');

        $this->assertTrue($stream->isWritable());
        $this->assertTrue($stream->isSeekable());
        $this->assertTrue($stream->isReadable());

        $this->assertEquals($stringLength, $stream->write($string));

        $stream->rewind();

        $this->assertEquals($string, $stream->getContents());
    }
}

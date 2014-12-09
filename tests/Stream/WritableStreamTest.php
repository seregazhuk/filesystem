<?php

namespace React\Tests\Filesystem\Stream;

use React\Filesystem\Eio\WritableStream;
use React\Promise\RejectedPromise;

class WritableStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testWrite()
    {
        $path = 'foo.bar';
        $fd = '0123456789abcdef';
        $filesystem = $this->getMock('React\Filesystem\EioAdapter', [
            'write',
        ], [
            $this->getMock('React\EventLoop\StreamSelectLoop'),
        ]);

        $filesystem
            ->expects($this->once())
            ->method('write')
            ->with($fd)
            ->will($this->returnValue($fd))
        ;

        (new WritableStream($path, $fd, $filesystem))->write('abc');
    }

    public function testIsWritable()
    {
        $path = 'foo.bar';
        $fd = '0123456789abcdef';
        $filesystem = $this->getMock('React\Filesystem\EioAdapter', [], [
            $this->getMock('React\EventLoop\StreamSelectLoop'),
        ]);

        $this->assertTrue((new WritableStream($path, $fd, $filesystem))->isWritable());
    }

    public function testIsNotWritable()
    {
        $path = 'foo.bar';
        $fd = '0123456789abcdef';
        $filesystem = $this->getMock('React\Filesystem\EioAdapter', [
            'close',
        ], [
            $this->getMock('React\EventLoop\StreamSelectLoop'),
        ]);


        $filesystem
            ->expects($this->once())
            ->method('close')
            ->with($fd)
            ->will($this->returnValue(new RejectedPromise()))
        ;


        $stream = (new WritableStream($path, $fd, $filesystem));
        $stream->close();
        $this->assertTrue(!$stream->isWritable());
    }

    public function testEnd()
    {
        $data = 'iahbfeq';
        $stream = $this->getMock('React\Filesystem\Eio\WritableStream', [
            'write',
            'close',
        ], [
            'foo.bar',
            '0123456789abcdef',
            $this->getMock('React\Filesystem\EioAdapter', [
                'close',
            ], [
                $this->getMock('React\EventLoop\StreamSelectLoop'),
            ]),
        ]);

        $stream
            ->expects($this->once())
            ->method('write')
            ->with($data)
        ;

        $stream
            ->expects($this->once())
            ->method('close')
            ->with()
        ;

        $stream->end($data);
    }

    public function testEndNoWrite()
    {
        $stream = $this->getMock('React\Filesystem\Eio\WritableStream', [
            'write',
            'close',
        ], [
            'foo.bar',
            '0123456789abcdef',
            $this->getMock('React\Filesystem\EioAdapter', [
                'close',
            ], [
                $this->getMock('React\EventLoop\StreamSelectLoop'),
            ]),
        ]);

        $stream
            ->expects($this->never())
            ->method('write')
            ->with()
        ;

        $stream
            ->expects($this->once())
            ->method('close')
            ->with()
        ;

        $stream->end();
    }
}

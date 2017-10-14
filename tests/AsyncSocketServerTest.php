<?php

namespace BV\ROoHP;

class AsyncSocketServerTest extends \PHPUnit_Framework_TestCase
{
    /** @var AsyncSocketServer  */
    protected $socketServer = null;

    /** @var AsyncSocketServer  */
    protected $customSocketServer = null;

    protected function setUp()
    {
        $this->socketServer = new AsyncSocketServer();
        $this->customSocketServer = new AsyncSocketServer('127.0.0.1', 8009);
    }

    protected function tearDown()
    {
        $this->assertTrue($this->socketServer->stop());
    }

    public function testGetHostPort()
    {
        $this->assertEquals(8082, $this->socketServer->getPort());
        $this->assertEquals(8009, $this->customSocketServer->getPort());

        $this->socketServer->setPort(9000);
        $this->assertEquals(9000, $this->socketServer->getPort());

        $this->assertEquals('localhost', $this->socketServer->getHost());
        $this->socketServer->setHost('127.0.0.1');
        $this->assertEquals('127.0.0.1', $this->socketServer->getHost());
    }

    public function testStartServer()
    {
        $this->assertTrue($this->socketServer->start());
        return $this->socketServer;
    }

    /**
     * @param AsyncSocketServer $server
     * @depends testStartServer
     */
    public function testIsRun(AsyncSocketServer $server)
    {
        $this->assertFalse($server->isRun());
    }

    /**
     * @depends testIsRun
     */
    public function testCloseServer()
    {
        $this->assertTrue($this->socketServer->stop());
    }
}

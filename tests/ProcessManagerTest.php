<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

class ProcessManagerTest extends TestCase
{
    /**
     * @var HttpServer
     */
    protected $server;

    protected function setUp()
    {
        parent::setUp();

        $this->server = new HttpServer('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 1,
        ]);
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response->end('Success');
        });
    }

    public function testSpawn()
    {
        $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertContains('Success', $result);
    }

    public function testSpawnAlive()
    {
        $this->spawn($this->server);

        sleep(2);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertContains('Success', $result);
    }

    public function testSpawnStale()
    {
        $this->spawn($this->server, 10, 1);

        sleep(2);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertFalse($result);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Server startup timeout exceeded
     */
    public function testSpawnTimeout()
    {
        $this->server->setDelay(2);
        
        $this->spawn($this->server, 1);
    }

    public function testKill()
    {
        $pid = $this->spawn($this->server);
        
        $this->kill($pid);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertFalse($result);
    }
}
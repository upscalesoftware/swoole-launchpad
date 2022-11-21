<?php declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

class ProcessManagerTest extends TestCase
{
    protected HttpServer $server;

    protected function setUp(): void
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
        $this->assertStringContainsString('Success', $result);
    }

    public function testSpawnAlive()
    {
        $this->spawn($this->server);

        sleep(2);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringContainsString('Success', $result);
    }

    public function testSpawnStale()
    {
        $this->spawn($this->server, 10, 1);

        sleep(2);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertNull($result);
    }

    public function testSpawnTimeout()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Server startup timeout exceeded');

        $this->server->setStartupDelay(2);
        
        try {
            $this->spawn($this->server, 1);
        } finally {
            $result = $this->curl('http://127.0.0.1:8080/');
            $this->assertNull($result);
        }
    }

    public function testKill()
    {
        $this->server->setShutdownDelay(2);
        
        $pid = $this->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertStringContainsString('Success', $result);
        
        $this->kill($pid);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertNull($result);
    }
}
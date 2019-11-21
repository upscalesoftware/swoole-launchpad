<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Process\Tests;

use Upscale\Swoole\Process\ProcessManager;

class ProcessManagerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var HttpServer
     */
    protected $server;

    /**
     * @var ProcessManager
     */
    protected $processManager;

    /**
     * @var int
     */
    protected $pid;

    protected function setUp()
    {
        $this->server = new HttpServer('127.0.0.1', 8080);
        $this->server->set([
            'log_file' => '/dev/null',
            'log_level' => 4,
            'worker_num' => 1,
        ]);
        $this->server->on('request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
            $response->end('Success');
        });
        
        $this->processManager = new ProcessManager();
    }

    protected function tearDown()
    {
        $this->processManager->kill($this->pid);
    }

    public function testSpawn()
    {
        $this->pid = $this->processManager->spawn($this->server);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertContains('Success', $result);
    }

    public function testSpawnAlive()
    {
        $this->pid = $this->processManager->spawn($this->server);

        sleep(2);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertContains('Success', $result);
    }

    public function testSpawnStale()
    {
        $this->pid = $this->processManager->spawn($this->server, 10, 1);

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
        
        $this->pid = $this->processManager->spawn($this->server, 1);
    }

    public function testKill()
    {
        $this->pid = $this->processManager->spawn($this->server);
        
        $this->processManager->kill($this->pid);

        $result = $this->curl('http://127.0.0.1:8080/');
        $this->assertFalse($result);
    }

    /**
     * Send an HTTP request to a given URL and return response body
     *
     * @param string $url
     * @param int $timeout Timeout in seconds
     * @return string|bool
     */
    protected function curl($url, $timeout = 2)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}
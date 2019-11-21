<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

use Upscale\Swoole\Launchpad\ProcessManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ProcessManager
     */
    private $processManager;

    /**
     * @var int[]
     */
    private $processIds = [];

    /**
     * Send an HTTP request to a given URL and return response
     *
     * @param string $url
     * @param array $options
     * @return string|bool
     */
    public static function curl($url, array $options = [])
    {
        $options += [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FORBID_REUSE => true,
            CURLOPT_FRESH_CONNECT => true,
            CURLOPT_HEADER => true,
            CURLOPT_TIMEOUT => 2,
        ];
        $curl = curl_init();
        foreach ($options as $key => $value) {
            curl_setopt($curl, $key, $value);
        }
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }

    protected function setUp()
    {
        $this->processManager = new ProcessManager();
    }

    protected function tearDown()
    {
        array_walk($this->processIds, [$this, 'kill']);
    }

    /**
     * @param \Swoole\Server $server
     * @param mixed ...$args
     * @return int
     */
    protected function spawn(\Swoole\Server $server, ...$args)
    {
        $pid = $this->processManager->spawn($server, ...$args);
        $this->processIds[] = $pid;
        return $pid;
    }

    /**
     * @param int $pid
     */
    protected function kill($pid)
    {
        $this->processManager->kill($pid);
    }
}
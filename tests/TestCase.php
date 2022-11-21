<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Launchpad\Tests;

use Upscale\Swoole\Launchpad\ProcessManager;

class TestCase extends \PHPUnit\Framework\TestCase
{
    private ProcessManager $processManager;

    private array $processIds = [];

    /**
     * Send an HTTP request to a given URL and return response
     */
    public static function curl(string $url, array $options = []): ?string
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
        return ($result !== false)
            ? $result
            : null;
    }

    protected function setUp(): void
    {
        $this->processManager = new ProcessManager();
    }

    protected function tearDown(): void
    {
        array_walk($this->processIds, [$this, 'kill']);
    }

    protected function spawn(\Swoole\Server $server, ...$args): int
    {
        $pid = $this->processManager->spawn($server, ...$args);
        $this->processIds[] = $pid;
        return $pid;
    }

    protected function kill(int $pid)
    {
        $this->processManager->kill($pid);
    }
}
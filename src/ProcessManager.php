<?php
declare(strict_types=1);
/**
 * Copyright © Upscale Software. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Upscale\Swoole\Launchpad;

class ProcessManager
{
    /**
     * Infinite amount of time
     */
    const TIME_INFINITY = 0;
    
    /**
     * Managed processes by PID
     * 
     * @var \Swoole\Process[]
     */
    protected array $processes = [];

    /**
     * Launch a given server in a child process and return its PID
     * 
     * @param \Swoole\Server $server
     * @param int $timeout Server startup timeout in seconds
     * @param int $lifetime Server process lifetime in seconds
     * @return int PID
     * @throws \RuntimeException
     */
    public function spawn(\Swoole\Server $server, int $timeout = 10, int $lifetime = self::TIME_INFINITY): int
    {
        $semaphore = new \Swoole\Atomic();

        $server->on('WorkerStart', function (\Swoole\Server $server) use ($semaphore, $lifetime) {
            $semaphore->wakeup();
            if ($lifetime > 0) {
                sleep($lifetime);
                $server->shutdown();
            }
        });
        
        $process = new \Swoole\Process([$server, 'start']);
        $pid = $process->start();

        if (!$semaphore->wait($timeout)) {
            \Swoole\Process::kill($pid);
            throw new \RuntimeException('Server startup timeout exceeded.');
        }
        
        $this->processes[$pid] = $process;
        
        return $pid;
    }

    /**
     * Terminate managed process identified by a given PID
     */
    public function kill(int $pid): void
    {
        if (isset($this->processes[$pid])) {
            unset($this->processes[$pid]);
            \Swoole\Process::kill($pid);
            \Swoole\Process::wait();
        }
    }
}
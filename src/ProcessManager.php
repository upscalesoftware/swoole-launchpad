<?php
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
    protected $processes = [];

    /**
     * Launch a given server in a child process and return its PID
     * 
     * @param \Swoole\Server $server
     * @param int $timeout Server startup timeout in seconds
     * @param int $lifetime Server process lifetime in seconds
     * @return int PID
     * @throws \RuntimeException
     */
    public function spawn(\Swoole\Server $server, $timeout = 10, $lifetime = self::TIME_INFINITY)
    {
        $semaphore = new \Swoole\Atomic();

        $server->on('WorkerStart', function ($server) use ($semaphore, $lifetime) {
            $semaphore->wakeup();
            if ($lifetime > 0) {
                $server->after($lifetime * 1000, [$server, 'shutdown']);
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
     * 
     * @param int $pid
     */
    public function kill($pid)
    {
        if (isset($this->processes[$pid])) {
            unset($this->processes[$pid]);
            \Swoole\Process::kill($pid);
            \Swoole\Process::wait();
        }
    }
}
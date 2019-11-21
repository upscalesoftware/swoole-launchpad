<?php
/**
 * Copyright © Upscale Software. All rights reserved.
 * See COPYRIGHT.txt for license details.
 */
namespace Upscale\Swoole\Process;

class ProcessManager
{
    /**
     * Kill switches of managed processes
     * 
     * @var \Swoole\Atomic[]
     */
    protected $cutouts = [];

    /**
     * Launch a given server in a child process and return its PID
     * 
     * @param \Swoole\Server $server
     * @param int $timeout Server startup timeout in seconds
     * @param int $lifetime Server process lifetime in seconds
     * @return int PID
     * @throws \RuntimeException
     */
    public function spawn(\Swoole\Server $server, $timeout = 10, $lifetime = PHP_INT_MAX)
    {
        $launch = new \Swoole\Atomic();
        $cutout = new \Swoole\Atomic();

        $server->on('WorkerStart', function () use ($launch) {
            $launch->wakeup();
        });
        
        $watchdog = new \Swoole\Process(function () use ($server, $cutout, $lifetime) {
            $cutout->wait($lifetime);
            $server->shutdown();
        });
        $server->addProcess($watchdog);

        $process = new \Swoole\Process([$server, 'start']);
        $pid = $process->start();

        if (!$launch->wait($timeout)) {
            $cutout->wakeup();
            \Swoole\Process::kill($pid);
            throw new \RuntimeException('Server startup timeout exceeded.');
        }
        
        $this->cutouts[$pid] = $cutout;

        return $pid;
    }

    /**
     * Terminate managed process identified by a given PID
     * 
     * @param int $pid
     */
    public function kill($pid)
    {
        if (isset($this->cutouts[$pid])) {
            $cutout = $this->cutouts[$pid];
            $cutout->wakeup();
            unset($this->cutouts[$pid]);
        }
    }
}